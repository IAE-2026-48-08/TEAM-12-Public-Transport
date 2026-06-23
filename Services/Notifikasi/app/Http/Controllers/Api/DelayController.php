<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delay;
use App\Services\AuditService;
use App\Services\RabbitMqService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DelayController extends Controller
{
    #[OA\Get(
        path: '/api/v1/delays',
        summary: 'Get all delays',
        tags: ['Delays'],
        security: [['ApiKeyAuth' => [], 'bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Delay list retrieved successfully',
            'data' => Delay::all(),
            'meta' => [
                'service_name' => 'Notification-Delay-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/delays/{id}',
        summary: 'Get specific delay by ID',
        tags: ['Delays'],
        security: [['ApiKeyAuth' => [], 'bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the delay record',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success'
            ),
            new OA\Response(
                response: 404,
                description: 'Delay not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function show($id)
    {
        $delay = Delay::find($id);

        if (!$delay) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delay not found',
                'errors' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Delay retrieved successfully',
            'data' => $delay,
            'meta' => [
                'service_name' => 'Notification-Delay-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/delays',
        summary: 'Create a new delay record',
        tags: ['Delays'],
        security: [['ApiKeyAuth' => [], 'bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['schedule_code', 'reason', 'delay_minutes'],
                properties: [
                    new OA\Property(property: 'schedule_code', type: 'string', example: '1'),
                    new OA\Property(property: 'reason', type: 'string', example: 'Cuaca buruk / hujan lebat'),
                    new OA\Property(property: 'delay_minutes', type: 'integer', example: 30)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function store(Request $request)
    {
        $token = $request->bearerToken();

        // 1. Verifikasi schedule_code ke Rute Service
        try {
            $ruteRes = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("http://rute-jadwal-app/api/v1/schedules/{$request->schedule_code}");

            if ($ruteRes->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal (schedule_code) tidak valid atau tidak ditemukan di Rute-Jadwal Service.',
                    'errors' => null
                ], 404);
            }
        } catch (\Throwable $e) {
            // Fallback toleransi jika service offline
            report($e);
        }

        // 2. Verifikasi keterhubungan ke Ticket Service
        try {
            $ticketRes = \Illuminate\Support\Facades\Http::withToken($token)
                ->withHeaders(['X-IAE-KEY' => '102022400251']) // API Key Bayu
                ->timeout(10)
                ->get("http://ticket-pembayaran-web/api/v1/tickets");

            if ($ticketRes->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Layanan Tiket & Pembayaran tidak dapat dihubungi atau tidak merespon.',
                    'errors' => null
                ], 502);
            }
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke Layanan Tiket & Pembayaran: ' . $e->getMessage(),
                'errors' => null
            ], 502);
        }

        $delay = Delay::create([
            'schedule_code' => $request->schedule_code,
            'reason' => $request->reason,
            'delay_minutes' => $request->delay_minutes
        ]);

        $auditResponse = AuditService::sendAudit($delay);

        return response()->json([
            'status' => 'success',
            'message' => 'Delay created successfully',
            'data' => $delay,
            'audit_response' => $auditResponse->body(),
            'meta' => [
                'service_name' => 'Notification-Delay-Service',
                'api_version' => 'v1'
            ]
        ], 201);
    }

    #[OA\Post(
        path: '/api/v1/delays/notifications',
        summary: 'Send delay notification mock',
        tags: ['Delays'],
        security: [['ApiKeyAuth' => [], 'bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['schedule_code'],
                properties: [
                    new OA\Property(property: 'schedule_code', type: 'string', example: '1')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function sendNotification(Request $request)
    {
        $token = $request->bearerToken();

        // 1. Verifikasi schedule_code ke Rute Service
        try {
            $ruteRes = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("http://rute-jadwal-app/api/v1/schedules/{$request->schedule_code}");

            if ($ruteRes->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal (schedule_code) tidak ditemukan di Rute Service.',
                    'errors' => null
                ], 404);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 2. Verifikasi keterhubungan ke Ticket Service
        try {
            $ticketRes = \Illuminate\Support\Facades\Http::withToken($token)
                ->withHeaders(['X-IAE-KEY' => '102022400251']) // API Key Bayu
                ->timeout(10)
                ->get("http://ticket-pembayaran-web/api/v1/tickets");

            if ($ticketRes->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghubungi Layanan Tiket.',
                    'errors' => null
                ], 502);
            }
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke Layanan Tiket & Pembayaran: ' . $e->getMessage(),
                'errors' => null
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Delay notification sent successfully',
            'data' => [
                'schedule_code' => $request->schedule_code,
                'notification' => 'Your trip has been delayed'
            ],
            'meta' => [
                'service_name' => 'Notification-Delay-Service',
                'api_version' => 'v1'
            ]
        ]);
    }
}