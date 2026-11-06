<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResolveClientIp
{
    public function handle(Request $request, Closure $next)
    {
        $xff = $request->header('X-Forwarded-For');

        if ($xff) {
            $ip = trim(explode(',', $xff)[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $request->server->set('REMOTE_ADDR', $ip);
            }
        }

        return $next($request);
    }
}
