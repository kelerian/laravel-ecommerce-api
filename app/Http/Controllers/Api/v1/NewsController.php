<?php

namespace App\Http\Controllers\Api\v1;

use App\Dto\News\FilterForNewsListDto;
use App\Dto\News\NewsDto;
use App\Http\Requests\Media\IndexNewsRequest;
use App\Http\Requests\Media\NewCreateRequest;
use App\Http\Requests\Media\NewsUpdateRequest;
use App\Http\Resources\Media\NewsDetailResource;
use App\Http\Resources\Media\NewsResource;
use App\Models\Media\News;
use App\Services\Media\ApiResponseService;
use App\Services\Media\NewsQueryService;
use App\Services\Media\NewsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class NewsController extends Controller
{
    public function __construct(
        private ApiResponseService $api,
        private NewsServices $newsServices,
        private NewsQueryService $newsQueryServ,
    ) {}

    #[OA\Get(
        path: '/v1/news',
        summary: "news list",
        tags: ['News'],
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
                name: 'user_email',
                description: 'емайл криейтера новости',
                in: 'query'
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
            new OA\Parameter(
                name: 'tags[]',
                description: 'Список slug тегов. Пример: ?tags[]=impedit&tags[]=non',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string', example: 'impedit')
                ),
                style: 'form',
                explode: true
            ),
            new OA\Parameter(
                name: 'tags_flag',
                description: 'флаг: true - у новости должны быть только переданные теги, false - один из тегов',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    enum: [true, false]
                )
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
    public function index(IndexNewsRequest $request)
    {
        $filters = $request->validated();

        $dto = FilterForNewsListDto::fromArray($filters);
        $query = $this->newsQueryServ->newsListWithFilter($dto);

        return NewsResource::collection($query)
            ->additional([
                'meta' => [
                    'filters' => $dto->toArray(),
                ],
            ])
            ->response()
            ->setStatusCode(200);
    }
    #[OA\Get(
        path: '/v1/news/{slug}',
        summary: "news by slug",
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'new slug',
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
    public function show(string $slug)
    {
        $new = $this->newsQueryServ->getNewsDetailBySlug($slug);

        return new NewsDetailResource($new);
    }

    #[OA\Post(
        path: '/v1/news',
        summary: "Добавить новость",
        security : [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [],
                        properties: [
                            new OA\Property(
                                property: 'title',
                                description: "Название новости",
                                type: "string",
                                default: ''
                            ),
                            new OA\Property(
                                property: 'content',
                                description: "Текст",
                                type: "string",
                                default: ''
                            ),
                            new OA\Property(
                                property: 'detail_picture',
                                description: "Детальная картинка",
                                type: "string",
                                format: "binary"
                            ),
                            new OA\Property(
                                property: 'preview_picture',
                                description: "Картинка превью",
                                type: "string",
                                format: "binary"
                            ),
                            new OA\Property(
                                property: 'images[]',
                                description: "Массив изображений",
                                type: "array",
                                items: new OA\Items(
                                    type: "file",
                                    format: "binary"
                                )
                            ),
                            new OA\Property(
                                property: 'tags',
                                description: "теги",
                                type: "array",
                                items: new OA\Items(
                                    type: "string",
                                )
                            ),

                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['News'],
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
    public function create(NewCreateRequest $request)
    {
        $params = $request->validated();
        $dto = NewsDto::fromArray($params);
        $userId = $request->user()->id;

        $new = $this->newsServices->create($dto, $userId);

        return $this->api->success((new NewsDetailResource($new)), 'success', 201);
    }

    #[OA\Post(
        path: '/v1/news/{slug}',
        summary: "Обновить указанные поля новости",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'title',
                            description: "Название новости",
                            type: "string",
                            example: "Новое название"
                        ),
                        new OA\Property(
                            property: 'content',
                            description: "Текст новости",
                            type: "string",
                            example: "Обновлённый текст"
                        ),
                        new OA\Property(
                            property: 'detail_picture',
                            description: "Детальная картинка",
                            type: "string",
                            format: "binary"
                        ),
                        new OA\Property(
                            property: 'preview_picture',
                            description: "Картинка превью",
                            type: "string",
                            format: "binary"
                        ),
                        new OA\Property(
                            property: 'images[]',
                            description: "Дополнительные изображения",
                            type: "array",
                            items: new OA\Items(
                                type: "file",
                                format: "binary"
                            )
                        ),
                        new OA\Property(
                            property: 'tags',
                            description: "теги",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                            )
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Символьный код новости',
                in: 'path',
            )
        ],
        responses: [
            new Response(
                response: 200,
                description: 'updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: "updated new"),
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
                description: 'Новость не найдена'
            ),
            new Response(
                response: 422,
                description: 'Ошибка валидации'
            ),
        ]
    )]
    public function update(NewsUpdateRequest $request, $slug)
    {
        $new = News::where('slug', $slug)->firstOrFail();

        if ($request->user()->cannot('update',$new)) {
            return $this->api->forbidden();
        };
        $filters = $request->validated();

        $dto = NewsDto::fromArray($filters);
        $userId = $request->user()->id;

        $new = $this->newsServices->update($new, $dto, $userId);


        return $this->api->success(new NewsDetailResource($new),'updated new', 200);
    }

    #[OA\Delete(
        path: '/v1/news/{slug}',
        summary: "Удалить новость",
        security: [['sanctumAuth' => []]],
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Символьный код новости',
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
    public function delete(Request $request, News $news)
    {
        if ($request->user()->cannot('delete', $news)) {
            return $this->api->forbidden();
        }

        $news->delete();
        Cache::tags('news')->flush();

        return $this->api->success(null, 'News deleted successfully', 200);
    }
}
