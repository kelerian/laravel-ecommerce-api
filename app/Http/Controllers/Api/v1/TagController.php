<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Resources\Media\TagsResource;
use App\Models\Media\Tag;
use App\Services\Media\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;


class TagController extends Controller
{
    public function __construct(
        private ApiResponseService $api
    ) {}
    #[OA\Get(
        path: '/v1/tags',
        summary: "tags list",
        tags: ['Tags'],
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
        $tags = Tag::get();
        return $this->api->success(TagsResource::collection($tags));
    }

    #[OA\Post(
        path: '/v1/tags',
        summary: "Добавить тэг",
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
                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Tags'],
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
    public function create(Request $request)
    {
        Gate::authorize('create-tag');

        $tagName = $request->validate([
            'title' => 'required|unique:tags,title'
        ]);

        $newTag = new Tag();
        $newTag->fill($tagName);
        $newTag->save();

        return $this->api->success(new TagsResource($newTag));
    }
}
