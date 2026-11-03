<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * This ensures kiosks, biometric attendance terminals, and mobile devices
     * do not fail due to session/token timeouts.
     *
     * @var array<int, string>
     */
    protected $except = [
        'attendance/face-scan',
        'employee/enroll-face',
        'api/*',
    ];
}
