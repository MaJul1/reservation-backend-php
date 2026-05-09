<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FacilityController extends Controller
{
    #[OA\Post(
        path: "/api/facility",
        tags: ["Facility"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "capacity", type: "integer")
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
            'capacity' => 'required|integer|min:1',
        ]);

        $facility = Facility::create($validated);

        return response()->json($facility, 200);
    }

    #[OA\Get(
        path: "/api/facility/get-facilities-info",
        tags: ["Facility"],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function info()
    {
        return response()->json(Facility::all(), 200);
    }

    #[OA\Get(
        path: "/api/facility/get-facilities",
        tags: ["Facility"],
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

        $query = Facility::query();

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
        path: "/api/facility/get-facility-by-id/{id}",
        tags: ["Facility"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function show($id)
    {
        $facility = Facility::find($id);
        if (!$facility) {
            return response()->json(['message' => 'Facility not found'], 404);
        }
        return response()->json($facility, 200);
    }
}
