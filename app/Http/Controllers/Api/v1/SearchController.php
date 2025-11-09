<?php

namespace App\Http\Controllers\Api\v1;

use App\Dto\Search\SearchDto;
use App\Http\Requests\Search\SearchRequest;
use App\Services\Media\ApiResponseService;
use App\Services\Search\SearchService;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService,
        private ApiResponseService $api
    ) {}


    #[OA\Get(
        path: '/v1/search',
        summary: "search news",
        tags: ['Search'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'page number',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'perPage',
                description: 'news limit',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'q',
                description: 'емайл криейтера новости',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'search_type',
                description: 'fulltext или autocomplete',
                in: 'query'
            ),
            new OA\Parameter(
                name: 'models_type',
                description: 'product или news',
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
    public function search(SearchRequest $request)
    {
        $params = $request->validated();

        $dto = SearchDto::fromArray($params);

        $results = $this->searchService->search($dto);

        return $this->api->success($results);
    }
}
