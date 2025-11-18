<?php

namespace App\Services\Products;

use App\Dto\Products\ProductDto;
use App\Exceptions\BusinessException;
use App\Models\Orders\OrderStatus;
use App\Models\Orders\PayType;
use App\Models\Products\PriceName;
use App\Models\Products\Product;
use App\Models\Products\StockName;
use App\Services\Media\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{

    public function __construct(
        protected MediaService $mediaService,
    )
    {}

    public function create(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $product = new Product();
            $product = $this->setDataToProduct($product, $dto);

            return $product;
        });
    }

    public function update(ProductDto $dto, Product $product): Product
    {
        return DB::transaction(function () use ($dto,$product) {
            Cache::forget("product:".$product->slug);

            $product = $this->setDataToProduct($product, $dto);

            return $product;
        });
    }

    private function setDataToProduct(Product $product, ProductDto $dto): Product
    {

        $data = $this->preparedDate($dto);

        $prices = $this->priceToArray($dto);
        $quantityInStock = $this->quantityInStockToArray($dto);
        $product->fill($data);

        $product->save();
        $this->setAllPicturesToProduct($product, $dto);

        $product->updateQuantityInStock($quantityInStock);
        $product->updatePrices($prices);

        $product->load(['priceName', 'images', 'stocks']);

        $product->save();
        return $product;

    }


    private function setAllPicturesToProduct(Product $product, ProductDto $dto): void
    {
        if (isset($dto->previewPicture)) {
            $previewPicturePath = $dto->previewPicture->store('catalog/' . $product->id . '/preview', 'public');
            if (!isset($product->preview_picture) || !Storage::disk('public')->exists($product->preview_picture)) {
                $product->preview_picture = $previewPicturePath;
            } else {
                $this->deletePreviewPicture($product->preview_picture);
                $product->preview_picture = $previewPicturePath;
            }
        }
        if (!empty($dto->images)) {
            foreach ($dto->images as $image) {
                if ($image instanceof UploadedFile) {
                    $this->mediaService->uploadFile($product, $image, 'catalog_image');
                }
            }
        }
    }

    private function preparedDate($dto): array
    {
        $data = [];
        if (isset($dto->title)) {
            $data['title'] = $dto->title;
        }
        if (isset($dto->description)) {
            $data['description'] = $dto->description;
        }
        return $data;
    }

    private function deletePreviewPicture($filePath)
    {
        $deleted = Storage::disk('public')->delete($filePath);
        if (!$deleted || Storage::disk('public')->exists($filePath)) {
            Log::channel('image')->error("Failed to delete the preview image, file {$filePath}");
            throw new BusinessException("Failed to delete file: {$filePath}");
        }
    }


    private function priceToArray(ProductDto $dto): array
    {
        $pricesCollect = $this->getPriceNameCollect();

        $pricesToUpdate = [];

        if (isset($dto->prices['price_opt'])) {
            $pricesToUpdate[$pricesCollect['opt']->id] = $dto->prices['price_opt'];
        }
        if (isset($dto->prices['price_special'])) {
            $pricesToUpdate[$pricesCollect['spec']->id] = $dto->prices['price_special'];
        }
        if (isset($dto->prices['price_rozn'])) {
            $pricesToUpdate[$pricesCollect['rozn']->id] = $dto->prices['price_rozn'];
        }
        return $pricesToUpdate;
    }

    public function getPriceNameCollect()
    {
        $cacheKey = 'priceNameCollect-'.time();
        return Cache::tags(['settings'])
            ->remember($cacheKey, 3600, function () {
                return PriceName::select(['id','slug'])->get()->keyBy('slug');
            });
    }

    private function quantityInStockToArray(ProductDto $dto): array
    {
        $stockCollect = $this->getStockNameCollect();
        $quantityInStockToUpdate = [];

        if (isset($dto->stocks['stock_ivanovo'])) {
            $quantityInStockToUpdate[$stockCollect['ivanovo']->id] = $dto->stocks['stock_ivanovo'];
        }
        if (isset($dto->stocks['stock_moskov'])) {
            $quantityInStockToUpdate[$stockCollect['moskov']->id] = $dto->stocks['stock_moskov'];
        }
        if (isset($dto->stocks['stock_krasnodar'])) {
            $quantityInStockToUpdate[$stockCollect['krasnodar']->id] = $dto->stocks['stock_krasnodar'];
        }
        return $quantityInStockToUpdate;
    }

    private function getStockNameCollect()
    {
        $cacheKey = 'stockNameCollect-'.time();
        return Cache::tags(['settings'])
            ->remember($cacheKey, 3600, function () {
                return StockName::select(['id','slug'])->get()->keyBy('slug');
            });
    }

}
