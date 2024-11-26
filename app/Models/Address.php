<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone_number',
        'province',
        'city_or_district',
        'detailed_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
