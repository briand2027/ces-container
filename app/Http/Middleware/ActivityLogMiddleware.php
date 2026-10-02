<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Support\IncidentLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            if (! $exception instanceof ValidationException) {
                $status = $exception instanceof HttpExceptionInterface
                    ? $exception->getStatusCode()
                    : 500;

                IncidentLogger::exception($exception, 'HTTP request failed', ['status' => $status]);
            }

            throw $exception;
        }

        if (! $request->isMethodSafe() || $response->getStatusCode() >= 400) {
            Log::channel('activity')->info('HTTP action completed', [
                ...$this->requestContext($request),
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    private function requestContext(Request $request): array
    {
        $route = $request->route();

        return [
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => $route?->getName() ?? 'unmatched',
            'action' => $route?->getActionName() ?? 'unmatched route',
            'admin_id' => $request->hasSession()
                ? $request->session()->get('admin_id')
                : null,
        ];
    }
}
