<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\FamilyHead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BeneficiaryController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::query()
            ->with(['barangay', 'familyHead'])
            ->orderBy('last_name')
            ->get()
            ->map(fn (Beneficiary $beneficiary) => [
                'id' => 'BEN-'.str_pad((string) $beneficiary->id, 6, '0', STR_PAD_LEFT),
                'name' => $beneficiary->full_name,
                'age' => $beneficiary->date_of_birth->age,
                'sex' => $beneficiary->sex,
                'purok' => $beneficiary->purok,
                'barangay' => $beneficiary->barangay->name,
                'sector' => $beneficiary->sector,
                'occupation' => $beneficiary->familyHead?->occupation ?? '—',
                'verification' => $beneficiary->verification_status,

                // 'payout' => $beneficiary->payout_status, gi move ko ni sa payouts table --shijme
            ]);
        // Gi add ko source ang values sa barangay table for search filter --shijme
        // Gi change ko ang source for barangays, sa database ko na gi connect ang values --shijme
        $barangays = DB::table('barangays')->orderBy('name')->get(['id', 'name']);

        return view('beneficiaries.index', compact('beneficiaries', 'barangays'));
    }

    public function create()
    {
        $barangays = DB::table('barangays')->orderBy('name')->get(['id', 'name']);

        return view('beneficiaries.create', compact('barangays'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->beneficiaryRules());

        $document = $validated['id_document'] ?? null;
        unset($validated['id_document']);

        // Separate family-head fields from beneficiary fields.
        $familyHeadFields = [
            'household_members',
            'dependents',
            'children',
            'senior_citizens',
            'pwd_members',
            'occupation',
            'source_of_income',
            'monthly_estimated_income',
            'employment_status',
        ];

        $familyHeadData = array_intersect_key(
            $validated,
            array_flip($familyHeadFields)
        );

        $beneficiaryData = array_diff_key(
            $validated,
            array_flip($familyHeadFields)
        );

        DB::transaction(function () use (
            &$beneficiaryData,
            $familyHeadData,
            $document
        ): void {
            if ($document !== null) {
                $path = $document->store('beneficiary-ids', 'local');

                if ($path === false) {
                    throw new RuntimeException(
                        'The ID document could not be stored.'
                    );
                }

                $beneficiaryData['id_document_path'] = $path;
            }

            $beneficiary = Beneficiary::create($beneficiaryData);

            if ($beneficiary->is_familyhead) {
                $beneficiary->familyHead()->create($familyHeadData);
            }
        });

        return redirect()
            ->route('beneficiaries.index')
            ->with('status', 'Beneficiary registered successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'masterlist' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $handle = fopen($request->file('masterlist')->getRealPath(), 'r');
        if ($handle === false) {
            throw new RuntimeException('The masterlist file could not be read.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw ValidationException::withMessages(['masterlist' => 'The CSV file is empty.']);
        }
        // Tangtanga ang UTF-8 BOM kay usahay maapil ni sa unang column name sa gi-export nga CSV. -shaine
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
        $requiredHeaders = ['first_name', 'last_name', 'date_of_birth', 'sex', 'barangay', 'sector'];
        if (array_diff($requiredHeaders, $headers)) {
            fclose($handle);
            throw ValidationException::withMessages([
                'masterlist' => 'CSV headers must include: '.implode(', ', $requiredHeaders).'.',
            ]);
        }

        $barangays = DB::table('barangays')->pluck('id', 'name')->all();
        $allowedHeaders = [
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'date_of_birth',
            'sex',
            'civil_status',
            'contact_number',
            'purok',
            'barangay',
            'sector',
            'is_familyhead',
        ];
        $rows = [];
        $errors = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if ($values === [null] || $values === []) {
                continue;
            }
            if (count($values) !== count($headers)) {
                $errors[] = "Line {$line}: column count does not match the header.";
                continue;
            }

            // I-map ang mga ngalan sa barangay ngadto sa foreign key nga need sa database. -shaine
            $row = array_combine($headers, $values);
            $row = array_intersect_key($row, array_flip($allowedHeaders));
            $row = array_map(
                fn ($value) => is_string($value) && trim($value) === '' ? null : (is_string($value) ? trim($value) : $value),
                $row
            );
            $row['barangay_id'] = $barangays[$row['barangay'] ?? ''] ?? null;
            unset($row['barangay']);

            if (
                isset($row['is_familyhead'])
                && filter_var($row['is_familyhead'], FILTER_VALIDATE_BOOLEAN)
            ) {
                $errors[] = "Line {$line}: Family heads must be registered through the Add Beneficiary form.";
                continue;
            }

            $validator = Validator::make($row, $this->beneficiaryRules(false));
            if ($validator->fails()) {
                $errors[] = "Line {$line}: ".implode(' ', $validator->errors()->all());
                continue;
            }
            $rows[] = $validator->validated();
            if (count($rows) > 5000) {
                $errors[] = 'CSV files may contain no more than 5,000 beneficiaries.';
                break;
            }
        }
        fclose($handle);

        // I-validate una ang tanang linya aron walay partial nga ma-import kung naay sayop. -shaine
        if ($errors !== []) {
            throw ValidationException::withMessages(['masterlist' => implode("\n", array_slice($errors, 0, 20))]);
        }
        if ($rows === []) {
            throw ValidationException::withMessages(['masterlist' => 'The CSV contains no beneficiary records.']);
        }

        // Usa ka transaction aron kompleto ang import o walay ma-save kung mamali ang pagsulat. -shaine
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                Beneficiary::create($row);
            }
        });

        return redirect()
            ->route('beneficiaries.index')
            ->with('status', count($rows).' beneficiaries imported.');
    }

    public function export(): StreamedResponse
    {
        $beneficiaries = Beneficiary::query()->with('barangay')->orderBy('id')->get();

        return response()->streamDownload(function () use ($beneficiaries): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('The CSV output stream could not be opened.');
            }
            fputcsv($output, $this->csvHeaders(), escape: '');

            // I-preload ang barangay relation sa query sa taas aron malikayan ang query kada beneficiary. -shaine
            foreach ($beneficiaries as $beneficiary) {
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $beneficiary->first_name,
                    $beneficiary->middle_name,
                    $beneficiary->last_name,
                    $beneficiary->suffix,
                    $beneficiary->date_of_birth->format('Y-m-d'),
                    $beneficiary->sex,
                    $beneficiary->civil_status,
                    $beneficiary->contact_number,
                    $beneficiary->purok,
                    $beneficiary->barangay?->name,
                    $beneficiary->sector,
                    $beneficiary->is_familyhead,
                ]), escape: '');
            }

            fclose($output);
        }, 'beneficiaries.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function beneficiaryRules(bool $includeDocument = true): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:Male,Female'],
            'civil_status' => ['nullable', 'in:Single,Married,Divorced,Widowed,Separated'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'purok' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['required', 'integer', 'exists:barangays,id'],
            'sector' => ['required', 'in:General Beneficiary,Senior Citizen,PWD,Solo Parent,Pregnant/Lactating,Indigenous People,PWUD,Other'],
            'is_familyhead' => ['required', 'boolean'],
        ];

        // mag appear lang ni nga fields if beneficiary is a family head --shijme
        if ($includeDocument) {
            $rules = array_merge($rules, [
                'household_members' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'integer',
                    'min:1',
                ],
                'dependents' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'integer',
                    'min:0',
                ],
                'children' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'integer',
                    'min:0',
                ],
                'senior_citizens' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'integer',
                    'min:0',
                ],
                'pwd_members' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'integer',
                    'min:0',
                ],
                'occupation' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'string',
                    'max:255',
                ],
                'source_of_income' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'string',
                    'max:255',
                ],
                'monthly_estimated_income' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'numeric',
                    'min:0',
                ],
                'employment_status' => [
                    'exclude_unless:is_familyhead,1',
                    'required',
                    'string',
                    'max:255',
                ],
            ]);

            $rules['id_document'] = [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ];
        }

        return $rules;
    }

    private function csvHeaders(): array
    {
        return [
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'date_of_birth',
            'sex',
            'civil_status',
            'contact_number',
            'purok',
            'barangay',
            'sector',
            'is_familyhead',
        ];
    }

    private function safeCsvValue(mixed $value): mixed {

        // Butangi og apostrophe ang formula-like nga text aron dili ma-execute sa spreadsheet. -shaine
        if (
            ! is_string($value)
            || preg_match('/^[\s]*[=+\-@]/', $value) !== 1
        ) {
            return $value;
        }

        return "'".$value;

    }
}
