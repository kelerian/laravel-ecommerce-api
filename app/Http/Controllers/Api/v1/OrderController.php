<?php

namespace App\Http\Controllers\Api\v1;

use App\Dto\Order\FilterForOrderListDto;
use App\Dto\Order\OrderCreateDto;
use App\Http\Requests\Orders\OrderCreateRequest;
use App\Http\Requests\Orders\OrderIndexRequest;
use App\Http\Resources\Carts\BasketResource;
use App\Http\Resources\Orders\OrderIndexResource;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatus;
use App\Models\Orders\PayType;
use App\Services\Media\ApiResponseService;
use App\Services\Orders\OrderQueryServices;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class OrderController extends Controller
{

    public function __construct(
        private OrderService       $orderService,
        private ApiResponseService $api,
        private OrderQueryServices $orderQueryServ
    )
    {}

    #[OA\Get(
        path: '/v1/order/cart',
        summary: "Заказ для оформления",
        security: [['sanctumAuth' => []]],
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'fuser',
                description: 'fuser id',
                in: 'header'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 400,
                description: 'fail'
            ),
        ],
    )]
    public function getCart(Request $request)
    {
        $fuserId = $request->header('fuser');

        $cartOrder = new BasketResource($this->orderService->getCartToOrder($fuserId));
        return $this->api->success($cartOrder);

    }

    #[OA\Patch(
        path: '/v1/order/{id}/cancel',
        summary: "Отмена заказа",
        security: [['sanctumAuth' => []]],
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'id заказа',
                in: 'path'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'order cancelled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            description: 'Новый статус заказа',
                            type: 'string',
                            example: 'canceled'
                        ),

                    ]
                )
            ),
            new Response(
                response: 400,
                description: 'Ошибка отмены заказа'
            ),
            new Response(
                response: 404,
                description: 'Заказ не найден'
            ),
        ],
    )]
    public function cancelOrder(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->cannot('canceled', $order)) {
            return $this->api->forbidden('You cannot cancel this order');
        }
        $order->loadMissing('orderStatus');
        $status = $order->orderStatus?->slug;

        if ($status === OrderStatus::CANCELLED || !OrderStatus::canBeCancelled($status)
            && !$user->isAdmin()) {
            throw ValidationException::withMessages([
                'message' => 'You cannot cancel this order',
            ]);
        }

        $this->orderService->cancelOrder($order);

        return $this->api->success([], 'order cancelled', 200);
    }

    #[OA\Post(
        path: '/v1/order',
        summary: "Создание заказа",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Параметры заказа",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [
                            'email',
                            'phone',
                            'address',
                            'pay_type',
                            'fuser_id'
                        ],
                        properties: [
                            new OA\Property(
                                property: 'email',
                                description: "Адрес электронной почты",
                                type: "string",
                                default: '@mail.ru'
                            ),
                            new OA\Property(
                                property: 'phone',
                                description: "Номер мобильного телефона",
                                type: "string",
                                default: '+********'
                            ),
                            new OA\Property(
                                property: 'address',
                                description: "Адрес",
                                type: "string",
                                default: 'город Москва, улица Ленина, дом 4'
                            ),
                            new OA\Property(
                                property: 'pay_type',
                                description: "Тип оплаты",
                                type: "string",
                                default: '',
                                enum: ['online', 'offline'],

                            ),
                            new OA\Property(
                                property: 'fuser_id',
                                description: "айди фусер",
                                type: "string",
                                default: ''
                            ),

                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Orders'],
        responses: [
            new Response(
                response: 201,
                description: 'created',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 400,
                description: 'fail'
            ),
        ],
    )]
    public function store(OrderCreateRequest $request)
    {
        $params = $request->validated();

        $params['user_id'] = $request->user()->id;
        $dto = OrderCreateDto::fromArray($params);

        $newOrderId = $this->orderService->createOrder($dto);

        return $this->api->success(['orderId' => $newOrderId], 'order created', 201);
    }

    #[OA\Get(
        path: '/v1/order',
        summary: "order list",
        security: [['sanctumAuth' => []]],
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'all_orders',
                description: 'Показать все заказы (только для админов) (true/false, параметр необязательный)',
                in: 'query'
            ),
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
                name: 'email',
                description: 'емайл в заказе',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'id пользователя (только для администраторов)',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'pay_type',
                description: 'тип оплаты (offline, online)',
                in: 'query',
            ),
            new OA\Parameter(
                name: 'order_status',
                description: 'ордер статус ',
                in: 'query',
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'параметр сортировки',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'direction',
                description: 'параметр сортировки',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'date_from',
                description: 'дата от (1990-05-15)',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'date_to',
                description: 'дата до (1990-05-15)',
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
    public function index(OrderIndexRequest $request)
    {
        $filters = $request->validated();

        $user = $request->user();
        $dto = FilterForOrderListDto::fromArray($filters);

        $query = $this->orderQueryServ->orderListWithFilter($dto, $user);

        return OrderIndexResource::collection($query)
            ->additional([
                'meta' => [
                    'filters' => $dto->toArray(),
                ],
            ])
            ->response()
            ->setStatusCode(200);
    }

    #[OA\Post(
        path: '/v1/order/repeat',
        summary: "Повторить заказ, кладет товары из заказа в корзину",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Параметры заказа",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [],
                        properties: [
                            new OA\Property(
                                property: 'order_id',
                                description: "id заказа",
                                type: "string",
                                default: ''
                            ),

                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'fuser',
                description: 'fuser_id',
                in: 'header'
            ),
        ],
        responses: [
            new Response(
                response: 201,
                description: 'success',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 400,
                description: 'fail'
            ),
        ],
    )]
    public function repeatOrder(Request $request)
    {
        $params = $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);
        $fuser = $request->header('fuser');

        $order = $request->user()->orders()->where('id', $params['order_id'])->firstOrFail();
        $result = $this->orderService->repeatOrder($order, $fuser);

        return $this->api->success($result);
    }

    #[OA\Get(
        path: '/v1/order/statuses',
        summary: "",
        tags: ['Orders'],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function statuses(Request $request)
    {
        return $this->api->success(OrderStatus::get());
    }

    #[OA\Get(
        path: '/v1/order/pay-types',
        summary: "",
        tags: ['Orders'],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function payTypes(Request $request)
    {
        return $this->api->success(PayType::get());
    }

    #[OA\Patch(
        path: '/v1/order/{id}/change-status',
        summary: "Обновление статуса заказа для администраторов",
        security: [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: "string", example: "prinyat"),
                ]
            )
        ),
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'order id',
                in: 'path'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order status changed'),
                    ]
                )
            ),
            new Response(
                response: 401,
                description: 'unauthorized'
            ),
            new Response(
                response: 500,
                description: 'database error'
            ),
            new Response(
                response: 422,
                description: 'Validation error or invalid status transition'
            ),
            new Response(
                response: 403,
                description: 'To change the order status, you must have the "administrator" status'
            ),
        ],
    )]
    public function changeOrderStatus(Request $request, Order $order)
    {
        if ($request->user()->cannot('changeStatus',$order)) {
            return $this->api->forbidden('To change the order status, you must have the "administrator" status');
        }
        $params = $request->validate([
            'status' => [
                'required',
                'string',
                'exists:order_statuses,slug',
                Rule::notIn([OrderStatus::CANCELLED]),
            ]
        ],[
            'status.not_in' => 'Cancelling orders is not allowed via this endpoint'
        ]);

        $changed = $order->changeStatus($params['status']);

        return $this->api->success([],$changed ? "Status changed" : "Status already set");

    }
}
