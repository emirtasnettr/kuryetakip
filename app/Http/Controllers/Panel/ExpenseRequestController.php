<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use Illuminate\Http\Request;

class ExpenseRequestController extends Controller
{
    /**
     * Masraf Talepleri (bekleyenler)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $requests = ExpenseRequest::with(['user', 'items'])
            ->whereIn('user_id', $courierIds)
            ->where('status', ExpenseRequest::STATUS_PENDING)
            ->latest()
            ->paginate(15);

        return view('panel.expenses.index', compact('requests'));
    }

    /**
     * Geçmiş Masraf Talepleri
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $requests = ExpenseRequest::with(['user', 'items', 'approvedByUser'])
            ->whereIn('user_id', $courierIds)
            ->latest()
            ->paginate(15);

        return view('panel.expenses.history', compact('requests'));
    }

    /**
     * Masraf taleplerini (bekleyen) Excel (CSV) olarak indir
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $requests = ExpenseRequest::with(['user'])
            ->whereIn('user_id', $courierIds)
            ->where('status', ExpenseRequest::STATUS_PENDING)
            ->latest()
            ->get();

        $filename = 'masraf_talepleri_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($requests) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, ['Kurye', 'Tutar (TL)', 'Sipariş No', 'Nereden Alındı', 'Gerekçe', 'Oluşturulma'], ';');
            foreach ($requests as $req) {
                fputcsv($stream, [
                    $req->user?->name ?? '',
                    number_format($req->total_amount, 2, ',', '.'),
                    $req->order_number ?? '',
                    $req->source ?? '',
                    $req->reason ?? '',
                    $req->created_at?->format('d.m.Y H:i') ?? '',
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Geçmiş masraf taleplerini Excel (CSV) olarak indir — tüm alanlar ve kalemler dahil
     */
    public function exportHistory(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $requests = ExpenseRequest::with(['user', 'approvedByUser', 'items'])
            ->whereIn('user_id', $courierIds)
            ->latest()
            ->get();

        $filename = 'gecmis_masraf_talepleri_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($requests) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, [
                'Kurye',
                'Sipariş No',
                'Nereden Alındı',
                'Neden / Gerekçe',
                'Kalemler (Ürün – Adet/KG – Fiyat TL)',
                'Toplam (TL)',
                'Durum',
                'Onay Tipi',
                'Onaylayan',
                'Onay Tarihi',
                'Oluşturulma',
            ], ';');
            foreach ($requests as $req) {
                $statusLabel = $req->isPending() ? 'Beklemede' : 'Onaylandı';
                $typeLabel = $req->approval_type === ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT ? 'Hakedişten düşüldü'
                    : ($req->approval_type === ExpenseRequest::APPROVAL_CLOSED ? 'Tamamlandı' : ($req->approval_type === ExpenseRequest::APPROVAL_SETTLEMENT ? 'Hakedişe eklendi' : ($req->approval_type ? 'Havale' : '')));
                $kalemler = $req->items->map(function ($item) {
                    return $item->product_name . ' – ' . ($item->quantity_or_kg ?: '-') . ' – ' . number_format((float) $item->price, 2, ',', '.') . ' TL';
                })->implode("\n");
                fputcsv($stream, [
                    $req->user?->name ?? '',
                    $req->order_number ?? '',
                    $req->source ?? '',
                    $req->reason ?? '',
                    $kalemler,
                    number_format($req->total_amount, 2, ',', '.'),
                    $statusLabel,
                    $typeLabel,
                    $req->approvedByUser?->name ?? '',
                    $req->approved_at ? $req->approved_at->format('d.m.Y H:i') : '',
                    $req->created_at ? $req->created_at->format('d.m.Y H:i') : '',
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * İncele (tek talep detay + onaylama)
     */
    public function show(ExpenseRequest $expenseRequest)
    {
        $this->authorizeForExpense($expenseRequest);

        $expenseRequest->load(['user', 'items', 'approvedByUser']);
        return view('panel.expenses.show', compact('expenseRequest'));
    }

    /**
     * Onayla: settlement veya transfer
     */
    public function approve(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorizeForExpense($expenseRequest);

        $request->validate([
            'approval_type' => 'required|in:deduct_from_settlement,closed',
        ]);

        if ($expenseRequest->status !== ExpenseRequest::STATUS_PENDING) {
            return redirect()->route('panel.expenses.index')->with('error', 'Bu talep zaten işlenmiş.');
        }

        $expenseRequest->update([
            'status' => ExpenseRequest::STATUS_APPROVED,
            'approval_type' => $request->approval_type,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $message = $request->approval_type === ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT
            ? 'Masraf onaylandı. Tutar kurye hakedişinden düşülecek.'
            : 'Talep tamamlandı olarak kapatıldı. Tutarla ilgili işlem yapılmadı.';

        return redirect()->route('panel.expenses.index')->with('success', $message);
    }

    private function authorizeForExpense(ExpenseRequest $expenseRequest): void
    {
        $user = request()->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        if (!$courierIds->contains($expenseRequest->user_id)) {
            abort(403);
        }
    }
}
