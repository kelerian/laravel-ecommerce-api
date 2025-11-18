<?php

namespace App\Services\Products;

use App\Dto\Products\FilterForListDto;
use App\Models\Products\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductsQueryService
{

    public function __construct(
        private ProductsListQuery $productsListQuery,
    )
    {}

    public function catalogListWithFilter(FilterForListDto $dto): LengthAwarePaginator
    {
        $cacheKey = $this->buildCatalogCacheKey($dto);

        return Cache::tags(['catalog'])
            ->remember($cacheKey, 1800, function () use ($dto) {
                return $this->buildCatalogListQuery($dto);
            });
    }

    private function buildCatalogListQuery(FilterForListDto $dto): LengthAwarePaginator
    {
        return $this->productsListQuery
            ->applySort($dto->sort, $dto->direction)
            ->paginate($dto->limit);

    }

    private function buildCatalogCacheKey(FilterForListDto $dto): string
    {
        $keyData = $dto->toArray();

        return "catalog:list:" . md5(serialize($keyData));
    }

    public function getProductDetailBySlug(string $slug): Product
    {
        $cacheKey = 'product:' . $slug;
        return Cache::tags(['catalog'])
            ->remember($cacheKey, 1800, function () use ($slug) {
                return $this->buildProductDetailQuery($slug);
            });
    }

    private function buildProductDetailQuery(string $slug): Product
    {
        return Product::with([
            'priceName',
            'stocks' => function($query) {
                $query->where('is_active', true);
            },
            'images',
        ])->where('slug', $slug)->firstOrFail();
    }

}
