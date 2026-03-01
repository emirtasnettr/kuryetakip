<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        $requests = auth()->user()->expenseRequests()->with('items')->latest()->paginate(15);
        return view('courier.expenses.index', compact('requests'));
    }

    public function create()
    {
        return view('courier.expenses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'order_number' => 'required|string|max:128',
            'reason' => 'required|string|max:2000',
            'source' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ], [
            'receipt_photo.required' => 'Fiş fotoğrafı zorunludur.',
            'order_number.required' => 'Sipariş numarası zorunludur.',
            'reason.required' => 'Neden zorunludur.',
            'source.required' => 'Nereden alındı alanı zorunludur.',
        ]);

        $hasValidItem = collect($request->input('items', []))->contains(function ($item) {
            return !empty(trim($item['product_name'] ?? ''));
        });
        if (!$hasValidItem) {
            return back()->withInput()->withErrors(['items' => 'En az bir ürün satırı doldurulmalıdır.']);
        }

        $path = $request->file('receipt_photo')->store(
            'expenses/' . date('Y/m'),
            'public'
        );

        $expense = ExpenseRequest::create([
            'user_id' => auth()->id(),
            'receipt_photo_path' => $path,
            'order_number' => $request->input('order_number'),
            'reason' => $request->input('reason'),
            'source' => $request->input('source'),
            'total_amount' => $request->input('total_amount'),
            'status' => ExpenseRequest::STATUS_PENDING,
        ]);

        foreach ($request->input('items') as $i => $item) {
            $name = trim($item['product_name'] ?? '');
            if ($name === '') {
                continue;
            }
            ExpenseRequestItem::create([
                'expense_request_id' => $expense->id,
                'product_name' => $name,
                'quantity_or_kg' => $item['quantity_or_kg'] ?? '',
                'price' => (float) ($item['price'] ?? 0),
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('courier.expenses.index')->with('success', 'Masraf talebiniz alındı. İnceleme sonrası bilgilendirileceksiniz.');
    }
}
