<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Resource;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class ReservationController extends Controller
{
    #[OA\Post(
        path: "/api/reservation/create-reservation",
        tags: ["Reservation"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "start", type: "string", format: "date-time"),
                    new OA\Property(property: "end", type: "string", format: "date-time"),
                    new OA\Property(property: "resourceId", type: "integer"),
                    new OA\Property(property: "firstName", type: "string"),
                    new OA\Property(property: "lastName", type: "string"),
                    new OA\Property(property: "phoneNumber", type: "string"),
                    new OA\Property(property: "email", type: "string")
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
            'resourceId' => 'required|exists:resources,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'phoneNumber' => 'required|string',
            'email' => 'required|email',
        ]);

        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);
        $resourceId = $validated['resourceId'];

        if ($this->hasOverlap($resourceId, $start, $end)) {
            return response()->json(['message' => 'Overlapping reservation exists'], 409);
        }

        $reservation = Reservation::create([
            'resource_id' => $resourceId,
            'start' => $start,
            'end' => $end,
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'phone_number' => $validated['phoneNumber'],
            'email' => $validated['email'],
            'status' => 'pending',
        ]);

        return response()->json($reservation, 200);
    }

    private function hasOverlap($resourceId, $start, $end, $excludeId = null)
    {
        $query = Reservation::where('resource_id', $resourceId)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($inner) use ($start, $end) {
                    $inner->where('start', '<', $end)
                          ->where('end', '>', $start);
                });
            });

        $query->where('status', '!=', 'cancelled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    #[OA\Get(
        path: "/api/reservation/get-reservations",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "size", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
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

        $query = Reservation::query();

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
        path: "/api/reservation/get-reservation/{id}",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function show($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        return response()->json($reservation, 200);
    }

    #[OA\Get(
        path: "/api/reservation/get-reservation-by-resource/{resourceId}",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "resourceId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "size", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "sortBy", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function getByResource(Request $request, $resourceId)
    {
        $size = $request->query('size', 10);
        $sortBy = $request->query('sortBy', 'id');

        $query = Reservation::where('resource_id', $resourceId);

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

    #[OA\Post(
        path: "/api/reservation/ongoing-reservation",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "id", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function ongoing(Request $request)
    {
        $id = $request->query('id');
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        $reservation->status = 'ongoing';
        $reservation->save();
        return response()->json($reservation, 200);
    }

    #[OA\Post(
        path: "/api/reservation/cancel-reservation",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "id", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function cancel(Request $request)
    {
        $id = $request->query('id');
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        $reservation->status = 'cancelled';
        $reservation->save();
        return response()->json($reservation, 200);
    }

    #[OA\Post(
        path: "/api/reservation/done-reservation",
        tags: ["Reservation"],
        parameters: [
            new OA\Parameter(name: "id", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function done(Request $request)
    {
        $id = $request->query('id');
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }
        $reservation->status = 'done';
        $reservation->save();
        return response()->json($reservation, 200);
    }

    #[OA\Post(
        path: "/api/reservation/move-reservation",
        tags: ["Reservation"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "newStart", type: "string", format: "date-time"),
                    new OA\Property(property: "newEnd", type: "string", format: "date-time")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function move(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:reservations,id',
            'newStart' => 'required|date',
            'newEnd' => 'required|date|after:newStart',
        ]);

        $reservation = Reservation::find($validated['id']);
        $newStart = Carbon::parse($validated['newStart']);
        $newEnd = Carbon::parse($validated['newEnd']);

        if ($this->hasOverlap($reservation->resource_id, $newStart, $newEnd, $reservation->id)) {
            return response()->json(['message' => 'Overlapping reservation exists at new time'], 409);
        }

        $reservation->start = $newStart;
        $reservation->end = $newEnd;
        $reservation->save();

        return response()->json($reservation, 200);
    }
}
