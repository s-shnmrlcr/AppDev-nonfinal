<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
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
            ->with('barangay')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Beneficiary $beneficiary) => [
                'id' => 'BEN-'.str_pad((string) $beneficiary->id, 6, '0', STR_PAD_LEFT),
                'name' => $beneficiary->full_name,
                'age' => $beneficiary->date_of_birth->age,
                'sex' => $beneficiary->sex,
                'barangay' => $beneficiary->barangay->name,
                'sector' => $beneficiary->sector,
                'occupation' => $beneficiary->occupation,
                'verification' => $beneficiary->verification_status,
                'payout' => $beneficiary->payout_status,
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

        DB::transaction(function () use ($validated, $document): void {
            if ($document !== null) {
                $path = $document->store('beneficiary-ids', 'local');
                if ($path === false) {
                    throw new RuntimeException('The ID document could not be stored.');
                }
                $validated['id_document_path'] = $path;
            }
            Beneficiary::create($validated);
        });

        return redirect()
            ->route('beneficiaries.index')
            ->with('status', 'Beneficiary registered.');
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
            'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'sex',
            'civil_status', 'contact_number', 'address', 'barangay', 'sector', 'household_id',
            'household_members', 'dependents', 'children', 'senior_citizens', 'pwd_members',
            'occupation', 'source_of_income', 'monthly_income', 'employment_status',
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
                    $beneficiary->address,
                    $beneficiary->barangay->name,
                    $beneficiary->sector,
                    $beneficiary->household_id,
                    $beneficiary->household_members,
                    $beneficiary->dependents,
                    $beneficiary->children,
                    $beneficiary->senior_citizens,
                    $beneficiary->pwd_members,
                    $beneficiary->occupation,
                    $beneficiary->source_of_income,
                    $beneficiary->monthly_income,
                    $beneficiary->employment_status,
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
            'sex' => ['required', 'in:Male,Female,Other'],
            'civil_status' => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'barangay_id' => ['required', 'integer', 'exists:barangays,id'],
            'sector' => ['required', 'in:General Beneficiary,Senior Citizen,PWD,Solo Parent,Pregnant/Lactating,Indigenous People,PWUD,Other'],
            'household_id' => ['nullable', 'string', 'max:255'],
            'household_members' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'dependents' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'children' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'senior_citizens' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'pwd_members' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'source_of_income' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'employment_status' => ['nullable', 'in:Self-employed,Employed,Unemployed,Informal'],
        ];
        if ($includeDocument) {
            $rules['id_document'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
        }

        return $rules;
    }

    private function csvHeaders(): array
    {
        return [
            'first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'sex',
            'civil_status', 'contact_number', 'address', 'barangay', 'sector', 'household_id',
            'household_members', 'dependents', 'children', 'senior_citizens', 'pwd_members',
            'occupation', 'source_of_income', 'monthly_income', 'employment_status',
        ];
    }

    private function safeCsvValue(mixed $value): mixed
    {
        // Butangi og apostrophe ang formula-like nga text aron dili ma-execute sa spreadsheet. -shaine
        if (! is_string($value) || preg_match('/^[\s]*[=+\-@]/', $value) !== 1) {
            return $value;
        }

        return "'".$value;
    }
}
