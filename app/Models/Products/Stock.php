<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{


    protected $table = 'stocks';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockName(): BelongsTo
    {
        return $this->belongsTo(StockName::class);
    }
}
