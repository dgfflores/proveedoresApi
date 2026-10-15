<?php

namespace App\Services;

use App\Contracts\CaptchaVerifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class GoogleRecaptchaVerifier implements CaptchaVerifier
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(
        private readonly string $secret,
        private readonly float $minScore,
    ) {}

    public function verify(string $token, string $action, ?string $ip = null): bool
    {
        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret'   => $this->secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (ConnectionException $e) {
            report($e);
            return false; // fail-closed
        }

        if (! $response->successful()) {
            return false;
        }

        return $response->json('success') === true
            && $response->json('action') === $action
            && (float) $response->json('score', 0) >= $this->minScore;
    }
}