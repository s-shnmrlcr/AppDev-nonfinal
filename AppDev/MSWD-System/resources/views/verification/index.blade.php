@extends('layouts.app')
@section('title', 'Claimant Identity Check | MSWDO Kabacan')
@section('page-title', 'Claimant Identity Check')
@section('breadcrumb', 'Verification Center')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">IN-PERSON CLAIM</div>
            <h1>Claimant Identity Check</h1>
            <p>Compare the claimant’s face with the portrait on their uploaded ID.</p>
            <p class="identity-privacy">Staff review only. No facial recognition; images stay on this device.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <section class="card identity-check-card">
        <div class="identity-panels">
            <section class="identity-panel">
                <div class="section-heading">
                    <h3>Live claimant</h3>
                </div>
                <div class="camera-frame">
                    <video id="claimantCamera" autoplay muted playsinline aria-label="Live claimant camera preview"></video>
                    <canvas id="claimantCapture" class="hidden" aria-label="Captured claimant photo"></canvas>
                    <div id="cameraPrompt" class="camera-prompt">
                        <span class="empty-state-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 7h3l2-3h6l2 3h3v13H4z" /><circle cx="12" cy="13" r="4" /></svg>
                        </span>
                        <span>Camera preview</span>
                    </div>
                </div>
                <div class="camera-controls">
                    <button type="button" class="button button-secondary" id="startCamera">Start camera</button>
                    <button type="button" class="button button-primary" id="capturePhoto" disabled>Capture photo</button>
                    <button type="button" class="button button-secondary" id="retakePhoto" hidden>Retake photo</button>
                </div>
                <p id="cameraStatus" class="camera-status" role="status"></p>
            </section>

            <section class="identity-panel">
                <div class="section-heading">
                    <h3>Photo ID</h3>
                </div>
                <label class="id-upload">
                    <input type="file" id="idDocument" accept="image/*,.pdf,application/pdf">
                    <span class="empty-state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="9" cy="11" r="2" /><path d="m5 17 5-4 3 3 2-2 4 3" /></svg>
                    </span>
                    <strong>Select uploaded ID document</strong>
                    <span>Image or PDF · up to 10 MB</span>
                </label>
                <div class="id-preview-frame">
                    <img id="idPreview" class="hidden" alt="Selected ID image preview">
                    <iframe id="idPdfPreview" class="hidden" title="Uploaded ID document preview"></iframe>
                    <p id="idPrompt">ID document preview</p>
                </div>
                <p id="idStatus" class="camera-status" role="status"></p>
            </section>
        </div>

        <form method="POST" action="{{ route('verification.review') }}" id="identityReviewForm" class="identity-review-form">
            @csrf
            <div class="button-group">
                <button type="submit" name="decision" value="matched" class="button button-primary" disabled>
                    Identity matches
                </button>
                <button type="submit" name="decision" value="not_matched" class="button button-secondary" disabled>
                    Identity does not match
                </button>
            </div>
            <p id="reviewStatus" class="camera-status" role="status">Capture a photo and select an ID document to enable review.</p>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const camera = document.getElementById('claimantCamera');
        const captureCanvas = document.getElementById('claimantCapture');
        const cameraPrompt = document.getElementById('cameraPrompt');
        const startCameraButton = document.getElementById('startCamera');
        const captureButton = document.getElementById('capturePhoto');
        const retakeButton = document.getElementById('retakePhoto');
        const cameraStatus = document.getElementById('cameraStatus');
        const idInput = document.getElementById('idDocument');
        const idPreview = document.getElementById('idPreview');
        const idPdfPreview = document.getElementById('idPdfPreview');
        const idPrompt = document.getElementById('idPrompt');
        const idStatus = document.getElementById('idStatus');
        const reviewButtons = document.querySelectorAll('#identityReviewForm button[type="submit"]');
        let cameraStream = null;
        let captured = false;
        let selectedId = false;
        let idImageUrl = null;

        function updateReviewAvailability() {
            // I-enable lang ang review human makuha ang live photo ug mapili ang valid nga ID file. -shaine
            const ready = captured && selectedId;
            reviewButtons.forEach(button => button.disabled = !ready);
        }

        startCameraButton.addEventListener('click', async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                cameraStatus.textContent = 'Camera unavailable. Use a supported browser over HTTPS or localhost.';
                return;
            }

            startCameraButton.disabled = true;
            cameraStatus.textContent = 'Waiting for camera permission…';
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: { facingMode: 'user' }
                });
                camera.srcObject = cameraStream;
                camera.classList.remove('hidden');
                captureCanvas.classList.add('hidden');
                cameraPrompt.classList.add('hidden');
                captureButton.disabled = false;
                cameraStatus.textContent = 'Camera ready.';
            } catch (error) {
                const messages = {
                    NotAllowedError: 'Camera permission was denied. Allow access in your browser settings to continue.',
                    NotFoundError: 'No camera was found on this device.',
                    NotReadableError: 'The camera is in use by another application.'
                };
                cameraStatus.textContent = messages[error.name] || 'Camera could not start. Check device and browser permissions.';
                startCameraButton.disabled = false;
            }
        });

        captureButton.addEventListener('click', () => {
            if (!cameraStream || !camera.videoWidth) {
                cameraStatus.textContent = 'Wait for the camera preview before capturing.';
                return;
            }

            captureCanvas.width = camera.videoWidth;
            captureCanvas.height = camera.videoHeight;
            captureCanvas.getContext('2d').drawImage(camera, 0, 0);
            // Hunonga dayon ang camera ug bilin ang capture sa browser; dili ipadala ang hulagway sa server. -shaine
            cameraStream.getTracks().forEach(track => track.stop());
            camera.srcObject = null;
            cameraStream = null;
            captureCanvas.classList.remove('hidden');
            camera.classList.add('hidden');
            captured = true;
            captureButton.disabled = true;
            retakeButton.hidden = false;
            cameraStatus.textContent = 'Photo captured.';
            updateReviewAvailability();
        });

        retakeButton.addEventListener('click', () => {
            captured = false;
            captureCanvas.classList.add('hidden');
            camera.classList.add('hidden');
            cameraPrompt.classList.remove('hidden');
            startCameraButton.disabled = false;
            captureButton.disabled = true;
            retakeButton.hidden = true;
            cameraStatus.textContent = 'Start the camera to retake.';
            updateReviewAvailability();
        });

        idInput.addEventListener('change', () => {
            const file = idInput.files[0];
            selectedId = false;
            if (idImageUrl) URL.revokeObjectURL(idImageUrl);
            idImageUrl = null;
            idPdfPreview.src = 'about:blank';
            idPdfPreview.classList.add('hidden');
            idPreview.classList.add('hidden');
            idPreview.removeAttribute('src');
            idPrompt.classList.remove('hidden');

            if (!file) {
                idStatus.textContent = '';
                updateReviewAvailability();
                return;
            }
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            if (!file.type.startsWith('image/') && !isPdf) {
                idInput.value = '';
                idStatus.textContent = 'Choose an image or PDF.';
                updateReviewAvailability();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                idInput.value = '';
                idStatus.textContent = 'Maximum file size is 10 MB.';
                updateReviewAvailability();
                return;
            }

            idImageUrl = URL.createObjectURL(file);
            if (isPdf) {
                idPdfPreview.src = idImageUrl;
                idPdfPreview.classList.remove('hidden');
                idPrompt.classList.add('hidden');
                selectedId = true;
                idStatus.textContent = 'ID ready. Compare the portrait with the claimant.';
                updateReviewAvailability();
                return;
            }
            idPreview.onload = () => {
                selectedId = true;
                idStatus.textContent = 'ID ready. Compare the portrait with the claimant.';
                updateReviewAvailability();
            };
            idPreview.onerror = () => {
                selectedId = false;
                idStatus.textContent = 'Image preview failed. Choose another file.';
                updateReviewAvailability();
            };
            idPreview.src = idImageUrl;
            idPreview.classList.remove('hidden');
            idPrompt.classList.add('hidden');
        });

        document.getElementById('identityReviewForm').addEventListener('submit', event => {
            // I-validate pag-usab sa submit aron dili masulod ang review kung wala na ang gikinahanglang file/photo. -shaine
            if (!captured || !selectedId) {
                event.preventDefault();
                document.getElementById('reviewStatus').textContent = 'Capture a photo and select an ID document first.';
                return;
            }
            reviewButtons.forEach(button => button.disabled = true);
        });

        window.addEventListener('pagehide', () => {
            // Limpyoha ang camera ug temporary object URL kon mobiya sa panid aron dili magbilin og resource. -shaine
            cameraStream?.getTracks().forEach(track => track.stop());
            if (idImageUrl) URL.revokeObjectURL(idImageUrl);
        });
    </script>
@endpush
