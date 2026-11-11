<?php

namespace App\Jobs;

use App\Models\IvrCallLog;
use App\Services\TwilioService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendIvrSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $toNumber;
    public string $message;
    public ?int $callLogId;
    public string $type;
    public ?string $optionDigit;
    public ?string $fromNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $toNumber,
        string $message,
        ?int $callLogId = null,
        string $type = 'option_sms',
        ?string $optionDigit = null,
        ?string $fromNumber = null
    ) {
        $this->toNumber = $toNumber;
        $this->message = $message;
        $this->callLogId = $callLogId;
        $this->type = $type;
        $this->optionDigit = $optionDigit;
        $this->fromNumber = $fromNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(TwilioService $twilioService): void
    {
        Log::info("Executing background SMS dispatch to {$this->toNumber}", [
            'type' => $this->type,
            'call_log_id' => $this->callLogId,
            'option_digit' => $this->optionDigit,
        ]);

        try {
            $result = $this->fromNumber !== null
                ? $twilioService->sendSms($this->toNumber, $this->message, $this->fromNumber)
                : $twilioService->sendSms($this->toNumber, $this->message);

            if ($this->callLogId) {
                $callLog = IvrCallLog::find($this->callLogId);
                if ($callLog) {
                    if ($result['success']) {
                        $callLog->recordEvent('sms_sent', [
                            'to' => $this->toNumber,
                            'type' => $this->type,
                            'option' => $this->optionDigit,
                            'message_sid' => $result['message_sid'],
                        ]);
                    } else {
                        $callLog->recordEvent('sms_failed', [
                            'to' => $this->toNumber,
                            'type' => $this->type,
                            'option' => $this->optionDigit,
                            'error' => $result['error'],
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to execute background SMS job: {$e->getMessage()}", [
                'to' => $this->toNumber,
                'call_log_id' => $this->callLogId,
            ]);

            if ($this->callLogId) {
                $callLog = IvrCallLog::find($this->callLogId);
                if ($callLog) {
                    $callLog->recordEvent('sms_failed', [
                        'to' => $this->toNumber,
                        'type' => $this->type,
                        'option' => $this->optionDigit,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
