<?php

namespace App\Models\Products;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceName extends Model
{
    use HasFactory;

    public $timestamps = false;


    public function price():HasMany
    {
        return $this->hasMany(Price::class);
    }
    public function product(): BelongsToMany
    {
        return $this->belongsToMany(Product::class,
            'prices',
            'price_name_id',
        'product_id')
            ->withPivot('price');
    }
}
