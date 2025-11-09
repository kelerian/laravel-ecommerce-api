<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockName extends Model
{

    use HasFactory;

    public $timestamps = false;

    public function quantity():HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class,
            'stocks',
            'stock_name_id',
            'product_is',
        )
            ->using(Stock::class)
            ->withPivot('quantity');
    }
}
