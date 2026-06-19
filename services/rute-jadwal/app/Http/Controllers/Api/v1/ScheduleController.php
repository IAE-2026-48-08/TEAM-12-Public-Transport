<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Services\Iae\IaeAuditClient;
use App\Services\Iae\IaePublisher;

class ScheduleController extends Controller
{
    #[OA\Get(
        path: '/api/v1/schedules',
        summary: 'Mengambil daftar rute dan jadwal',
        tags: ['Schedules'],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil data')
        ]
    )]
    public function index()
    {
        $schedules = Schedule::all();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $schedules,
            'meta' => [
                'service_name' => 'Rute-Jadwal-Service',
                'api_version' => 'v1'
            ]
        ], 200); 
    }

    #[OA\Post(
        path: '/api/v1/schedules',
        summary: 'Mendaftarkan jadwal armada baru',
        tags: ['Schedules'],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['route', 'departure_time', 'facilities', 'price'],
                properties: [
                    new OA\Property(property: 'route', type: 'string', example: 'Bandung - Jakarta'),
                    new OA\Property(property: 'departure_time', type: 'string', format: 'date-time', example: '2026-06-03 08:00:00'),
                    new OA\Property(property: 'facilities', type: 'string', example: 'AC, Reclining Seat, WiFi'),
                    new OA\Property(property: 'price', type: 'number', example: 150000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Jadwal berhasil ditambahkan')
        ]
    )]

    public function store(Request $request, IaeAuditClient $audit, IaePublisher $publisher)
    {
        $schedule = Schedule::create($request->all());

        $receipt = null;
        try {
            $result = $audit->audit('ScheduleCreated', [
                'event'          => 'schedule.created',
                'schedule_id'    => $schedule->id,
                'route'          => $schedule->route,
                'departure_time' => $schedule->departure_time,
                'price'          => $schedule->price,
                'actor'          => $request->attributes->get('iae_subject'),
            ]);
            $receipt = $result['receipt'];
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $publisher->publish([
                'event_name'            => 'schedule.created',
                'service_name'          => 'Rute-Jadwal-Service',
                'api_version'           => 'v1',
                'occurred_at'           => now()->toIso8601String(),
                'schedule_id'           => $schedule->id,
                'route'                 => $schedule->route,
                'departure_time'        => $schedule->departure_time,
                'price'                 => $schedule->price,
                'legacy_receipt_number' => $receipt,
                'approved_by'           => [
                    'sso_subject' => $request->attributes->get('iae_subject'),
                    'roles'       => $request->attributes->get('iae_roles', []),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal berhasil ditambahkan',
            'data'    => $schedule,
            'meta'    => [
                'service_name'  => 'Rute-Jadwal-Service',
                'api_version'   => 'v1',
                'audit_receipt' => $receipt,
            ],
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/schedules/{id}',
        summary: 'Mengambil detail informasi jadwal spesifik',
        tags: ['Schedules'],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID Jadwal',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil data'),
            new OA\Response(response: 404, description: 'Resource not found')
        ]
    )]
    public function show($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found',
                'errors' => null
            ], 404); 
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $schedule,
            'meta' => [
                'service_name' => 'Rute-Jadwal-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }
}