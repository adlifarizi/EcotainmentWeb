<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    use HasFactory;

    protected $table = 'purchase_history';

    protected $fillable = [
        'user_id',
        'transaction_id',
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

