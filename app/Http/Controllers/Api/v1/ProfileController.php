<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Requests\Users\ProfileCreateRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Models\Users\Profile;
use App\Services\Media\ApiResponseService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class ProfileController extends Controller
{

    public function __construct(
        private ApiResponseService $api
    ) {}
    #[OA\Get(
        path: '/v1/profile',
        summary: "профили юзера",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Profile'],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function meProfiles()
    {
        return ProfileResource::collection(
            request()
                ->user()
                ->load('profiles')->profiles
        );
    }

    #[OA\Get(
        path: '/v1/profile/{inn}',
        summary: "профиль",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'inn',
                description: 'inn компании',
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
    public function profileByInn(Profile $profile)
    {
        if(request()->user()->can('view', $profile)){
            return $this->api->success( new ProfileResource($profile) );
        } else {
            return $this->api->forbidden();
        }
    }

    #[OA\Post(
        path: '/v1/profile',
        summary: "Добавить профиль",
        security : [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [
                            'phone',
                            'title',
                            'address',
                            'inn',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'phone',
                                description: "Номер мобильного телефона",
                                type: "string",
                                default: '+********'
                            ),
                            new OA\Property(
                                property: 'title',
                                description: "Название компании",
                                type: "string",
                                default: 'ООО Луна'
                            ),
                            new OA\Property(
                                property: 'address',
                                description: "Адрес компании",
                                type: "string",
                                default: 'город Москва, улица Ленина, дом 4'
                            ),
                            new OA\Property(
                                property: 'inn',
                                description: "Инн компании (12 цифр)",
                                type: "string",
                                default: ''
                            ),
                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Profile'],
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
    public function create(ProfileCreateRequest $request)
    {
        $data = $request->only(['phone','title','address', 'inn']);
        $userId = $request->user()->id;
        $data['user_id'] = $userId;
        $profile = new Profile();
        $profile->fill($data);
        $profile->save();
        return $this->api->success( new ProfileResource($profile) );
    }

    #[OA\Patch(
        path: '/v1/profile/{inn}',
        summary: "Обновить указанные поля",
        security: [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'inn', type: "string", example: "111111111111"),
                    new OA\Property(property: 'address', type: "string", example: "Москва"),
                    new OA\Property(property: 'title', type: "string", example: 'АО Пятерочка'),
                    new OA\Property(property: 'phone', type: "string", example: '+70000000000'),
                ]
            )
        ),
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'inn',
                description: 'inn компании',
                in: 'path'
            ),
        ],
        responses: [
            new Response(
                response: 200,
                description: 'updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Profile data updated'),
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
        ],
    )]
    public function profileUpdateByInn(Request $request, Profile $profile)
    {
        if(!$request->user()->can('update', $profile)){
            return $this->api->forbidden();
        }

        $data = $request->only(['inn', 'address', 'title' ,'phone']);

        $profile->fill($data);
        $profile->save();
        return $this->api->success([],'updated profile');
    }

    #[OA\Delete(
        path: '/v1/profile/{inn}',
        summary: "Удалить профиль",
        security: [ ['sanctumAuth' => []] ],
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'inn',
                description: 'inn компании',
                in: 'path'
            ),
        ],
        responses: [
            new Response(
                response: 204,
                description: 'deleted'
            ),
            new Response(
                response: 404,
                description: 'not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function deleteProfile(Request $request, Profile $profile)
    {
        if($request->user()->cannot('delete', $profile)){
            return $this->api->forbidden();
        }
        $profile->delete();
        return $this->api->success( new ProfileResource($profile) );
    }

}
