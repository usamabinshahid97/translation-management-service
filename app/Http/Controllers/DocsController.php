<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocsController extends Controller
{
    public function index()
    {
        $jsonFile = storage_path('api-docs/api-docs.json');
        
        if (!file_exists($jsonFile)) {
            return response('API documentation not found. Please run: php artisan l5-swagger:generate', 404);
        }

        $swaggerUI = '
<!DOCTYPE html>
<html>
<head>
    <title>Translation Management Service API</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/api/docs.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>';

        return response($swaggerUI)->header('Content-Type', 'text/html');
    }

    public function json()
    {
        $jsonFile = storage_path('api-docs/api-docs.json');
        
        if (!file_exists($jsonFile)) {
            return response()->json(['error' => 'API documentation not found'], 404);
        }

        $content = file_get_contents($jsonFile);
        return response($content)->header('Content-Type', 'application/json');
    }
}