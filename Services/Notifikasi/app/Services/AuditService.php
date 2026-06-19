<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\SsoService;

class AuditService
{
    public static function sendAudit($delay)
    {
        $token = SsoService::getToken();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>' . env('IAE_TEAM') . '</iae:TeamID>
            <iae:ActivityName>DelayCreated</iae:ActivityName>
            <iae:LogContent><![CDATA[
{
    "schedule_code":"' . $delay->schedule_code . '",
    "reason":"' . $delay->reason . '",
    "delay_minutes":' . $delay->delay_minutes . '
}
]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>';

        return Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'text/xml'
            ])
            ->send(
                'POST',
                'https://iae-sso.virtualfri.id/soap/v1/audit',
                [
                    'body' => $xml
                ]
            );
    }
}