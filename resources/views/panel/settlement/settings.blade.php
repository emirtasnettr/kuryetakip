@extends('layouts.panel')

@section('title', 'Hakediş Ayarları')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Hakediş Ayarları</h1>
    <p class="text-gray-500 mt-1">Her bölge için saatlik ücret, paket ücreti, vardiya uyumluluk primi ve KDV oranını ayrı ayrı belirleyin. Her kartı kendi Kaydet butonu ile kaydedin.</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif

@php
    $cards = $regions->map(fn ($region) => (object)[
        'key' => (string) $region->id,
        'title' => $region->name,
        'subtitle' => $region->city ?? '',
        'setting' => $regionSettings[$region->id] ?? null,
    ])->all();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach($cards as $card)
        @php
            $s = $card->setting ?? new \App\Models\SettlementSetting(['hourly_rate' => 0, 'photo_compliance_bonus' => 0, 'package_rate' => 0, 'vat_rate' => 18]);
            $isSubmitted = old('region_key') === $card->key;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">{{ $card->title }}</h2>
                @if($card->subtitle)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $card->subtitle }}</p>
                @endif
            </div>
            <form action="{{ route('panel.settlement.settings.update') }}" method="POST" class="p-4 flex-1 flex flex-col">
                @csrf
                <input type="hidden" name="region_key" value="{{ $card->key }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Saatlik ücret (TL)</label>
                        <input type="number" name="hourly_rate" step="0.01" min="0" required
                               value="{{ $isSubmitted ? old('hourly_rate', $s->hourly_rate) : $s->hourly_rate }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($isSubmitted)
                            @error('hourly_rate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Paket başı (TL)</label>
                        <input type="number" name="package_rate" step="0.01" min="0" required
                               value="{{ $isSubmitted ? old('package_rate', $s->package_rate ?? 0) : ($s->package_rate ?? 0) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($isSubmitted)
                            @error('package_rate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vardiya Uyumluluk Primi (TL)</label>
                        <input type="number" name="photo_compliance_bonus" step="0.01" min="0" required
                               value="{{ $isSubmitted ? old('photo_compliance_bonus', $s->photo_compliance_bonus) : $s->photo_compliance_bonus }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($isSubmitted)
                            @error('photo_compliance_bonus')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">KDV (%)</label>
                        <input type="number" name="vat_rate" step="0.01" min="0" max="100"
                               value="{{ $isSubmitted ? old('vat_rate', $s->vat_rate ?? 18) : ($s->vat_rate ?? 18) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($isSubmitted)
                            @error('vat_rate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="has_guaranteed_package" value="0">
                            <input type="checkbox" name="has_guaranteed_package" value="1"
                                   {{ ($isSubmitted ? old('has_guaranteed_package', $s->has_guaranteed_package ?? false) : ($s->has_guaranteed_package ?? false)) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Saatlik garanti paket var</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Açıksa, vardiyada teslim edilen paket 12’nin altındaysa 12 paket kabul edilir (saatlik garanti × vardiya saati, max’e kadar).</p>
                    </div>
                    <div class="guaranteed-fields">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Saatlik garanti paket</label>
                            <input type="number" name="guaranteed_packages_per_hour" step="0.01" min="0"
                                   value="{{ $isSubmitted ? old('guaranteed_packages_per_hour', $s->guaranteed_packages_per_hour ?? 1) : ($s->guaranteed_packages_per_hour ?? 1) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @if($isSubmitted)
                                @error('guaranteed_packages_per_hour')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Vardiya başı max garanti paket</label>
                            <input type="number" name="max_guaranteed_packages_per_shift" min="0" step="1"
                                   value="{{ $isSubmitted ? old('max_guaranteed_packages_per_shift', $s->max_guaranteed_packages_per_shift ?? 12) : ($s->max_guaranteed_packages_per_shift ?? 12) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @if($isSubmitted)
                                @error('max_guaranteed_packages_per_shift')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Kaydet</button>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection
