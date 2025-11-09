<?php

namespace App\Http\Controllers\Api\v1;

use App\Dto\Products\FilterForListDto;
use App\Dto\Products\ProductDto;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\CatalogIndexResource;
use App\Http\Resources\Product\ProductDetailResource;
use App\Models\Products\Product;
use App\Services\Media\ApiResponseService;
use App\Services\Products\ProductQueryService;
use App\Services\Products\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class CatalogController extends Controller
{

    public function __construct(
        private ApiResponseService $api,
        private ProductService $productService,
        private ProductQueryService  $prodQueryServ,
    ) {}
    #[OA\Get(
        path: '/v1/catalog',
        summary: "catalog list",
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'page number',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'news limit',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'параметр сортировки (price,date,stock)',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'direction',
                description: 'параметр сортировки (asc,desc)',
                in: 'query'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function index(Request $request)
    {
        $filters = $request->validate([
            'limit' => 'sometimes|integer|min:1',
            'direction' => 'sometimes|in:asc,desc',
            'sort' => 'sometimes|in:price,date,stock',
            'page' => 'sometimes|integer|min:1',
        ]);

        $dto = FilterForListDto::fromArray($filters);

        $queryProduct = $this->prodQueryServ->catalogListWithFilter($dto);

        return CatalogIndexResource::collection($queryProduct)
            ->additional([
            'meta' => [
                'filters' => $dto->toArray(),
            ],
        ])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Get(
        path: '/v1/catalog/{slug}',
        summary: "catalog element detail",
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'product slug',
                in: 'path'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function show(Request $request, $slug)
    {
        $product = $this->prodQueryServ->getProductDetailBySlug($slug);

        return $this->api->success( (new ProductDetailResource($product)) );
    }


    #[OA\Post(
        path: '/v1/catalog',
        description: "Создание нового товара.",
        summary: "Создание товара",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Данные для создания товара",
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: [],
                    properties: [
                        new OA\Property(
                            property: 'title',
                            description: "Название товара",
                            type: "string",
                            maxLength: 100,
                            minLength: 2,
                            example: "Новое название товара"
                        ),
                        new OA\Property(
                            property: 'description',
                            description: "Описание товара",
                            type: "string",
                            minLength: 5,
                            example: "Подробное описание товара"
                        ),
                        new OA\Property(
                            property: 'preview_picture',
                            description: "Картинка превью (до 10MB, jpeg,png,jpg,gif,svg)",
                            type: "string",
                            format: "binary"
                        ),
                        new OA\Property(
                            property: 'images[]',
                            description: "Дополнительные изображения (максимум 10 файлов)",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                format: "binary"
                            )
                        ),
                        new OA\Property(
                            property: 'price_opt',
                            description: "Оптовая цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 1500.50
                        ),
                        new OA\Property(
                            property: 'price_special',
                            description: "Специальная цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 1200.00
                        ),
                        new OA\Property(
                            property: 'price_rozn',
                            description: "Розничная цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 2000.00
                        ),
                        new OA\Property(
                            property: 'stock_ivanovo',
                            description: "Количество на складе Иваново (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 25
                        ),
                        new OA\Property(
                            property: 'stock_moskov',
                            description: "Количество на складе Москва (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 15
                        ),
                        new OA\Property(
                            property: 'stock_krasnodar',
                            description: "Количество на складе Краснодар (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 30
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ['Catalog'],
        responses: [
            new Response(
                response: 200,
                description: 'created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'товар создан'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new Response(
                response: 403,
                description: 'Forbidden'
            ),
            new Response(
                response: 404,
                description: 'Товар не найден'
            ),
            new Response(
                response: 422,
                description: 'Ошибка валидации'
            ),
        ]
    )]
    public function create(CreateProductRequest $request)
    {
        $params = $request->validated();

        $dto = ProductDto::fromRequest($params);

        $product = $this->productService->create($dto);

        return $this->api->success((new ProductDetailResource($product)),'create product', 200);
    }
    #[OA\Post(
        path: '/v1/catalog/{slug}',
        description: "Обновляет только переданные поля товара. Для обновления цен и остатков используйте соответствующие поля.",
        summary: "Обновить указанные поля товара",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: [],
                    properties: [
                        new OA\Property(
                            property: 'title',
                            description: "Название товара",
                            type: "string",
                            maxLength: 100,
                            minLength: 2,
                            example: "Новое название товара"
                        ),
                        new OA\Property(
                            property: 'description',
                            description: "Описание товара",
                            type: "string",
                            minLength: 5,
                            example: "Обновлённое подробное описание товара"
                        ),
                        new OA\Property(
                            property: 'preview_picture',
                            description: "Картинка превью (до 10MB, jpeg,png,jpg,gif,svg)",
                            type: "string",
                            format: "binary"
                        ),
                        new OA\Property(
                            property: 'images[]',
                            description: "Дополнительные изображения (максимум 10 файлов)",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                format: "binary"
                            )
                        ),
                        new OA\Property(
                            property: 'price_opt',
                            description: "Оптовая цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 1500.50
                        ),
                        new OA\Property(
                            property: 'price_special',
                            description: "Специальная цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 1200.00
                        ),
                        new OA\Property(
                            property: 'price_rozn',
                            description: "Розничная цена (число, минимум 0)",
                            type: "number",
                            format: "float",
                            minimum: 0,
                            example: 2000.00
                        ),
                        new OA\Property(
                            property: 'stock_ivanovo',
                            description: "Количество на складе Иваново (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 25
                        ),
                        new OA\Property(
                            property: 'stock_moskov',
                            description: "Количество на складе Москва (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 15
                        ),
                        new OA\Property(
                            property: 'stock_krasnodar',
                            description: "Количество на складе Краснодар (целое число, минимум 0)",
                            type: "integer",
                            minimum: 0,
                            example: 30
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Символьный код товара',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: "nazvanie-tovara"
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: 'Обновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'товар обновлен'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new Response(
                response: 403,
                description: 'Forbidden'
            ),
            new Response(
                response: 404,
                description: 'Товар не найден'
            ),
            new Response(
                response: 422,
                description: 'Ошибка валидации'
            ),
        ]
    )]
    public function update(UpdateProductRequest $request, $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        if ($request->user()->cannot('update',$product)) {
            return $this->api->forbidden();
        };
        $params = $request->validated();

        $dto = ProductDto::fromRequest($params);

        $product = $this->productService->update($dto, $product);

        return $this->api->success((new ProductDetailResource($product)),'updated product', 200);
    }

    #[OA\Delete(
        path: '/v1/catalog/{slug}',
        summary: "Удалить товар",
        security: [['sanctumAuth' => []]],
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Символьный код товара',
                in: 'path',
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Not found'
            ),
        ]
    )]
    public function delete(Request $request, Product $product)
    {

        if ($request->user()->cannot('delete', $product)) {
            return $this->api->forbidden();
        }

        $product->delete();
        Cache::tags('catalog')->flush();

        return $this->api->success(null, 'Product deleted successfully', 200);
    }

}
