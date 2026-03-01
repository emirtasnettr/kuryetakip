@extends('layouts.panel')

@section('title', 'Hakediş Hesaplama')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Hakediş Hesaplama</h1>
    <p class="text-gray-500 mt-1">Tarih aralığına göre kuryelerin hakediş tutarlarını görüntüleyin. Toplam sütununda KDV hariç ve KDV dahil tutar gösterilir.</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('panel.settlement.calculation') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Başlangıç tarihi</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Bitiş tarihi</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="name_search" class="block text-sm font-medium text-gray-700 mb-1">Kurye adı</label>
            <input type="text" name="name" id="name_search" value="{{ old('name', $nameSearch ?? '') }}"
                   placeholder="İsimle ara…"
                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 w-full min-w-[160px]">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Hesapla</button>
        @if(!empty($nameSearch ?? ''))
            <a href="{{ route('panel.settlement.calculation', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-3 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg text-sm">Filtreyi temizle</a>
        @endif
        <a href="{{ route('panel.settlement.calculation.export', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'name' => $nameSearch ?? ''])) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vardiya</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Süre (saat)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Süre tutarı (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paket</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paket tutarı (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vardiya uyumluluk primi (adet)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vardiya uyumluluk primi (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Hakedişten düşülen (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kesinti (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ek Prim (TL)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam (KDV hariç)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam (KDV dahil)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['courier']->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['shift_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row['hours'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row['hourly_earnings'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['package_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row['package_earnings'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['photo_bonus_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row['photo_bonus_total'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if($row['deduct_from_settlement_total'] > 0)
                                <button type="button" class="text-amber-600 hover:text-amber-800 underline cursor-pointer js-expense-deduction-detail"
                                        data-courier-id="{{ $row['courier']->id }}"
                                        data-courier-name="{{ $row['courier']->name }}"
                                        data-type="deduct_from_settlement"
                                        data-start-date="{{ $startDate }}"
                                        data-end-date="{{ $endDate }}">
                                    {{ number_format($row['deduct_from_settlement_total'], 2, ',', '.') }}
                                </button>
                            @else
                                {{ number_format($row['deduct_from_settlement_total'], 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-red-600 text-right">{{ number_format($row['deduction_total'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if($row['extra_bonus_total'] > 0)
                                <button type="button" class="text-indigo-600 hover:text-indigo-800 underline cursor-pointer js-extra-bonus-detail"
                                        data-courier-id="{{ $row['courier']->id }}"
                                        data-courier-name="{{ $row['courier']->name }}"
                                        data-start-date="{{ $startDate }}"
                                        data-end-date="{{ $endDate }}">
                                    {{ number_format($row['extra_bonus_total'], 2, ',', '.') }}
                                </button>
                            @else
                                {{ number_format($row['extra_bonus_total'], 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($settings->toExclVat($row['total']), 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($row['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-4 py-8 text-center text-gray-500">Bu tarih aralığında veri yok veya erişebildiğiniz kurye bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(!empty($rows))
            <tfoot class="bg-gray-100 font-semibold">
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">Toplam</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $totals['shift_count'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['hours'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['hourly_earnings'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $totals['package_count'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['package_earnings'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $totals['photo_bonus_count'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['photo_bonus_total'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['deduct_from_settlement_total'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-red-600 text-right">{{ number_format($totals['deduction_total'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totals['extra_bonus_total'], 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($settings->toExclVat($totals['total']), 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-indigo-600 font-semibold text-right">{{ number_format($totals['total'], 2, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if(!empty($rows))
<div class="mt-4 text-sm text-gray-500">
    <p>Birim fiyatlar KDV dahil. Toplam sütununda KDV hariç tutar, ayarlardaki KDV oranına göre hesaplanır (şu an %{{ number_format($settings->vat_rate ?? 18, 0) }}).</p>
</div>
@endif

<div class="mt-8 pt-6 border-t border-gray-200">
    <button type="button" id="js-open-extra-bonus-modal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        Ekstra Prim Ekle
    </button>
</div>

{{-- Modal: Ekstra Prim Ekle --}}
<div id="extra-bonus-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('extra-bonus-modal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ekstra Prim Ekle</h3>
            <form action="{{ route('panel.settlement.extra-bonus.store') }}" method="POST">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                @if(!empty($nameSearch ?? ''))
                    <input type="hidden" name="name" value="{{ $nameSearch }}">
                @endif
                <div class="space-y-4">
                    <div>
                        <label for="extra_bonus_courier_trigger" class="block text-sm font-medium text-gray-700 mb-1">Kurye <span class="text-red-500">*</span></label>
                        <input type="hidden" name="user_id" id="extra_bonus_user_id" value="" required>
                        <div class="relative" id="extra-bonus-courier-wrap">
                            <button type="button" id="extra_bonus_courier_trigger" aria-haspopup="listbox" aria-expanded="false"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-left bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 flex items-center justify-between">
                                <span id="extra_bonus_courier_label" class="text-gray-500">Kurye seçin</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="extra_bonus_courier_dropdown" class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 flex flex-col">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" id="extra_bonus_courier_search" placeholder="Kurye ara..." autocomplete="off"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <ul id="extra_bonus_courier_list" role="listbox" class="overflow-y-auto py-1 max-h-48">
                                    @foreach($couriers as $c)
                                        <li role="option" data-value="{{ $c->id }}" data-name="{{ e($c->name) }}" class="extra-bonus-courier-option px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50 text-gray-900">{{ $c->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="extra_bonus_bonus_date" class="block text-sm font-medium text-gray-700 mb-1">Hakedişe yansıyacağı tarih</label>
                        <input type="date" name="bonus_date" id="extra_bonus_bonus_date" value="{{ $endDate }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="extra_bonus_reason" class="block text-sm font-medium text-gray-700 mb-1">Prim nedeni</label>
                        <textarea name="reason" id="extra_bonus_reason" rows="3" required maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Örn: Özel gün performans primi"></textarea>
                    </div>
                    <div>
                        <label for="extra_bonus_amount" class="block text-sm font-medium text-gray-700 mb-1">Tutar (KDV dahil, TL)</label>
                        <input type="number" name="amount" id="extra_bonus_amount" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="0,00">
                    </div>
                </div>
                <div class="mt-6 flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('extra-bonus-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Hakedişten düşülen / Borç bakiyesi detay listesi --}}
<div id="expense-deduction-detail-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('expense-deduction-detail-modal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-2"><span id="exp-detail-courier-name"></span> — <span id="exp-detail-title"></span></h3>
            <p class="text-sm text-gray-500 mb-4" id="exp-detail-date-range"></p>
            <div id="exp-detail-body" class="overflow-y-auto flex-1">
                <p class="text-gray-500">Yükleniyor…</p>
            </div>
            <div class="mt-4 pt-4 border-t flex justify-end">
                <button type="button" onclick="document.getElementById('expense-deduction-detail-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Kapat</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Ek Prim detay listesi --}}
<div id="extra-bonus-detail-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('extra-bonus-detail-modal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-2"><span id="detail-modal-courier-name"></span> — Ek Primler</h3>
            <p class="text-sm text-gray-500 mb-4" id="detail-modal-date-range"></p>
            <div id="detail-modal-body" class="overflow-y-auto flex-1">
                <p class="text-gray-500">Yükleniyor…</p>
            </div>
            <div class="mt-4 pt-4 border-t flex justify-end">
                <button type="button" onclick="document.getElementById('extra-bonus-detail-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Kapat</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openExtraBonusModal() {
    document.getElementById('extra-bonus-modal').classList.remove('hidden');
    var hiddenInput = document.getElementById('extra_bonus_user_id');
    var label = document.getElementById('extra_bonus_courier_label');
    var searchInput = document.getElementById('extra_bonus_courier_search');
    if (hiddenInput) hiddenInput.value = '';
    if (label) { label.textContent = 'Kurye seçin'; label.classList.add('text-gray-500'); }
    if (searchInput) searchInput.value = '';
    filterExtraBonusCourierOptions('');
}
function filterExtraBonusCourierOptions(q) {
    var list = document.getElementById('extra_bonus_courier_list');
    if (!list) return;
    q = (q || '').toLowerCase().trim();
    var options = list.querySelectorAll('.extra-bonus-courier-option');
    options.forEach(function(opt) {
        var name = (opt.getAttribute('data-name') || opt.textContent || '').toLowerCase();
        opt.style.display = !q || name.indexOf(q) !== -1 ? '' : 'none';
    });
}
(function initExtraBonusCourierDropdown() {
    var trigger = document.getElementById('extra_bonus_courier_trigger');
    var dropdown = document.getElementById('extra_bonus_courier_dropdown');
    var searchInput = document.getElementById('extra_bonus_courier_search');
    var list = document.getElementById('extra_bonus_courier_list');
    var hiddenInput = document.getElementById('extra_bonus_user_id');
    var label = document.getElementById('extra_bonus_courier_label');
    if (searchInput) {
        searchInput.addEventListener('input', function() { filterExtraBonusCourierOptions(this.value); });
        searchInput.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !dropdown.classList.contains('hidden');
            dropdown.classList.toggle('hidden', isOpen);
            trigger.setAttribute('aria-expanded', !isOpen);
            if (!isOpen && searchInput) searchInput.focus();
        });
    }
    if (list) {
        list.addEventListener('click', function(e) {
            var opt = e.target.closest('.extra-bonus-courier-option');
            if (!opt) return;
            var val = opt.getAttribute('data-value');
            var name = opt.getAttribute('data-name') || opt.textContent;
            if (hiddenInput) hiddenInput.value = val;
            if (label) { label.textContent = name; label.classList.remove('text-gray-500'); }
            dropdown.classList.add('hidden');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }
    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('extra-bonus-courier-wrap');
        if (wrap && !wrap.contains(e.target)) {
            if (dropdown) dropdown.classList.add('hidden');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        }
    });
})();
document.getElementById('js-open-extra-bonus-modal')?.addEventListener('click', openExtraBonusModal);
if (window.location.search.includes('open_extra_bonus=1')) {
    openExtraBonusModal();
    if (window.history.replaceState) {
        var p = new URLSearchParams(window.location.search);
        p.delete('open_extra_bonus');
        var qs = p.toString();
        window.history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : '') + (window.location.hash || ''));
    }
}

document.querySelectorAll('.js-expense-deduction-detail').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var cid = this.getAttribute('data-courier-id');
        var cname = this.getAttribute('data-courier-name');
        var type = this.getAttribute('data-type');
        var start = this.getAttribute('data-start-date');
        var end = this.getAttribute('data-end-date');
        document.getElementById('exp-detail-courier-name').textContent = cname;
        document.getElementById('exp-detail-date-range').textContent = start + ' - ' + end;
        document.getElementById('exp-detail-body').innerHTML = '<p class="text-gray-500">Yükleniyor…</p>';
        document.getElementById('expense-deduction-detail-modal').classList.remove('hidden');

        var qs = new URLSearchParams({ courier_id: cid, type: type, start_date: start, end_date: end });
        fetch('{{ route('panel.settlement.expense-deductions.list') }}?' + qs.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function(r) { return r.json(); }).then(function(data) {
            document.getElementById('exp-detail-title').textContent = data.title || '';
            var html = '';
            if (data.items && data.items.length) {
                html = '<table class="min-w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2 pr-2">Tarih</th><th class="text-right py-2 pr-2">Tutar</th><th class="text-left py-2">Sebep / Sipariş</th></tr></thead><tbody>';
                data.items.forEach(function(row) {
                    var reason = (row.order_number ? 'Sipariş: ' + row.order_number + ' · ' : '') + (row.reason || '-');
                    html += '<tr class="border-b border-gray-100"><td class="py-2 pr-2 text-gray-600">' + (row.date || '') + '</td><td class="py-2 pr-2 text-right font-medium">' + (row.amount || '') + ' TL</td><td class="py-2">' + reason + '</td></tr>';
                });
                html += '</tbody></table>';
            } else {
                html = '<p class="text-gray-500">Bu tarih aralığında kayıt yok.</p>';
            }
            document.getElementById('exp-detail-body').innerHTML = html;
        }).catch(function() {
            document.getElementById('exp-detail-body').innerHTML = '<p class="text-red-600">Yüklenemedi.</p>';
        });
    });
});

document.querySelectorAll('.js-extra-bonus-detail').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var cid = this.getAttribute('data-courier-id');
        var cname = this.getAttribute('data-courier-name');
        var start = this.getAttribute('data-start-date');
        var end = this.getAttribute('data-end-date');
        document.getElementById('detail-modal-courier-name').textContent = cname;
        document.getElementById('detail-modal-date-range').textContent = start + ' - ' + end;
        document.getElementById('detail-modal-body').innerHTML = '<p class="text-gray-500">Yükleniyor…</p>';
        document.getElementById('extra-bonus-detail-modal').classList.remove('hidden');

        var qs = new URLSearchParams({ courier_id: cid, start_date: start, end_date: end });
        fetch('{{ route('panel.settlement.extra-bonuses.list') }}?' + qs.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function(r) { return r.json(); }).then(function(data) {
            var html = '';
            if (data.items && data.items.length) {
                html = '<table class="min-w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2 pr-4">Tarih</th><th class="text-left py-2 pr-4">Neden</th><th class="text-right py-2">Tutar (TL)</th></tr></thead><tbody>';
                data.items.forEach(function(row) {
                    html += '<tr class="border-b border-gray-100"><td class="py-2 pr-4 text-gray-600">' + row.bonus_date + '</td><td class="py-2 pr-4">' + (row.reason || '-') + '</td><td class="py-2 text-right font-medium">' + row.amount + '</td></tr>';
                });
                html += '</tbody></table>';
            } else {
                html = '<p class="text-gray-500">Bu tarih aralığında ek prim kaydı yok.</p>';
            }
            document.getElementById('detail-modal-body').innerHTML = html;
        }).catch(function() {
            document.getElementById('detail-modal-body').innerHTML = '<p class="text-red-600">Yüklenemedi.</p>';
        });
    });
});
</script>
@endpush
@endsection
