import * as faceapi from '@vladmandic/face-api';

const check = document.getElementById('identityCheck');

if (check) {
    const camera = document.getElementById('claimantCamera');
    const captureCanvas = document.getElementById('claimantCapture');
    const cameraPrompt = document.getElementById('cameraPrompt');
    const startCameraButton = document.getElementById('startCamera');
    const captureButton = document.getElementById('capturePhoto');
    const retakeButton = document.getElementById('retakePhoto');
    const cameraStatus = document.getElementById('cameraStatus');
    const idInput = document.getElementById('idDocument');
    const idPreview = document.getElementById('idPreview');
    const idPrompt = document.getElementById('idPrompt');
    const idStatus = document.getElementById('idStatus');
    const matchStatus = document.getElementById('matchStatus');
    const reviewStatus = document.getElementById('reviewStatus');
    const reviewForm = document.getElementById('identityReviewForm');
    const reviewDecision = document.getElementById('reviewDecision');
    const reviewButtons = reviewForm.querySelectorAll('button[type="submit"]');

    const distanceThreshold = 0.6;
    let cameraStream = null;
    let captured = false;
    let selectedId = false;
    let idImageUrl = null;
    let modelsPromise = null;
    let comparisonRun = 0;
    let comparing = false;

    function updateReviewAvailability() {
        const ready = captured && selectedId && !comparing;
        reviewButtons.forEach(button => {
            button.disabled = !ready;
        });

        if (!captured || !selectedId) {
            reviewStatus.textContent = 'Capture a photo and select an ID image to enable review.';
        } else if (comparing) {
            reviewStatus.textContent = 'Comparing faces on this device. Review decisions will be enabled when the comparison finishes.';
        } else {
            reviewStatus.textContent = 'Review both images and choose the decision that reflects your assessment. The automated result is advisory only.';
        }
    }

    reviewButtons.forEach(button => {
        button.addEventListener('click', () => {
            reviewDecision.value = button.value;
        });
    });

    function loadModels() {
        if (!modelsPromise) {
            const modelPath = `${check.dataset.modelPath}/`;
            modelsPromise = Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(modelPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelPath),
            ]);
        }

        return modelsPromise;
    }

    async function describeSingleFace(image) {
        const faces = await faceapi
            .detectAllFaces(
                image,
                new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }),
            )
            .withFaceLandmarks()
            .withFaceDescriptors();

        return faces.length === 1 ? faces[0].descriptor : null;
    }

    async function compareFaces() {
        if (!captured || !selectedId) {
            return;
        }

        const currentRun = ++comparisonRun;
        comparing = true;
        matchStatus.textContent = 'Loading the local face model and comparing both images…';
        matchStatus.className = '';
        updateReviewAvailability();

        try {
            await loadModels();
            const [claimantDescriptor, idDescriptor] = await Promise.all([
                describeSingleFace(captureCanvas),
                describeSingleFace(idPreview),
            ]);

            if (currentRun !== comparisonRun) {
                return;
            }

            if (!claimantDescriptor || !idDescriptor) {
                matchStatus.textContent = 'No comparison result: each image must contain exactly one clearly detectable face. Please review the images manually.';
                matchStatus.className = 'match-unavailable';
                return;
            }

            const distance = faceapi.euclideanDistance(claimantDescriptor, idDescriptor);
            const distanceText = distance.toFixed(2);

            if (distance <= distanceThreshold) {
                matchStatus.textContent = `Possible match (face distance ${distanceText}). This is an uncalibrated advisory result, not identity proof.`;
                matchStatus.className = 'match-possible';
            } else {
                matchStatus.textContent = `Possible mismatch (face distance ${distanceText}). This is an uncalibrated advisory result; verify manually.`;
                matchStatus.className = 'match-unlikely';
            }
        } catch (error) {
            if (currentRun !== comparisonRun) {
                return;
            }

            console.error('On-device face comparison failed.', error);
            matchStatus.textContent = 'Automatic comparison is unavailable. Please inspect both images and make the review decision manually.';
            matchStatus.className = 'match-unavailable';
        } finally {
            if (currentRun === comparisonRun) {
                comparing = false;
                updateReviewAvailability();
            }
        }
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
                video: { facingMode: 'user' },
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
                NotReadableError: 'The camera is in use by another application.',
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
        const context = captureCanvas.getContext('2d');

        if (!context) {
            cameraStatus.textContent = 'Photo capture is unavailable in this browser.';
            return;
        }

        context.drawImage(camera, 0, 0);
        cameraStream.getTracks().forEach(track => track.stop());
        camera.srcObject = null;
        cameraStream = null;
        captureCanvas.classList.remove('hidden');
        camera.classList.add('hidden');
        captured = true;
        captureButton.disabled = true;
        retakeButton.hidden = false;
        cameraStatus.textContent = 'Photo captured. Face comparison runs only on this device.';
        updateReviewAvailability();
        void compareFaces();
    });

    retakeButton.addEventListener('click', () => {
        comparisonRun++;
        comparing = false;
        captured = false;
        captureCanvas.classList.add('hidden');
        camera.classList.add('hidden');
        cameraPrompt.classList.remove('hidden');
        startCameraButton.disabled = false;
        captureButton.disabled = true;
        retakeButton.hidden = true;
        cameraStatus.textContent = 'Start the camera to retake.';
        matchStatus.textContent = 'Capture a photo and select an ID image to compare faces.';
        matchStatus.className = '';
        updateReviewAvailability();
    });

    idInput.addEventListener('change', () => {
        comparisonRun++;
        comparing = false;
        selectedId = false;
        if (idImageUrl) {
            URL.revokeObjectURL(idImageUrl);
        }
        idImageUrl = null;
        idPreview.classList.add('hidden');
        idPreview.removeAttribute('src');
        idPrompt.classList.remove('hidden');
        matchStatus.textContent = 'Capture a photo and select an ID image to compare faces.';
        matchStatus.className = '';

        const file = idInput.files[0];
        if (!file) {
            idStatus.textContent = '';
            updateReviewAvailability();
            return;
        }

        const isSupportedImage = ['image/jpeg', 'image/png'].includes(file.type)
            || /\.(jpe?g|png)$/i.test(file.name);
        if (!isSupportedImage) {
            idInput.value = '';
            idStatus.textContent = 'Choose a JPG or PNG image. PDF and other file types are not supported for automatic comparison.';
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
        idPreview.onload = () => {
            selectedId = true;
            idStatus.textContent = 'ID image ready. Both images will be analyzed on this device only.';
            updateReviewAvailability();
            void compareFaces();
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

    reviewForm.addEventListener('submit', event => {
        if (!captured || !selectedId || comparing) {
            event.preventDefault();
            reviewStatus.textContent = 'Wait for the local comparison to finish and ensure both images are ready.';
            return;
        }

        const decision = reviewDecision.value || event.submitter?.value;
        if (!['matched', 'not_matched'].includes(decision)) {
            event.preventDefault();
            reviewStatus.textContent = 'Choose an identity decision before submitting the review.';
            return;
        }

        reviewDecision.value = decision;
    });

    window.addEventListener('pagehide', () => {
        cameraStream?.getTracks().forEach(track => track.stop());
        if (idImageUrl) {
            URL.revokeObjectURL(idImageUrl);
        }
    });

    updateReviewAvailability();
}
