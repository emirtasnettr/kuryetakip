@extends('layouts.courier')

@section('title', 'Masraf Talebi')
@section('back_url', route('courier.expenses.index'))

@section('content')
<div class="p-4 max-w-2xl mx-auto">
    <p class="text-gray-500 text-sm mb-4">Sipariş numarası ve nedeni girin, fiş fotoğrafı yükleyin, ürün satırlarını girin. Toplam tutar satırlardan otomatik hesaplanır; gerekirse manuel düzenleyebilirsiniz.</p>

    <form action="{{ route('courier.expenses.store') }}" method="POST" enctype="multipart/form-data" id="expense-form">
        @csrf

        {{-- Sipariş numarası ve neden (zorunlu) --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <label for="order_number" class="block text-sm font-medium text-gray-700 mb-2">Sipariş numarası <span class="text-red-500">*</span></label>
            <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}" required maxlength="128"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Örn: SIP-2024-001">
            @error('order_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Neden <span class="text-red-500">*</span></label>
            <textarea name="reason" id="reason" rows="3" required maxlength="2000"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Masrafın nedenini kısaca yazın">{{ old('reason') }}</textarea>
            @error('reason')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <label for="source" class="block text-sm font-medium text-gray-700 mb-2">Nereden Alındı <span class="text-red-500">*</span></label>
            <input type="text" name="source" id="source" value="{{ old('source') }}" required maxlength="255"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Örn: Migros Kadıköy, Shell İstasyonu">
            @error('source')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fiş fotoğrafı (zorunlu) --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Fiş fotoğrafı <span class="text-red-500">*</span></label>
            <input type="file" name="receipt_photo" id="receipt_photo" accept="image/*" capture="environment"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700"
                   required>
            @error('receipt_photo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ürün satırları --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">Ürün Adı – Adet/KG – Fiyat</span>
                <button type="button" id="add-row" class="text-sm font-medium text-black bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-lg">+ Satır ekle</button>
            </div>
            <div id="items-container">
                <div class="item-row flex flex-wrap gap-2 sm:gap-3 mb-2 items-end">
                    <input type="text" name="items[0][product_name]" placeholder="Ürün adı" class="flex-1 min-w-[100px] px-3 py-2 border border-gray-300 rounded-lg text-sm" maxlength="255">
                    <input type="text" name="items[0][quantity_or_kg]" placeholder="Adet/KG" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm" maxlength="64">
                    <input type="number" name="items[0][price]" placeholder="Fiyat" step="0.01" min="0" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm" value="">
                    <button type="button" class="remove-row px-2 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm" title="Satırı kaldır">✕</button>
                </div>
            </div>
            @error('items')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Toplam tutar (otomatik toplanır, manuel düzenlenebilir) --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">Toplam tutar (TL) <span class="text-red-500">*</span></label>
            <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg"
                   placeholder="0,00"
                   value="{{ old('total_amount') }}">
            <p class="mt-1 text-xs text-gray-500">Satırlardaki fiyatlar otomatik toplanır; gerekirse bu alanı elle değiştirebilirsiniz.</p>
            @error('total_amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800">
            Talebi Gönder
        </button>
    </form>
</div>

@push('scripts')
<script>
(function() {
    let rowIndex = 1;
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-row');
    const totalInput = document.getElementById('total_amount');

    function sumItemPrices() {
        let sum = 0;
        container.querySelectorAll('.item-row input[name*="[price]"]').forEach(function(inp) {
            var v = parseFloat(inp.value) || 0;
            if (v >= 0) sum += v;
        });
        totalInput.value = sum > 0 ? sum.toFixed(2) : '';
    }

    function addRow() {
        const row = document.createElement('div');
        row.className = 'item-row flex flex-wrap gap-2 sm:gap-3 mb-2 items-end';
        row.innerHTML =
            '<input type="text" name="items[' + rowIndex + '][product_name]" placeholder="Ürün adı" class="flex-1 min-w-[100px] px-3 py-2 border border-gray-300 rounded-lg text-sm" maxlength="255">' +
            '<input type="text" name="items[' + rowIndex + '][quantity_or_kg]" placeholder="Adet/KG" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm" maxlength="64">' +
            '<input type="number" name="items[' + rowIndex + '][price]" placeholder="Fiyat" step="0.01" min="0" class="item-price w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm" value="">' +
            '<button type="button" class="remove-row px-2 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm" title="Satırı kaldır">✕</button>';
        container.appendChild(row);
        rowIndex++;
        row.querySelector('.remove-row').addEventListener('click', function() { removeRow(row); });
        row.querySelector('.item-price').addEventListener('input', sumItemPrices);
    }

    function removeRow(rowEl) {
        if (container.querySelectorAll('.item-row').length <= 1) return;
        rowEl.remove();
        sumItemPrices();
    }

    addBtn.addEventListener('click', addRow);
    container.querySelectorAll('.item-row').forEach(function(row) {
        var priceInp = row.querySelector('input[name*="[price]"]');
        if (priceInp) {
            priceInp.classList.add('item-price');
            priceInp.addEventListener('input', sumItemPrices);
        }
        row.querySelectorAll('.remove-row').forEach(function(btn) {
            btn.addEventListener('click', function() { removeRow(btn.closest('.item-row')); });
        });
    });
})();
</script>
@endpush
@endsection
