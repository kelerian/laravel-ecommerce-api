<?php

namespace App\Observers;

use App\Exceptions\BusinessException;
use App\Models\Products\Product;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }
    public function deleting(Product $product): void
    {
        $filePath = $product->preview_picture;

        if(isset($filePath)){

            $deleted = Storage::disk('public')->delete($filePath);
            if (!$deleted || Storage::disk('public')->exists($filePath)) {
                Log::channel('image')->error("Не удалось удалить файл {$filePath} у товара {$product->id}");
                throw new BusinessException("Failed to delete file: {$filePath}");
            }
        }

        if ($product->images->isNotEmpty()) {
            app(MediaService::class)->deleteImage($product->images);
        }
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
