<?php

namespace App\Services\Products;

use App\Dto\Products\FilterForListDto;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Cache;

class ProductQueryService
{

    public function catalogListWithFilter(FilterForListDto $dto)
    {
        $cacheKey = $this->buildCatalogCacheKey($dto);

        return Cache::tags(['catalog'])
            ->remember($cacheKey, 1800, function () use ($dto) {
                return $this->buildCatalogListQuery($dto);
            });
    }
    private function buildCatalogListQuery(FilterForListDto $dto)
    {
        return Product::query()->with([
            'priceName' ,
            'stocks' => function($query){
                $query->where(
                    'is_active', '=', true
                );
            },
            'images'
        ])
            ->select('products.*')
            ->when($dto->sort == 'price', function ($query) use ($dto){
                $query->join('prices', function($join) {
                    $join->on('products.id','=','prices.product_id')
                        ->where('prices.price_name_id',3);
                })
                    ->addSelect(
                        'prices.price',
                    )
                    ->orderBy('prices.price', $dto->direction);
            })
            ->when($dto->sort == 'date', function($query) use ($dto){
                $query->orderBy('created_at',$dto->direction);
            })
            ->when($dto->sort == 'stock', function($query) use ($dto) {
                $query->join('stocks', function ($join){
                    $join->on('products.id','=','stocks.product_id');
                })
                    ->addSelect(
                        \DB::raw('SUM(stocks.quantity) as total_quantity'))
                    ->groupBy('products.id')
                    ->orderBy('total_quantity', $dto->direction);

            })
            ->paginate($dto->limit);
    }

    private function buildCatalogCacheKey(FilterForListDto $dto): string
    {
        $keyData = $dto->toArray();

        return "catalog:list:" . md5(serialize($keyData));
    }

    public function getProductDetailBySlug(string $slug)
    {
        $cacheKey = 'product:' . $slug;
        return Cache::tags(['catalog'])
            ->remember($cacheKey, 1800, function () use ($slug) {
                return Product::with([
                    'priceName',
                    'stocks' => function($query) {
                        $query->where('is_active', true);
                    },
                    'images',
                ])->where('slug', $slug)->firstOrFail();
            });

    }
}
