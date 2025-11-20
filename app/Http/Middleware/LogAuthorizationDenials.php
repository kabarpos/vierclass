<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class LogAuthorizationDenials
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (AuthorizationException $e) {
            $this->logDenial($request, $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 403) {
                $this->logDenial($request, $e->getMessage());
            }
            throw $e;
        }
    }

    protected function logDenial(Request $request, string $message): void
    {
        $user = auth()->user();
        $route = optional($request->route());
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];

        Log::warning('Access denied intercepted by middleware', [
            'user_id' => $user->id ?? null,
            'roles' => $roles,
            'route_name' => is_string(optional($route)->getName()) ? $route->getName() : null,
            'route_uri' => optional($route)->uri(),
            'message' => $message,
        ]);
    }
}

