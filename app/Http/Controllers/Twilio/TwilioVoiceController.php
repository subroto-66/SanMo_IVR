<?php

namespace App\Http\Controllers\Twilio;

use App\Http\Controllers\Controller;
use App\Models\IvrCallLog;
use App\Services\IvrService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TwilioVoiceController extends Controller
{
    protected IvrService $ivrService;

    public function __construct(IvrService $ivrService)
    {
        $this->ivrService = $ivrService;
    }

    /**
     * Inbound Call Webhook (Twilio Voice Entry Point).
     * Route: POST /twilio/voice
     */
    public function voice(Request $request): Response
    {
        $callSid = $request->input('CallSid', 'UNKNOWN-' . time());
        $from = $request->input('From');
        $to = $request->input('To');
        $direction = $request->input('Direction', 'inbound');
        $callStatus = $request->input('CallStatus', 'in-progress');

        Log::info('Twilio Inbound Call Received', [
            'call_sid' => $callSid,
            'direction' => $direction,
            'status' => $callStatus,
        ]);

        try {
            $callLog = IvrCallLog::firstOrCreate(
                ['call_sid' => $callSid],
                [
                    'from_number' => $from,
                    'to_number' => $to,
                    'direction' => $direction,
                    'status' => $callStatus,
                    'started_at' => Carbon::now(),
                ]
            );

            $response = $this->ivrService->buildInitialResponse($callLog);

            return response((string) $response, 200, [
                'Content-Type' => 'text/xml; charset=utf-8',
            ]);
        } catch (Exception $e) {
            Log::error('Error processing Twilio voice webhook', [
                'call_sid' => $callSid,
                'error' => $e->getMessage(),
            ]);

            // Graceful emergency TwiML fallback
            $fallback = new \Twilio\TwiML\VoiceResponse();
            $fallback->say('An unexpected error occurred. Please try your call again later.', ['voice' => 'Polly.Joanna']);
            $fallback->hangup();

            return response((string) $fallback, 200, [
                'Content-Type' => 'text/xml; charset=utf-8',
            ]);
        }
    }

    /**
     * Menu & DTMF Input Callback Webhook.
     * Route: POST /twilio/menu
     */
    public function menu(Request $request): Response
    {
        $callSid = $request->input('CallSid');
        $from = $request->input('From');
        $digits = $request->input('Digits');
        $retry = (int) $request->input('retry', $request->query('retry', 0));
        $isTimeout = (bool) $request->query('timeout', false);
        $action = $request->input('action', $request->query('action'));

        Log::info('Twilio DTMF Menu Callback', [
            'call_sid' => $callSid,
            'digits' => $digits,
            'retry' => $retry,
            'timeout' => $isTimeout,
            'action' => $action,
        ]);

        try {
            $callLog = IvrCallLog::firstOrCreate(
                ['call_sid' => $callSid],
                [
                    'from_number' => $from,
                    'direction' => 'inbound',
                    'status' => 'in-progress',
                    'started_at' => Carbon::now(),
                ]
            );

            // If action is explicitly 'menu' (e.g. redirected from post-option timeout)
            if ($action === 'menu') {
                $response = $this->ivrService->buildMenuResponse($callLog, 0);
            } elseif ($isTimeout || $digits === null || $digits === '') {
                $response = $this->ivrService->handleTimeout($callLog, $retry);
            } else {
                $response = $this->ivrService->processInput((string) $digits, $from, $callLog, $retry);
            }

            return response((string) $response, 200, [
                'Content-Type' => 'text/xml; charset=utf-8',
            ]);
        } catch (Exception $e) {
            Log::error('Error handling Twilio menu input', [
                'call_sid' => $callSid,
                'error' => $e->getMessage(),
            ]);

            $fallback = new \Twilio\TwiML\VoiceResponse();
            $fallback->say('We encountered an error processing your selection. Goodbye.', ['voice' => 'Polly.Joanna']);
            $fallback->hangup();

            return response((string) $fallback, 200, [
                'Content-Type' => 'text/xml; charset=utf-8',
            ]);
        }
    }

    /**
     * Call Status Callback Webhook.
     * Route: POST /twilio/status
     */
    public function status(Request $request): Response
    {
        $callSid = $request->input('CallSid');
        $callStatus = $request->input('CallStatus');
        $callDuration = $request->input('CallDuration');

        Log::info('Twilio Call Status Update', [
            'call_sid' => $callSid,
            'status' => $callStatus,
            'duration' => $callDuration,
        ]);

        if (!empty($callSid)) {
            $callLog = IvrCallLog::where('call_sid', $callSid)->first();
            if ($callLog) {
                $updateData = [
                    'status' => $callStatus ?: $callLog->status,
                ];

                if (!empty($callDuration)) {
                    $updateData['duration'] = (int) $callDuration;
                }

                if (in_array($callStatus, ['completed', 'failed', 'busy', 'no-answer', 'canceled'])) {
                    $updateData['ended_at'] = Carbon::now();
                }

                $callLog->update($updateData);
                $callLog->recordEvent('call_status_update', [
                    'status' => $callStatus,
                    'duration' => $callDuration,
                ]);
            }
        }

        return response('OK', 200);
    }

    /**
     * SMS Status Callback Webhook.
     * Route: POST /twilio/sms-status
     */
    public function smsStatus(Request $request): Response
    {
        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $to = $request->input('To');

        Log::info('Twilio SMS Status Callback', [
            'message_sid' => $messageSid,
            'status' => $messageStatus,
        ]);

        return response('OK', 200);
    }
}
