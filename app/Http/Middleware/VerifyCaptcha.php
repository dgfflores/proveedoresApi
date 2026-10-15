<?php

namespace App\Http\Middleware;

use App\Contracts\CaptchaVerifier;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyCaptcha
{
    public function __construct(private readonly CaptchaVerifier $verifier) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        $token = (string) $request->header('X-Captcha-Token');

        if ($token === '' || ! $this->verifier->verify($token, $action, $request->ip())) {
            return ApiResponse::error('No se pudo validar la verificación de seguridad. Intenta de nuevo.', 403);
        }

        return $next($request);
    }
}