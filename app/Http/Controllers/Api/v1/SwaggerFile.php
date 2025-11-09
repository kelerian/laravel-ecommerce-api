<?php

namespace App\Http\Controllers\Api\v1;

use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

#[OA\Info(
    version: "1.0.0",
    description: "Laravel project API",
    title: "Api version 1",
    termsOfService: "http://swagger.io/terms/",
    contact: new OA\Contact(email: "contact@example.com"),
    license: new OA\License(name: "Apache 2.0", url: "http://www.apache.org/licenses/LICENSE-2.0.html")
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumAuth',
    type: 'http',
    scheme: 'Bearer',
    bearerFormat: 'JWT'
)]
#[OA\Get(
    path: '/testAuth/',
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

class SwaggerFile extends Controller
{
    //
}
