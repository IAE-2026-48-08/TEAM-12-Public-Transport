<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Services\Iae\IaeAuditClient;
use App\Services\Iae\IaePublisher;

#[OA\Info(
    version: "1.0.0",
    title: "Tickets Service API Documentation",
    description: "L5 Swagger OpenApi API Documentation for Tickets Microservice",
    contact: new OA\Contact(email: "bayusamudera@example.com")
)]
#[OA\Server(
    url: "http://localhost/api",
    description: "Local Tickets Service API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Masukkan Token JWT SSO untuk mengakses endpoint"
)]
class TicketController extends Controller
{
    #[OA\Get(
        path: "/v1/tickets",
        summary: "Get list of tickets (Collection)",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of tickets retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - Invalid X-IAE-KEY"
            )
        ]
    )]
    public function index()
    {
        $tickets = Ticket::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $tickets,
            'meta' => [
                'service_name' => 'Tickets-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/v1/tickets/{id}",
        summary: "Get details of a specific ticket (Resource)",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "UUID of the ticket",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Ticket retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Resource not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Resource not found"),
                        new OA\Property(property: "errors", type: "object", nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized"
            )
        ]
    )]
    public function show($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found',
                'errors' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $ticket,
            'meta' => [
                'service_name' => 'Tickets-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/v1/tickets",
        summary: "Create a new ticket (Action)",
        tags: ["Tickets"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["schedule_id", "seat_number"],
                properties: [
                    new OA\Property(property: "schedule_id", type: "string", example: "SCH-12345"),
                    new OA\Property(property: "seat_number", type: "string", example: "A12"),
                    new OA\Property(property: "total_price", type: "integer", example: 75000, nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Resource created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Resource created successfully"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized"
            )
        ]
    )]
    public function store(Request $request, IaeAuditClient $audit, IaePublisher $publisher)
    {
        $request->validate([
            'schedule_id' => 'required',
            'seat_number' => 'required'
        ]);

        $ticket = new Ticket();
        $ticket->schedule_id = $request->schedule_id;
        $ticket->seat_number = $request->seat_number;
        $ticket->total_price = $request->total_price ?? 100000;
        $ticket->status = 'LUNAS';
        $ticket->save();

        $receipt = null;
        try {
            $result = $audit->audit('TicketTransactionCreated', [
                'event'          => 'ticket.purchased',
                'ticket_id'      => $ticket->id,
                'schedule_id'    => $ticket->schedule_id,
                'seat_number'    => $ticket->seat_number,
                'total_price'    => $ticket->total_price,
                'actor'          => $request->attributes->get('iae_subject'),
            ]);
            $receipt = $result['receipt'];
        } catch (\Throwable $e) {
            report($e);
        }

        if ($receipt) {
            $ticket->receipt_number = $receipt;
            $ticket->status = 'AUDITED';
            $ticket->save();
        }

        try {
            $publisher->publish([
                'event_name'            => 'ticket.purchased',
                'service_name'          => 'Tickets-Service',
                'api_version'           => 'v1',
                'occurred_at'           => now()->toIso8601String(),
                'ticket' => [
                    'id'                    => $ticket->id,
                    'schedule_id'           => $ticket->schedule_id,
                    'seat_number'           => $ticket->seat_number,
                    'total_price'           => $ticket->total_price,
                    'status'                => $ticket->status,
                    'legacy_receipt_number' => $receipt,
                ],
                'published_by' => [
                    'api_key' => config('services.iae.api_key'),
                    'team_id' => config('services.iae.team_id'),
                ],
                'approved_by'           => [
                    'sso_subject' => $request->attributes->get('iae_subject'),
                    'role'        => $request->attributes->get('iae_role'),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Resource created successfully',
            'data' => $ticket,
            'meta' => [
                'service_name' => 'Tickets-Service',
                'api_version' => 'v1',
                'audit_receipt' => $receipt,
            ]
        ], 201);
    }
}
