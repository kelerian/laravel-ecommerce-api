<?php

namespace App\Http\Controllers\Api\v1;

use App\Dto\User\RegisterDto;
use App\Http\Requests\Users\RegisterUserRequest;
use App\Http\Resources\Users\UserResource;
use App\Services\Media\ApiResponseService;
use App\Services\User\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private ApiResponseService $api
    )
    {}

    #[OA\Post(
        path: '/v1/auth/register',
        summary: "Регистрация",
        requestBody: new OA\RequestBody(
            description: "Параметры аккаунта",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: [
                            'email',
                            'name',
                            'lastname',
                            'birthday',
                            'phone',
                            'address',
                            'gender',
                            'password',
                            'password_confirmation',
                            'title',
                            'company_address',
                            'inn',
                            'fuser_id',
                        ],
                        properties: [
                            new OA\Property(
                                property: 'email',
                                description: "Адрес электронной почты",
                                type: "string",
                                default: '@mail.ru'
                            ),
                            new OA\Property(
                                property: 'name',
                                description: "Имя",
                                type: "string",
                                default: 'John'
                            ),
                            new OA\Property(
                                property: 'lastname',
                                description: "Фамилия",
                                type: "string",
                                default: 'Doe'
                            ),
                            new OA\Property(
                                property: 'birthday',
                                description: "дата рождения",
                                type: "string",
                                default: '1990-05-15'
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
                                property: 'gender',
                                description: "Пол, female - женский, male - мужской",
                                type: "string",
                                default: '',
                                enum: ['female', 'male'],

                            ),
                            new OA\Property(
                                property: 'password',
                                description: "Пароль",
                                type: "string",
                                default: '12345qQ_'
                            ),
                            new OA\Property(
                                property: 'password_confirmation',
                                description: "Повторите пароль",
                                type: "string",
                                default: '12345qQ_'
                            ),
                            new OA\Property(
                                property: 'title',
                                description: "Название компании",
                                type: "string",
                                default: 'ООО Луна'
                            ),
                            new OA\Property(
                                property: 'company_address',
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
        tags: ['Auth'],
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
    public function register(RegisterUserRequest  $request)
    {
        $params = $request->validated();
        $dto = RegisterDto::fromArray($params);

        $userAgent = $request->header('User-Agent') ?? 'Unknown';
        $ip = $request->ip() ?? time() . random_int(1,10000000000);

        $responseDto = $this->authService
            ->createUser($dto,userAgent:$userAgent, ip:$ip);

        return $this->api->success([
            'user' => new UserResource($responseDto->user),
            'token' => $responseDto->token,
            'expires_at' => $responseDto->expiresAt
        ],'The user has been successfully registered',201);
    }

    #[OA\Post(
        path: '/v1/auth/refreshToken',
        summary: "Обновить токен",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Auth'],
        responses: [
            new Response(
                response: 201,
                description: 'created',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ],
    )]

    public function refreshToken(Request $request)
    {
        $userAgent = $request->header('User-Agent') ?? 'Unknown';
        $ip = $request->ip() ?? time() . random_int(1,10000000000);

        $currentToken = $request->user()->currentAccessToken();

        $token = $this->authService->createToken(userAgent:$userAgent, ip:$ip);

        $currentToken->delete();

        $expiresAt = $this->authService->getTokenExpiry()->toISOString();

        return $this->api->success([
            'token' => $token,
            'expires_at' => $expiresAt
        ], 'created new token',201);
    }

    #[OA\Post(
        path: '/v1/auth/login',
        summary: "Авторизация",
        requestBody: new OA\RequestBody(
            description: "Параметры аккаунта",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                        required: ['email', 'password'],
                        properties: [
                            new OA\Property(
                                property: 'email',
                                description: "Адрес электронной почты",
                                type: "string",
                                default: '@mail.ru'
                            ),
                            new OA\Property(
                                property: 'password',
                                description: "Пароль",
                                type: "string",
                                default: '12345qQ_'
                            ),
                        ],
                        type: "object"
                    )
                )
            ]
        ),
        tags: ['Auth'],
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
    public function login(Request $request)
    {
        $params = $request->validate([
            'email' => 'required|email:rfc,dns',
            'password' => 'required',
        ]);

        $userAgent = $request->header('User-Agent') ?? 'Unknown';
        $ip = $request->ip() ?? time() . random_int(1,10000000000);

        $responseDto = $this->authService->login(
            email: $params['email'],
            password: $params['password'],
            userAgent: $userAgent,
            ip: $ip );

        return $this->api->success([
            'user' => new UserResource($responseDto->user),
            'token' => $responseDto->token,
            'expires_at' => $responseDto->expiresAt
        ]);
    }

    #[OA\Post(
        path: '/v1/auth/logout',
        summary: "Выход из аккаунта",
        security : [ ['sanctumAuth' => []] ],
        tags: ['Auth'],
        responses: [
            new Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent()
            ),
            new Response(
                response: 401,
                description: 'unauthorized'
            ),
        ],
    )]

    public function logout(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        $currentToken->delete();

        return $this->api->success([], 'token deleted',200);
    }
}
