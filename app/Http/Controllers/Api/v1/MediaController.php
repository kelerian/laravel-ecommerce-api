<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Media\DeleteImageRequest;
use App\Models\Media\Media;
use App\Services\Media\ApiResponseService;
use App\Services\Media\MediaService;
use OpenApi\Attributes as OA;

class MediaController extends Controller
{
    public function __construct(
        private ApiResponseService $api,
        private MediaService $mediaService
    ) {}
    #[OA\Delete(
        path: '/v1/media',
        summary: "Удалить картинки из выбранной модели",
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Удаление изображений",
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'model_type',
                            description: "Тип модели",
                            type: "string",
                            enum: ["news", "products", "users"],
                            example: "news"
                        ),
                        new OA\Property(
                            property: 'id_model',
                            description: "ID элемента модели",
                            type: "integer",
                            example: 42
                        ),
                        new OA\Property(
                            property: 'image_id',
                            description: "ID изображения",
                            type: "array",
                            items: new OA\Items(type: 'integer'),
                            example: [1, 2, 3]
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        tags: ['Media'],
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
    public function deletedImage(DeleteImageRequest $request)
    {
        $requestData = $request->validated();

        $data = Media::whereIn('id', $requestData['image_id'])
            ->select('id', 'model_id', 'file_name', 'disk', 'generated_conversions')
            ->get();

        $approved = $data->where('model_id', $requestData['id_model']);

        if ($approved->isEmpty()) {
            return $this->api->notFound( 'The specified images of the object');
        }

        $report = $this->mediaService->deleteImage($approved);

        return $this->api->success($report, 'Deleted successfully', 200);
    }

}
