<?php

namespace App\Dto\Products;

use Illuminate\Http\UploadedFile;

class ProductDto
{
    public function __construct(
        public ?string $title,
        public ?string $description,
        public array $prices = [],
        public array $stocks = [],
        public ?UploadedFile $previewPicture = null,
        public array $images = [],
    )
    {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            title: $validated['title'] ,
            description: $validated['description'],
            prices: [
                'price_opt' => $validated['price_opt'],
                'price_special' => $validated['price_special'],
                'price_rozn' => $validated['price_rozn'],
            ],
            stocks: [
                'stock_ivanovo' => $validated['stock_ivanovo'],
                'stock_moskov' => $validated['stock_moskov'],
                'stock_krasnodar' => $validated['stock_krasnodar'],
            ],
            previewPicture: $validated['preview_picture'] ?? null,
            images: $validated['images'] ?? [],
        );
    }
}
