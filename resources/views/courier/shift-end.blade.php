@extends('layouts.courier')

@section('title', 'Vardiyayı Bitir')
@section('back_url', route('courier.home'))

@section('content')
<div class="p-4">
    
    <!-- Current Shift Info -->
    <div class="bg-black text-white rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="font-semibold">Aktif Vardiya</span>
            <span class="text-gray-300">{{ $activeShift->started_at->format('H:i') }}'den beri</span>
        </div>
        <div class="text-2xl font-bold">{{ $activeShift->formatted_duration }}</div>
        @if($activeShift->district)
            <div class="text-gray-300 text-sm mt-1">{{ $activeShift->district->name }}</div>
        @endif
    </div>
    
    <form method="POST" action="{{ route('courier.shift.end.submit') }}" enctype="multipart/form-data" id="endForm">
        @csrf
        
        <!-- Hidden location fields -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        
        <!-- Location Status -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">Bitiş Konumu</h3>
            
            <div id="locationStatus" class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="spinner mr-3"></div>
                <span class="text-gray-600">Konum alınıyor...</span>
            </div>
            
            <div id="locationSuccess" class="hidden items-center p-3 bg-green-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-green-700">Konum alındı</span>
            </div>
            
            <div id="locationError" class="hidden items-center p-3 bg-red-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-red-700" id="locationErrorText">Konum alınamadı</span>
            </div>
        </div>
        
        <!-- Package Count -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">
                Bugün Kaç Paket Attın?
                <span class="text-red-500">*</span>
            </h3>
            
            <input 
                type="number" 
                name="package_count" 
                id="packageCount"
                value="{{ old('package_count') }}"
                min="0"
                inputmode="numeric"
                pattern="[0-9]*"
                placeholder="Paket sayısı girin (0 olabilir)"
                required
                class="w-full text-center text-2xl font-bold py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('package_count') border-red-500 @enderror"
            >
            
            <!-- Package Count Required Warning -->
            <div id="packageCountRequired" class="mt-2 text-sm text-amber-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Paket sayısı girilmelidir (0 girebilirsiniz)</span>
            </div>
            
            @error('package_count')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Photo Capture: Sadece anlık kamera çekimi (galeri yok) -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">
                Bitiş Fotoğrafı
                <span class="text-red-500">*</span>
            </h3>
            <p class="text-sm text-gray-500 mb-3">Fotoğraf anlık olarak kameradan çekilmelidir; galeriden seçim yapılamaz.</p>
            
            <input type="file" name="photo" accept="image/*" class="hidden" id="photoInput" required>
            
            <div id="photoStartArea" class="mb-3">
                <button type="button" id="openCameraBtn" class="flex items-center justify-center w-full py-4 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-black hover:text-black transition-colors bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Kamerayı Aç ve Fotoğraf Çek</span>
                </button>
            </div>
            
            <div id="cameraArea" class="hidden mb-3">
                <div class="relative rounded-lg overflow-hidden bg-black">
                    <video id="cameraVideo" autoplay playsinline muted class="w-full max-h-[280px] object-cover"></video>
                    <button type="button" id="captureBtn" class="absolute bottom-3 left-1/2 -translate-x-1/2 px-6 py-3 bg-white text-black font-semibold rounded-full shadow-lg hover:bg-gray-100">
                        Fotoğrafı Çek
                    </button>
                </div>
                <button type="button" id="closeCameraBtn" class="mt-2 text-sm text-gray-500 hover:text-gray-700">İptal</button>
            </div>
            
            <div id="photoPreview" class="hidden mb-3">
                <img id="previewImage" src="" alt="Önizleme" class="w-full h-48 object-cover rounded-lg">
                <button type="button" id="retakePhotoBtn" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Yeniden Çek</button>
            </div>
            
            <div id="photoRequired" class="mt-2 text-sm text-amber-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Anlık fotoğraf çekmek zorunludur</span>
            </div>
            
            <div id="cameraPermissionError" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-red-700 font-medium">Kamera erişim izni verilmedi</p>
                        <p class="text-xs text-red-600 mt-1">Anlık fotoğraf çekebilmek için kamera iznini verin.</p>
                        <button type="button" id="requestCameraPermission" class="mt-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            İzin Ver
                        </button>
                    </div>
                </div>
            </div>
            
            @error('photo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Notes -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Not (Opsiyonel)</h3>
            
            <textarea 
                name="notes" 
                rows="3"
                placeholder="Vardiya hakkında not ekleyebilirsiniz..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black resize-none"
            >{{ old('notes') }}</textarea>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit" 
            id="submitBtn"
            disabled
            class="w-full bg-black text-white py-4 rounded-xl font-semibold hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
            <span id="submitText">Vardiyayı Bitir</span>
            <span id="submitLoading" class="hidden">
                <span class="inline-block spinner border-white border-t-transparent"></span>
                Tamamlanıyor...
            </span>
        </button>
        
    </form>
</div>

@push('scripts')
<script>
    let locationReady = false;
    let photoReady = false;
    let packageCountReady = false;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                
                document.getElementById('locationStatus').classList.add('hidden');
                document.getElementById('locationSuccess').classList.remove('hidden');
                document.getElementById('locationSuccess').classList.add('flex');
                
                locationReady = true;
                updateSubmitButton();
            },
            function(error) {
                let errorText = 'Konum alınamadı. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorText += 'Konum izni verilmedi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorText += 'Konum bilgisi mevcut değil.';
                        break;
                    case error.TIMEOUT:
                        errorText += 'Zaman aşımı.';
                        break;
                }
                
                document.getElementById('locationStatus').classList.add('hidden');
                document.getElementById('locationError').classList.remove('hidden');
                document.getElementById('locationError').classList.add('flex');
                document.getElementById('locationErrorText').textContent = errorText;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    // Package count validation
    const packageCountInput = document.getElementById('packageCount');
    const packageCountWarning = document.getElementById('packageCountRequired');
    
    function checkPackageCount() {
        const value = packageCountInput.value;
        // Check if value is entered (0 is valid, empty is not)
        packageCountReady = value !== '' && !isNaN(parseInt(value)) && parseInt(value) >= 0;
        
        if (packageCountReady) {
            packageCountWarning.classList.add('hidden');
            packageCountInput.classList.remove('border-red-500');
            packageCountInput.classList.add('border-green-500');
        } else {
            packageCountWarning.classList.remove('hidden');
            packageCountInput.classList.remove('border-green-500');
        }
        
        updateSubmitButton();
    }
    
    packageCountInput.addEventListener('input', checkPackageCount);
    packageCountInput.addEventListener('change', checkPackageCount);
    
    // Check on page load if there's an old value
    if (packageCountInput.value !== '') {
        checkPackageCount();
    }
    
    function showCameraPermissionError() {
        document.getElementById('cameraPermissionError').classList.remove('hidden');
        document.getElementById('photoRequired').classList.add('hidden');
    }
    function hideCameraPermissionError() {
        document.getElementById('cameraPermissionError').classList.add('hidden');
    }
    
    let cameraStream = null;
    function setPhotoFile(blob) {
        const file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('photoInput').files = dt.files;
        document.getElementById('previewImage').src = URL.createObjectURL(blob);
        document.getElementById('photoPreview').classList.remove('hidden');
        document.getElementById('photoStartArea').classList.add('hidden');
        document.getElementById('photoRequired').classList.add('hidden');
        photoReady = true;
        updateSubmitButton();
    }
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
        document.getElementById('cameraArea').classList.add('hidden');
        document.getElementById('cameraVideo').srcObject = null;
    }
    
    document.getElementById('openCameraBtn').addEventListener('click', async function() {
        hideCameraPermissionError();
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            document.getElementById('cameraVideo').srcObject = cameraStream;
            document.getElementById('photoStartArea').classList.add('hidden');
            document.getElementById('cameraArea').classList.remove('hidden');
        } catch (err) {
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                showCameraPermissionError();
            } else {
                alert('Kameraya erişilemiyor: ' + (err.message || 'Bilinmeyen hata'));
            }
        }
    });
    document.getElementById('closeCameraBtn').addEventListener('click', function() {
        stopCamera();
        document.getElementById('photoStartArea').classList.remove('hidden');
    });
    document.getElementById('captureBtn').addEventListener('click', function() {
        const video = document.getElementById('cameraVideo');
        if (!video.srcObject || !video.videoWidth) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(function(blob) {
            if (blob) { stopCamera(); setPhotoFile(blob); }
        }, 'image/jpeg', 0.92);
    });
    document.getElementById('retakePhotoBtn').addEventListener('click', function() {
        document.getElementById('photoPreview').classList.add('hidden');
        document.getElementById('photoStartArea').classList.remove('hidden');
        document.getElementById('photoInput').value = '';
        photoReady = false;
        updateSubmitButton();
        document.getElementById('photoRequired').classList.remove('hidden');
    });
    document.getElementById('requestCameraPermission').addEventListener('click', function() {
        document.getElementById('openCameraBtn').click();
    });
    
    function updateSubmitButton() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = !(locationReady && photoReady && packageCountReady);
    }
    
    document.getElementById('endForm').addEventListener('submit', function(e) {
        if (!locationReady) { e.preventDefault(); alert('Konum bilgisi alınamadı.'); return; }
        if (!packageCountReady) { e.preventDefault(); alert('Lütfen paket sayısını girin. 0 girebilirsiniz.'); packageCountInput.focus(); return; }
        if (!photoReady) { e.preventDefault(); alert('Lütfen bitiş fotoğrafını kameradan çekin.'); return; }
        stopCamera();
        document.getElementById('submitText').classList.add('hidden');
        document.getElementById('submitLoading').classList.remove('hidden');
        document.getElementById('submitBtn').disabled = true;
    });
    
    window.addEventListener('pagehide', stopCamera);
</script>
@endpush
@endsection
