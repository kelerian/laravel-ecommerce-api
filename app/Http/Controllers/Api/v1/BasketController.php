<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Resources\Carts\BasketResource;
use App\Services\Carts\CartService;
use App\Services\Media\ApiResponseService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class BasketController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ApiResponseService $api,
    )
    {}
    #[OA\Get(
        path: '/v1/cart',
        summary: "",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
            name: 'fuser',
            description: 'айди неавторизованного юзера',
            in: 'header'
        ),],
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
        $fuser = $request->header('fuser');
        $this->cartService->loadCart($fuser);

        return $this->api->success(new BasketResource($this->cartService->getCart()));
    }

    #[OA\Post(
        path: '/v1/cart',
        summary: "Добавить товар в корзину",
        security : [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "Корзина, добавление",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        required: [],
                        properties: [
                            new OA\Property(
                                property: "products",
                                description: "Список товаров с количеством",
                                type: "array",
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(
                                            property: "id",
                                            type: "integer",
                                            example: 12
                                        ),
                                        new OA\Property(
                                            property: "quantity",
                                            type: "integer",
                                            example: 3
                                        )
                                    ],
                                    type: "object"
                                )
                            ),
                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'fuser',
                description: 'айди неавторизованного юзера',
                in: 'header',
            )
        ],
        responses: [
            new Response(
                response: 201,
                description: 'success',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 422,
                description: 'fail'
            ),
        ],
    )]
    public function store(Request $request)
    {
        $fuser = $request->header('fuser');

        $requestData = $request->validate([
            'products' => 'array',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);
        $productsData = $requestData['products'];

        $data = $this->cartService->loadCart($fuser)
            ->updateCart($productsData);

        return $this->api->success($data);
    }

    #[OA\Delete(
        path: '/v1/cart',
        summary: "Удаление из корзины",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: [],
                    properties: [
                        new OA\Property(
                            property: "products",
                            description: "Список товаров",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        type: "integer",
                                        example: 12
                                    ),
                                ],
                                type: "object"
                            )
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'fuser',
                description: 'айди неавторизованного юзера',
                in: 'header',
            ),
            new OA\Parameter(
                name: 'all',
                description: 'флаг удаления (true = очистить всю корзину)',
                in: 'query',
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted successfully'
            ),

        ]
    )]
    public function delete(Request $request)
    {
        $fuser = $request->header('fuser');

        $requestData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer|exists:products,id',
            'all' => 'sometimes|in:true,false'
        ]);
        $requestData['all'] = isset($requestData['all']) && $requestData['all'] == 'true' ? true : false;
        $productsId = array_values($requestData['products']);

        $this->cartService->loadCart($fuser);
        if($requestData['all']){
            $this->cartService->clearCart();
        } else {
            $this->cartService->deleteItemFromCart($productsId);
        }
        return $this->api->success(null, 'deleted successfully', 200);
    }


}
