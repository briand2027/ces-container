<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class IncidentLogger
{
    public static function exception(Throwable $exception, string $message = 'Application request failed', array $context = []): void
    {
        $request = request();
        $route = $request->route();
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        Log::channel('incidents')->error($message, array_merge([
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => $route?->getName() ?? 'unmatched',
            'action' => $route?->getActionName() ?? 'unmatched route',
            'admin_id' => $request->hasSession() ? $request->session()->get('admin_id') : null,
            'status' => $status,
            'exception' => $exception::class,
            'cause' => self::safeCause($exception),
            'source_file' => basename($exception->getFile()),
            'source_line' => $exception->getLine(),
        ], $context));
    }

    public static function warning(string $message, array $context = []): void
    {
        $request = request();
        $route = $request->route();

        Log::channel('incidents')->warning($message, array_merge([
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => $route?->getName() ?? 'unmatched',
            'action' => $route?->getActionName() ?? 'unmatched route',
            'admin_id' => $request->hasSession() ? $request->session()->get('admin_id') : null,
        ], $context));
    }

    private static function safeCause(Throwable $exception): string
    {
        $cause = $exception instanceof QueryException && $exception->getPrevious()
            ? $exception->getPrevious()->getMessage()
            : $exception->getMessage();

        $cause = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[email]', $cause) ?? $cause;
        $cause = preg_replace('/\b(password|passwd|token|secret|authorization)\b\s*[:=]\s*\S+/i', '$1=[redacted]', $cause) ?? $cause;

        return Str::limit($cause, 400, '...');
    }
}
