<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ExpenseRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    public const APPROVAL_SETTLEMENT = 'settlement'; // (eski) Hakedişe eklendi
    public const APPROVAL_TRANSFER = 'transfer';    // (eski) Havale ile gönderildi
    public const APPROVAL_DEDUCT_FROM_SETTLEMENT = 'deduct_from_settlement'; // Kurye hakedişinden düş
    public const APPROVAL_DEBT_BALANCE = 'debt_balance'; // Kurye borç bakiyesi
    public const APPROVAL_CLOSED = 'closed'; // Kaydı sonlandır – tutar uygulanmaz, talep tamamlandı

    protected $fillable = [
        'user_id',
        'receipt_photo_path',
        'order_number',
        'reason',
        'source',
        'total_amount',
        'status',
        'approval_type',
        'approved_at',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseRequestItem::class)->orderBy('sort_order');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /** Fiş fotoğrafı tam URL */
    public function getReceiptPhotoUrlAttribute(): ?string
    {
        if (!$this->receipt_photo_path) {
            return null;
        }
        return Storage::disk('public')->url($this->receipt_photo_path);
    }
}
