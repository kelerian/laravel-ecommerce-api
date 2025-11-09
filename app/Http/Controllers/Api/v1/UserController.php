<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Users\UserUpdateRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\Users\User;
use App\Services\Media\ApiResponseService;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class UserController extends Controller
{
    public function __construct(
        private ApiResponseService $api
    ) {}

    #[OA\Get(
        path: '/v1/user/data',
        summary: "данные юзера",
        security : [ ['sanctumAuth' => []] ],
        tags: ['User'],
        responses: [
            new Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent()
            ),
        ],
    )]
    public function me()
    {
        $userId = request()->user()->id;
        $userData = User::withAllRelations()->findOrFail($userId);

        return $this->api->success(new UserResource($userData));
    }

    #[OA\Patch(
        path: '/v1/user/data',
        summary: "Обновить указанные поля",
        security : [ ['sanctumAuth' => []] ],
        requestBody: new OA\RequestBody(
            description: "Данные для обновления (только изменяемые поля)",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: "string", example: "Иван"),
                    new OA\Property(property: 'lastname', type: "string", example: "Ефремов"),
                    new OA\Property(property: 'gender_slug', type: "string", example: 'male'),
                    new OA\Property(property: 'birthday', type: "string", example: '1990-05-15'),
                    new OA\Property(property: 'address', type: "string", example: 'Москва'),
                ]
            )
        ),
        tags: ['User'],
        responses: [
            new Response(
                response: 200,
                description: 'updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Личные данные обновлены'),
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
    public function meUpdate(UserUpdateRequest $request)
    {
        $dataUser = $request->only(['name', 'email']);
        $dataUserPlus = $request->only([ 'lastname', 'gender_slug', 'birthday', 'address']);

        $request->user()->update($dataUser);
        $plusUser = $request->user()->plusUser;
        $plusUser->fill($dataUserPlus);
        $plusUser->save();

        return $this->api->success([],'updated');
    }

    #[OA\Get(
        path: '/v1/user/{email}/user-data',
        summary: "Получить данные пользователя по email(для администраторов)",
        security : [ ['sanctumAuth' => []] ],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'email',
                description: 'email пользователя',
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
    public function userData(User $user)
    {
        if (request()->user()->can('view', $user)) {
            return $this->api->success(new UserResource($user));
        } else {
            return $this->api->forbidden();
        }
    }


}
