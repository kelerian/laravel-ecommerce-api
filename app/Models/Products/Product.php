<?php

namespace App\Models\Products;

use App\Models\Carts\Cart;
use App\Models\Media\Media;
use App\Models\Orders\Order;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable as SearchableSc;
use Elastic\ScoutDriverPlus\Searchable as SearchableEl;

class Product extends Model
{
    use HasSlug, HasFactory, SearchableEl;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'preview_picture'
    ];

    public function toSearchableArray()
    {
        $priceData = $this->priceName->map(function ($priceName) {
            return [
                'id' => $priceName->id,
                'name' => $priceName->name,
                'price' => $priceName->pivot->price
            ];
        })->toArray();


        $stocksData = $this->stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'title' => $stock->title,
                'quantity' => $stock->pivot->quantity
            ];
        })->toArray();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'created_at_timestamp' => $this->created_at?->timestamp,
            'preview_picture' => $this->preview_picture ? Storage::url($this->preview_picture) : null,
            'description' => $this->description,
            'prices' => $priceData,
            'stocks' => $stocksData
        ];
    }

    public function searchableWith()
    {
        return ['priceName', 'stocks'];
    }
    public function shouldBeSearchable()
    {
        return true;
    }
    public function searchableAs(): string
    {
        return 'product_v1';
    }

    public function price():HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function priceName(): BelongsToMany
    {
        return $this->belongsToMany(PriceName::class,
            'prices',
            'product_id',
            'price_name_id')
            ->withPivot('price');
    }

    public function quantity():HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(StockName::class,
        'stocks',
            'product_id',
            'stock_name_id',
        )
            ->withPivot('quantity');
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class,
            'cart_items',
            'product_id',
            'cart_id'
        )
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function order(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            'order_items',
            'product_id',
            'order_id'
        )
            ->withPivot(
                'product_name',
                'price',
                'quantity'
            );
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class,'model');
    }

    public function images(): MorphMany
    {
        return $this->media()->where('collection_name','catalog_image');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->with(['images', 'stocks', 'priceName'])
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }

    public function updatePrices(array $prices): void
    {
        $priceToUpdate = [];

        foreach ($prices as $typeId => $price) {
            if (isset($price)) {
                $priceToUpdate[$typeId] = [
                    'price' => $price
                ];
            }
        }

        if (!empty($priceToUpdate)) {
            $this->priceName()->syncWithoutDetaching($priceToUpdate);
        }
    }

    public function updateQuantityInStock(array $stockQuantity)
    {
        $stockQuantityToUpdate = [];

        foreach ($stockQuantity as $stockId => $quantity) {
            if (isset($quantity)){
                $stockQuantityToUpdate[$stockId] = [
                    'quantity' => $quantity
                ];
            }
        }

        if(!empty($stockQuantityToUpdate)){
            $this->stocks()->syncWithoutDetaching($stockQuantityToUpdate);
        }
    }

}
