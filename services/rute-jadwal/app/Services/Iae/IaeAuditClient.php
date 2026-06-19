<?php

namespace App\Services\Iae;

use Illuminate\Support\Facades\Http;
use App\Models\AuditLog;

class IaeAuditClient
{
    public function __construct(private IaeTokenService $tokens) {}

    public function audit(string $activityName, array $logContent): array
    {
        $base   = rtrim(config('services.iae.sso_url'), '/');
        $teamId = config('services.iae.team_id');
        $token  = $this->tokens->m2mToken();

        $json = json_encode($logContent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $xml = <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
                <soap:Body>
                    <iae:AuditRequest>
                    <iae:TeamID>{$teamId}</iae:TeamID>
                    <iae:ActivityName>{$activityName}</iae:ActivityName>
                    <iae:LogContent><![CDATA[{$json}]]></iae:LogContent>
                    </iae:AuditRequest>
                </soap:Body>
                </soap:Envelope>
                XML;

        $response = Http::withToken($token)
            ->withBody($xml, 'text/xml; charset=utf-8')
            ->timeout(15)
            ->post($base.'/soap/v1/audit');

        $body = $response->body();

        $receipt = null;
        if (preg_match('/<(?:\w+:)?ReceiptNumber>(.*?)<\/(?:\w+:)?ReceiptNumber>/s', $body, $m)) {
            $receipt = trim($m[1]);
        }

        AuditLog::create([
            'activity_name'  => $activityName,
            'team_id'        => $teamId,
            'payload'        => $logContent,
            'receipt_number' => $receipt,
        ]);

        return ['receipt' => $receipt, 'status' => $response->status(), 'raw' => $body];
    }
}