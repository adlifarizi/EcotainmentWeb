<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'name',
        'price',
        'category',
        'description',
        'image',
        'total_sales',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

}
