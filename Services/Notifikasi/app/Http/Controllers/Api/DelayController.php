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
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success'
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

    public function store(Request $request)
    {
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

    public function sendNotification(Request $request)
    {
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

    public function testRabbit(Request $request)
    {
        if (!$request->has('message')) {
            return response()->json([
                'status' => 'error',
                'message' => 'message (object or string) is required.'
            ], 400);
        }

        try {
            RabbitMqService::publish('test_queue', $request->message);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesan berhasil dikirim ke RabbitMQ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim ke RabbitMQ: ' . $e->getMessage()
            ], 500);
        }
    }
}