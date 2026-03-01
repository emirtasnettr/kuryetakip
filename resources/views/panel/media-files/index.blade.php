@extends('layouts.panel')

@section('title', 'Ortam Dosyaları')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Ortam Dosyaları</h1>
    <p class="text-gray-500 mt-1">Yazılımdaki tüm dosya yüklemeleri: ortam dosyaları, vardiya başlangıç/bitiş fotoğrafları, masraf fişleri. Görüntüleyin, indirin veya silin.</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
@endif

{{-- Dosya yükleme --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Dosya Yükle</h2>
    <form action="{{ route('panel.media-files.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
        @csrf
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dosya(lar) seçin</label>
            <input type="file" name="files[]" multiple accept="*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Yükle</button>
    </form>
</div>

{{-- Toplu işlem butonları --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <span class="text-sm font-medium text-gray-700">Toplu işlem:</span>
    <button type="button" id="btn-select-all" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Tümünü seç / Kaldır</button>
    <form action="{{ route('panel.media-files.download-zip') }}" method="POST" class="inline" id="form-download-zip">
        @csrf
        <input type="hidden" name="ids" id="input-ids-zip" value="">
        <button type="submit" id="btn-download-zip" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700" disabled>Seçilenleri İndir (ZIP)</button>
    </form>
    <form action="{{ route('panel.media-files.bulk-destroy') }}" method="POST" class="inline" id="form-bulk-destroy" onsubmit="return confirm('Seçili dosyaları silmek istediğinize emin misiniz?');">
        @csrf
        <input type="hidden" name="ids" id="input-ids-destroy" value="">
        <button type="submit" id="btn-bulk-destroy" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700" disabled>Seçilenleri Sil</button>
    </form>
    <form action="{{ route('panel.media-files.delete-older-than-31') }}" method="POST" class="inline" onsubmit="return confirm('Yüklenmesinin üzerinden 31 gün geçmiş tüm dosyalar silinecek. Emin misiniz?');">
        @csrf
        <button type="submit" class="px-3 py-1.5 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700">30 Günlükleri Sil</button>
    </form>
    <form action="{{ route('panel.media-files.delete-older-than-46') }}" method="POST" class="inline" onsubmit="return confirm('Yüklenmesinin üzerinden 46 gün geçmiş tüm dosyalar silinecek. Emin misiniz?');">
        @csrf
        <button type="submit" class="px-3 py-1.5 text-sm bg-amber-700 text-white rounded-lg hover:bg-amber-800">45 Günlükleri Sil</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="check-all" class="rounded border-gray-300" title="Tümünü seç">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kaynak</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosya adı</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosya türü</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yükleyen</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dosya boyutu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yüklenme</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($files as $file)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($file->url && $file->can_zip)
                                <input type="checkbox" class="row-check rounded border-gray-300" value="{{ $file->row_id }}" data-row-id="{{ $file->row_id }}">
                            @else
                                <span class="text-gray-300" title="Dosya yok veya indirilemez">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $file->source_label }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $file->name }}">{{ $file->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $file->mime_type ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $file->uploaded_by }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $file->size_formatted }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $file->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($file->url)
                                @if($file->source_type === 'media_file')
                                    <a href="{{ route('panel.media-files.show', $file->source_id) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 rounded hover:bg-indigo-50">Görüntüle</a>
                                    <form action="{{ route('panel.media-files.destroy', $file->source_id) }}" method="POST" class="inline" onsubmit="return confirm('Bu dosyayı silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 rounded hover:bg-red-50">Sil</button>
                                    </form>
                                @elseif($file->source_type === 'shift_photo')
                                    <a href="{{ route('panel.media-files.show-shift-photo', $file->source_id) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 rounded hover:bg-indigo-50">Görüntüle</a>
                                    <form action="{{ route('panel.media-files.destroy-shift-photo', $file->source_id) }}" method="POST" class="inline" onsubmit="return confirm('Bu vardiya fotoğrafını silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 rounded hover:bg-red-50">Sil</button>
                                    </form>
                                @else
                                    <a href="{{ route('panel.media-files.show-expense-receipt', $file->source_id) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 rounded hover:bg-indigo-50">Görüntüle</a>
                                    <form action="{{ route('panel.media-files.destroy-expense-receipt', $file->source_id) }}" method="POST" class="inline" onsubmit="return confirm('Masraf fişi dosyası kaldırılacak (masraf kaydı kalır). Emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 rounded hover:bg-red-50">Sil</button>
                                    </form>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">Dosya yok</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">Henüz dosya yok. Yukarıdan dosya yükleyebilir veya vardiya/masraf yüklemeleri burada listelenir.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($files->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $files->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    var checkAll = document.getElementById('check-all');
    var rowChecks = document.querySelectorAll('.row-check');
    var btnDownloadZip = document.getElementById('btn-download-zip');
    var btnBulkDestroy = document.getElementById('btn-bulk-destroy');
    var inputIdsZip = document.getElementById('input-ids-zip');
    var inputIdsDestroy = document.getElementById('input-ids-destroy');
    var formDownloadZip = document.getElementById('form-download-zip');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-check:checked')).map(function(c) { return c.getAttribute('data-row-id') || c.value; });
    }

    function updateButtons() {
        var ids = getSelectedIds();
        var hasSelection = ids.length > 0;
        btnDownloadZip.disabled = !hasSelection;
        btnBulkDestroy.disabled = !hasSelection;
        inputIdsZip.value = ids.join(',');
        inputIdsDestroy.value = ids.join(',');
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowChecks.forEach(function(c) {
                c.checked = checkAll.checked;
            });
            updateButtons();
        });
    }

    rowChecks.forEach(function(c) {
        c.addEventListener('change', updateButtons);
    });

    document.getElementById('btn-select-all').addEventListener('click', function() {
        var allChecked = Array.from(rowChecks).every(function(c) { return c.checked; });
        rowChecks.forEach(function(c) {
            c.checked = !allChecked;
        });
        if (checkAll) checkAll.checked = !allChecked;
        updateButtons();
    });

    formDownloadZip.addEventListener('submit', function() {
        var ids = getSelectedIds();
        if (ids.length === 0) {
            alert('İndirilecek en az bir dosya seçin.');
            return false;
        }
        inputIdsZip.value = ids.join(',');
    });
})();
</script>
@endpush
@endsection
