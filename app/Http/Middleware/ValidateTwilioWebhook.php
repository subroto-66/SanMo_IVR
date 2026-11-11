<?php

namespace App\Http\Middleware;

use App\Services\TwilioService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTwilioWebhook
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if webhook validation is enabled and enforce it
        if (config('twilio.webhook_validation', false)) {
            if (!$this->twilioService->validateRequest($request)) {
                return response('Invalid Twilio Signature', Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
