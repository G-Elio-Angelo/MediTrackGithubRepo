<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * Create a new "XSRF-TOKEN" cookie that contains the CSRF token
     * and send it as a raw cookie (no URL-encoding) so JS can read it
     * without percent-decoding issues.
     *
     * @param Request $request
     * @param array $config
     * @return Cookie
     */
    protected function newCookie($request, $config)
    {
        return new Cookie(
            'XSRF-TOKEN',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false, // httpOnly = false so JavaScript can read it
            true,  // raw = true -> do not URL-encode the value
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }
    protected $except = [
        //
    ];
}
