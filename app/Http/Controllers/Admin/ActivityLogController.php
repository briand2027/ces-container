<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use SplFileObject;

class ActivityLogController extends Controller
{
    private const MAX_ENTRIES = 5000;
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        abort_unless(in_array($type, ['all', 'activity', 'incidents'], true), 404);

        $entries = $this->recentEntries($type);
        $page = max(1, LengthAwarePaginator::resolveCurrentPage());
        $items = array_slice($entries, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
        $logs = new LengthAwarePaginator($items, count($entries), self::PER_PAGE, $page, [
            'path' => route('admin.activity.index'),
            'query' => $request->query(),
        ]);

        return view('admin.activity.index', [
            'logs' => $logs,
            'type' => $type,
            'incidentCount' => count(array_filter($entries, fn (array $entry) => $entry['type'] === 'incidents')),
            'activityCount' => count(array_filter($entries, fn (array $entry) => $entry['type'] === 'activity')),
        ]);
    }

    private function recentEntries(string $type): array
    {
        $cutoff = now()->subDays(14)->format('Y-m-d');
        $paths = glob(storage_path('logs/*.log')) ?: [];
        $entries = [];

        foreach ($paths as $path) {
            $name = basename($path);
            if (! preg_match('/^(activity|incidents)-(\d{4}-\d{2}-\d{2})\.log$/', $name, $matches)) {
                continue;
            }

            if ($matches[2] < $cutoff || ($type !== 'all' && $matches[1] !== $type)) {
                continue;
            }

            $file = new SplFileObject($path, 'r');
            foreach ($file as $line) {
                $entry = $this->parseLine(trim((string) $line), $matches[1], $name);
                if ($entry !== null) {
                    $entries[] = $entry;
                }
            }
        }

        usort($entries, fn (array $a, array $b) => strcmp($b['timestamp'], $a['timestamp']));

        return array_slice($entries, 0, self::MAX_ENTRIES);
    }

    private function parseLine(string $line, string $type, string $file): ?array
    {
        if (! preg_match('/^\[([^\]]+)\]\s+\S+\.(\w+):\s*(.*)$/', $line, $matches)) {
            return null;
        }

        $rest = $matches[3];
        $jsonStart = strpos($rest, ' {');
        $message = $jsonStart === false ? $rest : substr($rest, 0, $jsonStart);
        $context = $jsonStart === false
            ? []
            : (json_decode(substr($rest, $jsonStart + 1), true) ?: []);

        return [
            'timestamp' => $matches[1],
            'level' => strtoupper($matches[2]),
            'type' => $type,
            'message' => $message,
            'method' => $context['method'] ?? null,
            'path' => $context['path'] ?? null,
            'route' => $context['route'] ?? null,
            'action' => $context['action'] ?? null,
            'status' => $context['status'] ?? null,
            'cause' => $context['cause'] ?? null,
            'exception' => $context['exception'] ?? null,
            'source_file' => $context['source_file'] ?? null,
            'source_line' => $context['source_line'] ?? null,
            'admin_id' => $context['admin_id'] ?? null,
            'operation' => $context['operation'] ?? null,
            'message_id' => $context['message_id'] ?? null,
            'order_id' => $context['order_id'] ?? null,
            'file' => $file,
        ];
    }
}
