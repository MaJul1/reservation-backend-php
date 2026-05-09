<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ResourceController extends Controller
{
    #[OA\Post(
        path: "/api/resource",
        tags: ["Resource"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    new OA\Property(property: "description", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $resource = Resource::create($validated);

        return response()->json($resource, 200);
    }

    #[OA\Get(
        path: "/api/resource/get-resources-info",
        tags: ["Resource"],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function info()
    {
        return response()->json(Resource::all(), 200);
    }

    #[OA\Get(
        path: "/api/resource/get-resources",
        tags: ["Resource"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "size", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "sortBy", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function index(Request $request)
    {
        $size = $request->query('size', 10);
        $sortBy = $request->query('sortBy', 'id');

        $query = Resource::query();

        if ($sortBy) {
            $direction = 'asc';
            if (str_starts_with($sortBy, '-')) {
                $direction = 'desc';
                $sortBy = substr($sortBy, 1);
            }
            $query->orderBy($sortBy, $direction);
        }

        return response()->json($query->paginate($size), 200);
    }

    #[OA\Get(
        path: "/api/resource/get-resrouce-by-id/{id}",
        tags: ["Resource"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function show($id)
    {
        $resource = Resource::find($id);
        if (!$resource) {
            return response()->json(['message' => 'Resource not found'], 404);
        }
        return response()->json($resource, 200);
    }
}
