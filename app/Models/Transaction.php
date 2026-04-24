<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'phone',
        'payer_name',
        'description',
        'type',
        'payment_method',
        'callback_data',
        'user_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isSuccessful()
    {
        return in_array($this->status, ['SUCCESS', 'SETTLED']);
    }

    public function isPending()
    {
        return in_array($this->status, ['PROCESSING', 'PENDING']);
    }

    public function isFailed()
    {
        return $this->status === 'FAILED';
    }
}
