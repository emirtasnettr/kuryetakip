@extends('layouts.courier')

@section('title', 'Fotoğraf Talebi')

@section('content')
<div class="p-4 space-y-6">
    <div>
        <a href="{{ route('courier.photo-retry') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1">← Fotoğraf Talepleri</a>
        <h1 class="text-xl font-bold text-gray-800 mt-2">Vardiya Başlangıç Fotoğrafı Yükle</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $shift->started_at->translatedFormat('d F Y') }} · {{ $shift->started_at->format('H:i') }}{{ $shift->ended_at ? ' - ' . $shift->ended_at->format('H:i') : '' }}</p>
    </div>

    <form action="{{ route('courier.photo-retry.upload.submit', $shift) }}" method="POST" enctype="multipart/form-data" id="photoRetryForm" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        @csrf
        <p class="text-sm text-gray-600">Vardiya başlangıç fotoğrafını anlık olarak kameradan çekin. Galeriden seçim yapılamaz. Mevcut fotoğraflar silinmeyecek; inceleme sonrası tekrar Vardiya Uyumluluk İncelemesi'ne düşecektir.</p>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vardiya başlangıç fotoğrafı (tekrar) *</label>
            <input type="file" name="photo_start" accept="image/*" class="hidden" id="photoRetryInput" required>
            <div id="photoRetryStartArea" class="mb-3">
                <button type="button" id="photoRetryOpenCamera" class="flex items-center justify-center w-full py-4 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-black hover:text-black bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Kamerayı Aç ve Fotoğraf Çek
                </button>
            </div>
            <div id="photoRetryCameraArea" class="hidden mb-3">
                <div class="relative rounded-lg overflow-hidden bg-black">
                    <video id="photoRetryVideo" autoplay playsinline muted class="w-full max-h-[280px] object-cover"></video>
                    <button type="button" id="photoRetryCapture" class="absolute bottom-3 left-1/2 -translate-x-1/2 px-6 py-3 bg-white text-black font-semibold rounded-full shadow-lg">Fotoğrafı Çek</button>
                </div>
                <button type="button" id="photoRetryCloseCamera" class="mt-2 text-sm text-gray-500 hover:text-gray-700">İptal</button>
            </div>
            <div id="photoRetryPreview" class="hidden mb-3">
                <img id="photoRetryPreviewImg" src="" alt="Önizleme" class="w-full h-48 object-cover rounded-lg">
                <button type="button" id="photoRetryRetake" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Yeniden Çek</button>
            </div>
            @error('photo_start')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" id="photoRetrySubmit" disabled class="w-full py-3 bg-black text-white font-semibold rounded-lg hover:bg-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed">Gönder</button>
    </form>
</div>

@push('scripts')
<script>
(function() {
    let stream = null;
    function stopCam() {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        document.getElementById('photoRetryCameraArea').classList.add('hidden');
        document.getElementById('photoRetryVideo').srcObject = null;
    }
    function setFile(blob) {
        const file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('photoRetryInput').files = dt.files;
        document.getElementById('photoRetryPreviewImg').src = URL.createObjectURL(blob);
        document.getElementById('photoRetryPreview').classList.remove('hidden');
        document.getElementById('photoRetryStartArea').classList.add('hidden');
        document.getElementById('photoRetrySubmit').disabled = false;
    }
    document.getElementById('photoRetryOpenCamera').addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            document.getElementById('photoRetryVideo').srcObject = stream;
            document.getElementById('photoRetryStartArea').classList.add('hidden');
            document.getElementById('photoRetryCameraArea').classList.remove('hidden');
        } catch (e) {
            alert(e.name === 'NotAllowedError' ? 'Kamera izni verin.' : 'Kameraya erişilemiyor.');
        }
    });
    document.getElementById('photoRetryCloseCamera').addEventListener('click', function() { stopCam(); document.getElementById('photoRetryStartArea').classList.remove('hidden'); });
    document.getElementById('photoRetryCapture').addEventListener('click', function() {
        const video = document.getElementById('photoRetryVideo');
        if (!video.srcObject || !video.videoWidth) return;
        const c = document.createElement('canvas');
        c.width = video.videoWidth;
        c.height = video.videoHeight;
        c.getContext('2d').drawImage(video, 0, 0);
        c.toBlob(function(b) { if (b) { stopCam(); setFile(b); } }, 'image/jpeg', 0.92);
    });
    document.getElementById('photoRetryRetake').addEventListener('click', function() {
        document.getElementById('photoRetryPreview').classList.add('hidden');
        document.getElementById('photoRetryStartArea').classList.remove('hidden');
        document.getElementById('photoRetryInput').value = '';
        document.getElementById('photoRetrySubmit').disabled = true;
    });
    document.getElementById('photoRetryForm').addEventListener('submit', function() { stopCam(); });
    window.addEventListener('pagehide', stopCam);
})();
</script>
@endpush
@endsection
