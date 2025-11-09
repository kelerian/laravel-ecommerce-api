<?php

namespace App\Dto\Search;

use App\Models\Media\News;
use App\Models\Products\Product;

class SearchDto
{
    public function __construct(
        public readonly string $q,
        public readonly string $model,
        public readonly string $searchType,
        public readonly int $page,
        public readonly int $perPage,
    )
    {}

    public static function fromArray($array)
    {
        return new self(
            q: $array['q'],
            model: static::getModel($array['models_type']),
            searchType: $array['search_type'],
            page: $array['page'],
            perPage: $array['perPage']
        );
    }

    private static function getModel($modelName)
    {
        return match($modelName) {
            'news' => News::class,
            'product' => Product::class,
        };
    }
}
