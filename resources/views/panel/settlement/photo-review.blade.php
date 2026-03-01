@extends('layouts.panel')

@section('title', 'Vardiya Uyumluluk İncelemesi')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Vardiya Uyumluluk İncelemesi</h1>
        <p class="text-gray-500 mt-1">Sadece vardiya <strong>başlangıç</strong> fotoğrafları değerlendirilir; bitiş fotoğrafı dikkate alınmaz. Prim verin, prim vermeyin veya tekrar fotoğraf isteyin (vardiya tamamlanmamış olsa da seçenekler geçerlidir).</p>
    </div>
    <a href="{{ route('panel.settlement.settings') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
        Hakediş ayarları (prim tutarı bölgeye göre)
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
@endif

@if($shifts->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        İncelenecek vardiya başlangıç fotoğrafı yok. Tamamlanmış vardiyalar burada listelenir.
    </div>
@else
    <div class="space-y-6">
        @foreach($shifts as $shift)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="font-semibold text-gray-800">{{ $shift->user->name }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $shift->started_at->translatedFormat('d F Y') }} · {{ $shift->started_at->format('H:i') }} - {{ $shift->ended_at?->format('H:i') }}
                            · {{ $shift->formatted_duration }}
                        </p>
                    </div>
                    @php $shiftSettings = \App\Models\SettlementSetting::getForRegion($shift->region_id); @endphp
                    <div class="flex flex-wrap items-center gap-2">
                        <form action="{{ route('panel.settlement.photo-review.approve', $shift) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Prim Ver ({{ number_format($shiftSettings->photo_compliance_bonus, 2, ',', '.') }} TL)</button>
                        </form>
                        <form action="{{ route('panel.settlement.photo-review.no-bonus', $shift) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600">Prim Verme</button>
                        </form>
                        <form action="{{ route('panel.settlement.photo-review.request-retry', $shift) }}" method="POST" class="inline" onsubmit="return confirm('Kuryeden tekrar vardiya başlangıç fotoğrafı yüklemesi istenecek. Mevcut fotoğraflar silinmeyecek. Onaylıyor musunuz?');">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700">Tekrar fotoğraf iste</button>
                        </form>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php $startPhotos = $shift->photos->where('type', 'start')->sortBy('is_retry'); @endphp
                    @forelse($startPhotos as $photo)
                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-1">
                                Vardiya başlangıç {{ $photo->is_retry ? '(2. yükleme)' : '' }}
                            </p>
                            <a href="{{ $photo->url }}" target="_blank" class="block rounded-lg border border-gray-200 overflow-hidden hover:opacity-90">
                                <img src="{{ $photo->url }}" alt="Vardiya başlangıç" class="w-full h-36 object-cover">
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-amber-600">Bu vardiya için başlangıç fotoğrafı yok.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $shifts->links() }}
    </div>
@endif
@endsection
