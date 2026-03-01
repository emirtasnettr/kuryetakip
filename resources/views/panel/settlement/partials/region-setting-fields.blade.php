@php
    $s = $setting ?? new \App\Models\SettlementSetting(['hourly_rate' => 0, 'photo_compliance_bonus' => 0, 'package_rate' => 0, 'vat_rate' => 18]);
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Saatlik ücret (TL, KDV dahil)</label>
        <input type="number" name="settings[{{ $key }}][hourly_rate]" step="0.01" min="0" required
               value="{{ old("settings.{$key}.hourly_rate", $s->hourly_rate) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        @error("settings.{$key}.hourly_rate")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Paket başı ücret (TL, KDV dahil)</label>
        <input type="number" name="settings[{{ $key }}][package_rate]" step="0.01" min="0" required
               value="{{ old("settings.{$key}.package_rate", $s->package_rate ?? 0) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        @error("settings.{$key}.package_rate")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Vardiya uyumluluk primi (TL, KDV dahil)</label>
        <input type="number" name="settings[{{ $key }}][photo_compliance_bonus]" step="0.01" min="0" required
               value="{{ old("settings.{$key}.photo_compliance_bonus", $s->photo_compliance_bonus) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        @error("settings.{$key}.photo_compliance_bonus")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">KDV oranı (%)</label>
        <input type="number" name="settings[{{ $key }}][vat_rate]" step="0.01" min="0" max="100"
               value="{{ old("settings.{$key}.vat_rate", $s->vat_rate ?? 18) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        @error("settings.{$key}.vat_rate")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
