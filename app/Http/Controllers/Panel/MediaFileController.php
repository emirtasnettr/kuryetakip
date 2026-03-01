<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\MediaFile;
use App\Models\ShiftPhoto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MediaFileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $rows = collect();

        // Ortam dosyaları (panelden yüklenen)
        foreach (MediaFile::with('user')->orderBy('created_at', 'desc')->get() as $f) {
            $rows->push((object)[
                'source_type' => 'media_file',
                'source_id' => $f->id,
                'row_id' => 'media_file:' . $f->id,
                'name' => $f->original_name,
                'mime_type' => $f->mime_type,
                'size_formatted' => $f->formatted_size,
                'uploaded_by' => $f->user?->name ?? '—',
                'created_at' => $f->created_at,
                'url' => $f->exists() ? $f->url : null,
                'full_path' => $f->exists() ? $f->full_path : null,
                'can_delete' => true,
                'can_zip' => $f->exists(),
                'source_label' => 'Ortam dosyası',
            ]);
        }

        // Vardiya fotoğrafları (başlangıç/bitiş)
        foreach (ShiftPhoto::with(['shift.user'])->orderBy('created_at', 'desc')->get() as $f) {
            if (!$f->shift || !$courierIds->contains($f->shift->user_id)) {
                continue;
            }
            $rows->push((object)[
                'source_type' => 'shift_photo',
                'source_id' => $f->id,
                'row_id' => 'shift_photo:' . $f->id,
                'name' => $f->original_filename ?? $f->filename ?? basename($f->path),
                'mime_type' => $f->mime_type,
                'size_formatted' => $f->readable_size ?? '—',
                'uploaded_by' => $f->shift?->user?->name ?? '—',
                'created_at' => $f->created_at,
                'url' => $f->exists() ? $f->url : null,
                'full_path' => $f->exists() ? $f->full_path : null,
                'can_delete' => true,
                'can_zip' => $f->exists(),
                'source_label' => $f->type === ShiftPhoto::TYPE_START ? 'Vardiya başlangıç' : 'Vardiya bitiş',
            ]);
        }

        // Masraf fişleri
        foreach (ExpenseRequest::with('user')->whereNotNull('receipt_photo_path')->orderBy('created_at', 'desc')->whereIn('user_id', $courierIds)->get() as $e) {
            $path = $e->receipt_photo_path;
            $fullPath = Storage::disk('public')->path($path);
            $exists = Storage::disk('public')->exists($path);
            $size = $exists && is_file($fullPath) ? filesize($fullPath) : 0;
            $rows->push((object)[
                'source_type' => 'expense_receipt',
                'source_id' => $e->id,
                'row_id' => 'expense_receipt:' . $e->id,
                'name' => 'Masraf fişi #' . $e->id . ' (' . basename($path) . ')',
                'mime_type' => 'image/*',
                'size_formatted' => $size ? $this->formatBytes($size) : '—',
                'uploaded_by' => $e->user?->name ?? '—',
                'created_at' => $e->created_at,
                'url' => $exists ? $e->receipt_photo_url : null,
                'full_path' => $exists ? $fullPath : null,
                'can_delete' => true,
                'can_zip' => $exists,
                'source_label' => 'Masraf fişi',
            ]);
        }

        $rows = $rows->sortByDesc(fn ($r) => $r->created_at->timestamp)->values();
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $files = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('panel.media-files.index', compact('files'));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1|max:20',
            'files.*' => 'file|max:51200', // 50MB per file
        ], [
            'files.required' => 'En az bir dosya seçiniz.',
        ]);

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            $path = $file->store('ortam-dosyalari', 'public');
            MediaFile::create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'disk' => 'public',
                'user_id' => $request->user()->id,
            ]);
            $uploaded++;
        }

        return redirect()->route('panel.media-files.index')
            ->with('success', "{$uploaded} dosya yüklendi.");
    }

    /**
     * Dosyayı görüntüle (yeni sekmede açılacak URL’e yönlendir)
     */
    public function show(MediaFile $mediaFile)
    {
        if (!$mediaFile->exists()) {
            return redirect()->route('panel.media-files.index')->with('error', 'Dosya bulunamadı.');
        }
        return redirect($mediaFile->url);
    }

    public function destroy(MediaFile $mediaFile)
    {
        $mediaFile->deleteFileFromDisk();
        $mediaFile->delete();
        return redirect()->route('panel.media-files.index')->with('success', 'Dosya silindi.');
    }

    /**
     * Vardiya fotoğrafını görüntüle
     */
    public function showShiftPhoto(ShiftPhoto $shiftPhoto)
    {
        $user = request()->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        if (!$shiftPhoto->shift || !$courierIds->contains($shiftPhoto->shift->user_id)) {
            abort(403);
        }
        if (!$shiftPhoto->exists()) {
            return redirect()->route('panel.media-files.index')->with('error', 'Dosya bulunamadı.');
        }
        return redirect($shiftPhoto->url);
    }

    /**
     * Masraf fişini görüntüle
     */
    public function showExpenseReceipt(ExpenseRequest $expenseRequest)
    {
        $user = request()->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        if (!$courierIds->contains($expenseRequest->user_id)) {
            abort(403);
        }
        if (!$expenseRequest->receipt_photo_path || !Storage::disk('public')->exists($expenseRequest->receipt_photo_path)) {
            return redirect()->route('panel.media-files.index')->with('error', 'Dosya bulunamadı.');
        }
        return redirect($expenseRequest->receipt_photo_url);
    }

    /**
     * Vardiya fotoğrafını sil
     */
    public function destroyShiftPhoto(ShiftPhoto $shiftPhoto)
    {
        $user = request()->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        if (!$shiftPhoto->shift || !$courierIds->contains($shiftPhoto->shift->user_id)) {
            abort(403);
        }
        $shiftPhoto->deleteWithFile();
        return redirect()->route('panel.media-files.index')->with('success', 'Dosya silindi.');
    }

    /**
     * Masraf fişi dosyasını sil (kayıt kalır, sadece dosya kaldırılır)
     */
    public function destroyExpenseReceipt(ExpenseRequest $expenseRequest)
    {
        $user = request()->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        if (!$courierIds->contains($expenseRequest->user_id)) {
            abort(403);
        }
        if ($expenseRequest->receipt_photo_path && Storage::disk('public')->exists($expenseRequest->receipt_photo_path)) {
            Storage::disk('public')->delete($expenseRequest->receipt_photo_path);
        }
        $expenseRequest->update(['receipt_photo_path' => null]);
        return redirect()->route('panel.media-files.index')->with('success', 'Fiş dosyası kaldırıldı.');
    }

    public function bulkDestroy(Request $request)
    {
        $rowIds = $this->parseRowIds($request->ids);
        if (empty($rowIds)) {
            return redirect()->route('panel.media-files.index')->with('error', 'Seçili dosya bulunamadı.');
        }
        $count = 0;
        foreach ($rowIds as $type => $ids) {
            if ($type === 'media_file') {
                foreach (MediaFile::whereIn('id', $ids)->get() as $item) {
                    $item->deleteFileFromDisk();
                    $item->delete();
                    $count++;
                }
            } elseif ($type === 'shift_photo') {
                $user = $request->user();
                $courierIds = $user->getAccessibleCouriers()->pluck('id');
                foreach (ShiftPhoto::whereIn('id', $ids)->with('shift')->get() as $item) {
                    if ($item->shift && $courierIds->contains($item->shift->user_id)) {
                        $item->deleteWithFile();
                        $count++;
                    }
                }
            } elseif ($type === 'expense_receipt') {
                $user = $request->user();
                $courierIds = $user->getAccessibleCouriers()->pluck('id');
                foreach (ExpenseRequest::whereIn('id', $ids)->whereIn('user_id', $courierIds)->get() as $item) {
                    if ($item->receipt_photo_path && Storage::disk('public')->exists($item->receipt_photo_path)) {
                        Storage::disk('public')->delete($item->receipt_photo_path);
                    }
                    $item->update(['receipt_photo_path' => null]);
                    $count++;
                }
            }
        }
        return redirect()->route('panel.media-files.index')->with('success', "{$count} dosya silindi.");
    }

    /**
     * Yüklenmesinin üzerinden 31 gün geçmiş dosyaları sil
     */
    public function deleteOlderThan31(Request $request)
    {
        $cutoff = now()->subDays(31);
        $items = MediaFile::where('created_at', '<', $cutoff)->get();
        $count = 0;
        foreach ($items as $item) {
            $item->deleteFileFromDisk();
            $item->delete();
            $count++;
        }
        return redirect()->route('panel.media-files.index')->with('success', "31 günden eski {$count} dosya silindi.");
    }

    /**
     * Yüklenmesinin üzerinden 46 gün geçmiş dosyaları sil
     */
    public function deleteOlderThan46(Request $request)
    {
        $cutoff = now()->subDays(46);
        $items = MediaFile::where('created_at', '<', $cutoff)->get();
        $count = 0;
        foreach ($items as $item) {
            $item->deleteFileFromDisk();
            $item->delete();
            $count++;
        }
        return redirect()->route('panel.media-files.index')->with('success', "46 günden eski {$count} dosya silindi.");
    }

    /**
     * Seçilen dosyaları ZIP olarak indir
     */
    public function downloadZip(Request $request)
    {
        $rowIds = $this->parseRowIds($request->ids);
        $itemsToZip = []; // [ full_path => zip_entry_name ]

        foreach ($rowIds as $type => $ids) {
            if ($type === 'media_file') {
                foreach (MediaFile::whereIn('id', $ids)->get() as $f) {
                    if ($f->exists()) {
                        $itemsToZip[$f->full_path] = $f->original_name;
                    }
                }
            } elseif ($type === 'shift_photo') {
                $user = $request->user();
                $courierIds = $user->getAccessibleCouriers()->pluck('id');
                foreach (ShiftPhoto::whereIn('id', $ids)->with('shift')->get() as $f) {
                    if ($f->shift && $courierIds->contains($f->shift->user_id) && $f->exists()) {
                        $name = $f->original_filename ?? $f->filename ?? ('shift_photo_' . $f->id);
                        $itemsToZip[$f->full_path] = $name;
                    }
                }
            } elseif ($type === 'expense_receipt') {
                $user = $request->user();
                $courierIds = $user->getAccessibleCouriers()->pluck('id');
                foreach (ExpenseRequest::whereIn('id', $ids)->whereIn('user_id', $courierIds)->whereNotNull('receipt_photo_path')->get() as $e) {
                    $fullPath = Storage::disk('public')->path($e->receipt_photo_path);
                    if (Storage::disk('public')->exists($e->receipt_photo_path) && is_file($fullPath)) {
                        $itemsToZip[$fullPath] = 'masraf_fisi_' . $e->id . '_' . basename($e->receipt_photo_path);
                    }
                }
            }
        }

        if (empty($itemsToZip)) {
            return redirect()->route('panel.media-files.index')->with('error', 'İndirilecek dosya seçiniz veya dosyalar mevcut değil.');
        }

        $zipPath = storage_path('app/temp/ortam_' . now()->format('Y-m-d_His') . '.zip');
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('panel.media-files.index')->with('error', 'ZIP oluşturulamadı.');
        }

        $usedNames = [];
        foreach ($itemsToZip as $fullPath => $baseName) {
            $name = $baseName;
            $i = 0;
            while (isset($usedNames[$name])) {
                $i++;
                $ext = pathinfo($baseName, PATHINFO_EXTENSION);
                $name = pathinfo($baseName, PATHINFO_FILENAME) . "_{$i}." . ($ext ?: '');
            }
            $usedNames[$name] = true;
            $zip->addFile($fullPath, $name);
        }

        $zip->close();

        return response()->download($zipPath, 'ortam_dosyalari_' . now()->format('Y-m-d_His') . '.zip')->deleteFileAfterSend(true);
    }

    /** @return array<string, array<int>> */
    private function parseRowIds($ids): array
    {
        $raw = is_array($ids) ? $ids : (is_string($ids) ? array_filter(explode(',', $ids)) : []);
        $byType = [];
        foreach ($raw as $v) {
            $v = trim((string) $v);
            if (str_contains($v, ':')) {
                [$type, $id] = explode(':', $v, 2);
                $id = (int) $id;
                if ($id > 0 && in_array($type, ['media_file', 'shift_photo', 'expense_receipt'], true)) {
                    $byType[$type] = $byType[$type] ?? [];
                    $byType[$type][] = $id;
                }
            }
        }
        foreach (array_keys($byType) as $type) {
            $byType[$type] = array_values(array_unique($byType[$type]));
        }
        return $byType;
    }

    private function parseIds($ids): array
    {
        if (is_array($ids)) {
            return array_values(array_filter(array_map('intval', $ids)));
        }
        if (is_string($ids)) {
            return array_values(array_filter(array_map('intval', explode(',', $ids))));
        }
        return [];
    }
}
