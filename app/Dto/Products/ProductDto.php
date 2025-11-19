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
                'price_opt' => isset($validated['price_opt']) ? (float)$validated['price_opt'] : null,
                'price_special' => isset($validated['price_special']) ? (float)$validated['price_special'] : null,
                'price_rozn' => isset($validated['price_rozn']) ? (float)$validated['price_rozn'] : null,
            ],
            stocks: [
                'stock_ivanovo' => isset($validated['stock_ivanovo']) ? (int)$validated['stock_ivanovo'] : null,
                'stock_moskov' => isset($validated['stock_moskov']) ? (int)$validated['stock_moskov'] : null,
                'stock_krasnodar' => isset($validated['stock_krasnodar']) ? (int)$validated['stock_krasnodar'] : null,
            ],
            previewPicture: $validated['preview_picture'] ?? null,
            images: $validated['images'] ?? [],
        );
    }
}
