<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Carts\Cart;
use App\Models\Media\News;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Users\User;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class TestController extends Controller
{
    #[OA\Get(
        path: '/testController/',
        summary: "",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Traning'],
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
        $user1 = $request->user()->groups;
        $user2 = $request->user()->groups();

        $request->user()->addToGroupBySlug('admin');

        $user3 = $request->user()->hasGroup('admin');
        $user4 = $request->user()->hasGroup('managers');
        $haha = [];
        foreach($user3 as $etst){
            $haha = $etst;
        }

        return response()->json($etst);
    }

    #[OA\Get(
        path: '/v1/testControllerAll/',
        summary: "",
        tags: ['Traning'],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]

    public function indexList()
    {
        $allUser = User::with(['plusUser', 'profiles','groups'])->find(3);
//        return response()->json($allUser);

        // $allNews = News::with(['images','tags'])->get();
         $allProducts = Product::with([
             //'price', 'price.priceName','quantity','quantity.stockName',
             'stocks','priceName'
         ])->get();
        $cart = Cart::with(['products', 'products.price', 'products.stocks'])->find(1);
        $order = Order::with(['products', 'orderStatus', 'payType'
            //'products.price', 'products.stocks'
        ])->find(1);

        return response()->json($allProducts);
    }

    #[OA\Post(
        path: '/testControllerImageNew/{news}',
        summary: "Создать новость",

        requestBody: new OA\RequestBody(
            description: "новости",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [],
                        properties: [
                            new OA\Property(
                                property: 'file',
                                description: "Файл",
                                type: "string",
                                format: "binary"
                            ),
                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Traning'],
        parameters: [
            new OA\Parameter(
                name: 'news',
                description: 'id new',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
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

    public function testControllerImageNew(Request $request, User $news)
    {
        $mediaService = app(MediaService::class);

        $media = $mediaService->uploadFile($news,$request->file('file'),'news_image');
        return response()->json(['success' => true, 'media' => $media]);
    }

    #[OA\Get(
        path: '/test/{id}',
        summary: "",
        tags: ['Traning'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'id new',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]

    public function test(Request $request, News $news)
    {
        $data = $news;
        $news->delete();
        return response()->json($news);
    }

}
