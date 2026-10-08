<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Minimal gate for an internal tool: HTTP Basic auth using values from .env.
 * Refuses to run at all if credentials are not configured.
 */
class ToolBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = (string) config('emailsender.username');
        $pass = (string) config('emailsender.password');

        abort_if($user === '' || $pass === '', 503, 'Set TOOL_USERNAME and TOOL_PASSWORD in .env');

        if (! hash_equals($user, (string) $request->getUser())
            || ! hash_equals($pass, (string) $request->getPassword())) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="Email Sender"']);
        }

        return $next($request);
    }
}
