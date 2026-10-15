<?php

namespace App\Contracts;

interface CaptchaVerifier
{
    public function verify(string $token, string $action, ?string $ip = null): bool;
}