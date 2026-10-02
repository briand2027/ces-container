<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

                Log::channel('incidents')->error('HTTP request failed', [
                    ...$this->requestContext($request),
                    'status' => $status,
                    'exception' => $exception::class,
                    'cause' => $this->safeCause($exception),
                    'source_file' => basename($exception->getFile()),
                    'source_line' => $exception->getLine(),
                ]);
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

    private function safeCause(Throwable $exception): string
    {
        $cause = $exception instanceof QueryException && $exception->getPrevious()
            ? $exception->getPrevious()->getMessage()
            : $exception->getMessage();

        $cause = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[email]', $cause) ?? $cause;
        $cause = preg_replace('/\b(password|passwd|token|secret|authorization)\b\s*[:=]\s*\S+/i', '$1=[redacted]', $cause) ?? $cause;

        return Str::limit($cause, 400, '...');
    }
}
