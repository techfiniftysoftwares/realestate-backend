<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "OpalLuxe Realty API Documentation - Real Estate Management System",
    title: "OpalLuxe Realty API"
)]
#[OA\Server(
    url: "http://localhost/api",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Tag(
    name: "Properties",
    description: "Property management endpoints"
)]
#[OA\Tag(
    name: "Authentication",
    description: "User authentication endpoints"
)]
#[OA\Tag(
    name: "Filters",
    description: "Filter options for property search"
)]
abstract class Controller
{
    //
}
