@extends('layouts.panel')

@section('title', 'Kesintiler')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kesintiler</h1>
        <p class="text-gray-500 mt-1">Kuryelerden yapılan kesintiler. Kesinti tutarı kuryenin KDV hariç hakedişinden düşer.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.settlement.deductions.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
        <button type="button" id="js-open-deduction-modal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Kesinti Oluştur
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tutar (TL)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Neden</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Oluşturulma</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($deductions as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $d->user->name }}</td>
                        <td class="px-4 py-3 text-sm text-red-600 text-right font-medium">{{ number_format($d->amount, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="{{ $d->reason }}">{{ $d->reason }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $d->deduction_date->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-400">{{ $d->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Henüz kesinti kaydı yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($deductions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $deductions->links() }}</div>
    @endif
</div>

{{-- Modal: Kesinti Oluştur --}}
<div id="deduction-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('deduction-modal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Kesinti Oluştur</h3>
            <form action="{{ route('panel.settlement.deductions.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="deduction_courier_trigger" class="block text-sm font-medium text-gray-700 mb-1">Kurye <span class="text-red-500">*</span></label>
                        <input type="hidden" name="user_id" id="deduction_user_id" value="" required>
                        <div class="relative" id="deduction-courier-wrap">
                            <button type="button" id="deduction_courier_trigger" aria-haspopup="listbox" aria-expanded="false"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-left bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 flex items-center justify-between">
                                <span id="deduction_courier_label" class="text-gray-500">Kurye seçin</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="deduction_courier_dropdown" class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 flex flex-col">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" id="deduction_courier_search" placeholder="Kurye ara..." autocomplete="off"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <ul id="deduction_courier_list" role="listbox" class="overflow-y-auto py-1 max-h-48">
                                    @foreach($couriers as $c)
                                        <li role="option" data-value="{{ $c->id }}" data-name="{{ e($c->name) }}" class="deduction-courier-option px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50 text-gray-900">{{ $c->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="deduction_date" class="block text-sm font-medium text-gray-700 mb-1">Hakedişe yansıyacağı tarih <span class="text-red-500">*</span></label>
                        <input type="date" name="deduction_date" id="deduction_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="deduction_amount" class="block text-sm font-medium text-gray-700 mb-1">Kesinti tutarı (KDV hariç, TL) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="deduction_amount" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="0,00">
                    </div>
                    <div>
                        <label for="deduction_reason" class="block text-sm font-medium text-gray-700 mb-1">Kesinti nedeni <span class="text-red-500">*</span></label>
                        <textarea name="reason" id="deduction_reason" rows="3" required maxlength="2000" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Neden kesinti yapıldığı"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('deduction-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">İptal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var openBtn = document.getElementById('js-open-deduction-modal');
    var modal = document.getElementById('deduction-modal');
    var trigger = document.getElementById('deduction_courier_trigger');
    var dropdown = document.getElementById('deduction_courier_dropdown');
    var searchInput = document.getElementById('deduction_courier_search');
    var list = document.getElementById('deduction_courier_list');
    var hiddenInput = document.getElementById('deduction_user_id');
    var label = document.getElementById('deduction_courier_label');

    if (openBtn) openBtn.addEventListener('click', function() {
        modal.classList.remove('hidden');
        hiddenInput.value = '';
        label.textContent = 'Kurye seçin';
        label.classList.add('text-gray-500');
        if (searchInput) searchInput.value = '';
        filterCourierOptions('');
    });

    function filterCourierOptions(q) {
        if (!list) return;
        q = (q || '').toLowerCase().trim();
        var options = list.querySelectorAll('.deduction-courier-option');
        options.forEach(function(opt) {
            var name = (opt.getAttribute('data-name') || opt.textContent || '').toLowerCase();
            opt.style.display = !q || name.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() { filterCourierOptions(this.value); });
        searchInput.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !dropdown.classList.contains('hidden');
            dropdown.classList.toggle('hidden', isOpen);
            trigger.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) { searchInput.focus(); }
        });
    }

    if (list) {
        list.addEventListener('click', function(e) {
            var opt = e.target.closest('.deduction-courier-option');
            if (!opt) return;
            var val = opt.getAttribute('data-value');
            var name = opt.getAttribute('data-name') || opt.textContent;
            hiddenInput.value = val;
            label.textContent = name;
            label.classList.remove('text-gray-500');
            dropdown.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function(e) {
        var wrap = document.getElementById('deduction-courier-wrap');
        if (wrap && !wrap.contains(e.target)) {
            dropdown.classList.add('hidden');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
@endpush
@endsection
