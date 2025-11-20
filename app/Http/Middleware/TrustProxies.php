<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     * Use '*' to trust all proxies (behind CDN/load balancer),
     * or set a comma-separated list via env TRUSTED_PROXIES.
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        $env = env('TRUSTED_PROXIES', '*');
        // Normalize env to array or '*'
        if ($env === '*' || $env === '"*"') {
            $this->proxies = '*';
        } else {
            $list = array_filter(array_map('trim', explode(',', $env)));
            $this->proxies = empty($list) ? null : $list;
        }
    }
}
