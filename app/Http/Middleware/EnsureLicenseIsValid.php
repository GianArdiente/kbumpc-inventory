<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseIsValid
{
    public function __construct(
        private LicenseService $licenseService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $this->licenseService->isValid()) {
            abort(
                403,
                'This installation does not have a valid software license.'
            );
        }

        return $next($request);
    }
}