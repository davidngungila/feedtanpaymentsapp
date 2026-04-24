<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillPayNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_pay_number',
        'bill_description',
        'bill_amount',
        'bill_currency',
        'bill_payment_mode',
        'bill_status',
        'bill_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'bill_reference',
        'notes',
        'total_paid',
        'last_payment_at',
        'created_by'
    ];

    protected $casts = [
        'bill_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'last_payment_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('bill_status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bill_type', $type);
    }

    public function scopeByCustomerPhone($query, $phone)
    {
        return $query->where('customer_phone', $phone);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('bill_pay_number', 'like', "%{$search}%")
              ->orWhere('bill_description', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('customer_email', 'like', "%{$search}%")
              ->orWhere('customer_phone', 'like', "%{$search}%")
              ->orWhere('bill_reference', 'like', "%{$search}%");
        });
    }

    public function getRemainingAmount()
    {
        return max(0, $this->bill_amount - $this->total_paid);
    }

    public function isFullyPaid()
    {
        return $this->total_paid >= $this->bill_amount;
    }

    public function isActive()
    {
        return $this->bill_status === 'ACTIVE';
    }

    public static function createFromApiResponse(array $data, array $additionalData = [])
    {
        return self::create(array_merge([
            'bill_pay_number' => $data['billPayNumber'] ?? null,
            'bill_description' => $data['billDescription'] ?? null,
            'bill_amount' => $data['billAmount'] ?? 0,
            'bill_currency' => $data['billCurrency'] ?? 'TZS',
            'bill_payment_mode' => $data['billPaymentMode'] ?? 'ALLOW_PARTIAL_AND_OVER_PAYMENT',
            'bill_status' => $data['billStatus'] ?? 'ACTIVE',
            'customer_name' => $data['billCustomerName'] ?? null,
            'customer_email' => $data['customerEmail'] ?? null,
            'customer_phone' => $data['customerPhone'] ?? null,
            'bill_reference' => $data['billReference'] ?? null,
        ], $additionalData));
    }
}
