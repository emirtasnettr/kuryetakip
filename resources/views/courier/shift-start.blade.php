@extends('layouts.courier')

@section('title', 'Vardiyaya Başla')
@section('back_url', route('courier.home'))

@section('content')
<div class="p-4">
    
    <!-- Info Card -->
    <div class="bg-gray-100 border border-gray-200 rounded-xl p-4 mb-6">
        <div class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-gray-700">
                <p class="font-medium mb-1">Vardiya Başlatma</p>
                <p>Konum bilginiz ve fotoğrafınız kaydedilecektir. Lütfen GPS'inizin açık olduğundan emin olun.</p>
            </div>
        </div>
    </div>
    
    <form method="POST" action="{{ route('courier.shift.start.submit') }}" enctype="multipart/form-data" id="startForm">
        @csrf
        
        <!-- Hidden location fields -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        
        <!-- Location Status -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <h3 class="font-semibold text-gray-800 mb-3">Konum Bilgisi</h3>
            
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
            
            @error('latitude')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Photo Capture -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Başlangıç Fotoğrafı</h3>
            
            <div id="photoPreview" class="hidden mb-3">
                <img id="previewImage" src="" alt="Önizleme" class="w-full h-48 object-cover rounded-lg">
            </div>
            
            <label class="block">
                <span class="flex items-center justify-center w-full py-3 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-black hover:text-black transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span id="photoText">Fotoğraf Çek / Seç</span>
                </span>
                <input type="file" name="photo" accept="image/*" capture="environment" class="hidden" id="photoInput">
            </label>
            
            @error('photo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit" 
            id="submitBtn"
            disabled
            class="w-full bg-black text-white py-4 rounded-xl font-semibold hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
            <span id="submitText">Vardiyaya Başla</span>
            <span id="submitLoading" class="hidden">
                <span class="inline-block spinner border-white border-t-transparent"></span>
                Başlatılıyor...
            </span>
        </button>
        
    </form>
</div>

@push('scripts')
<script>
    let locationReady = false;
    
    // Get location on page load
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
    } else {
        document.getElementById('locationStatus').classList.add('hidden');
        document.getElementById('locationError').classList.remove('hidden');
        document.getElementById('locationError').classList.add('flex');
        document.getElementById('locationErrorText').textContent = 'Tarayıcınız konum özelliğini desteklemiyor.';
    }
    
    // Photo preview
    document.getElementById('photoInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('photoPreview').classList.remove('hidden');
                document.getElementById('photoText').textContent = 'Fotoğrafı Değiştir';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    
    // Update submit button
    function updateSubmitButton() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = !locationReady;
    }
    
    // Form submit
    document.getElementById('startForm').addEventListener('submit', function(e) {
        if (!locationReady) {
            e.preventDefault();
            alert('Konum bilgisi alınamadı. Lütfen GPS\'inizi kontrol edin.');
            return;
        }
        
        document.getElementById('submitText').classList.add('hidden');
        document.getElementById('submitLoading').classList.remove('hidden');
        document.getElementById('submitBtn').disabled = true;
    });
</script>
@endpush
@endsection
