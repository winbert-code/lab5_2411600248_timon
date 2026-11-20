<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MonitorActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldRecordView($request)) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? '';
        $module = $this->moduleFor($routeName);
        $subject = $this->subjectFor($request);
        [$action, $details] = $this->queryDetails($request);

        if ($module === ActivityLog::MODULE_SYSTEM) {
            $action = ActivityLog::ACTION_REDIRECTED;
        }

        ActivityLog::record(
            $module,
            $action,
            $this->viewDescription($request, $module, $routeName, $subject).$details,
            $subject,
        );

        return $response;
    }

    private function shouldRecordView(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->expectsJson() || ! Auth::check()) {
            return false;
        }

        $path = $request->path();

        if (in_array($path, ['/', 'up', 'favicon.ico'], true)) {
            return false;
        }

        return ! Str::startsWith($path, ['css/', 'js/', 'vendor/', 'storage/', 'build/', '_']);
    }

    private function moduleFor(string $routeName): string
    {
        return match (true) {
            $routeName === 'dashboard' => ActivityLog::MODULE_DASHBOARD,
            $routeName === 'inventory.index' => ActivityLog::MODULE_INVENTORY,
            $routeName === 'activity-logs.index' => ActivityLog::MODULE_ACTIVITY,
            in_array($routeName, ['login', 'login.store', 'logout', 'register', 'register.store'], true) => ActivityLog::MODULE_AUTH,
            str_starts_with($routeName, 'products.') => ActivityLog::MODULE_PRODUCT,
            str_starts_with($routeName, 'categories.') => ActivityLog::MODULE_CATEGORY,
            str_starts_with($routeName, 'brands.') => ActivityLog::MODULE_BRAND,
            str_starts_with($routeName, 'users.') => ActivityLog::MODULE_USER,
            default => ActivityLog::MODULE_SYSTEM,
        };
    }

    private function subjectFor(Request $request): ?Model
    {
        foreach (['product', 'category', 'brand', 'user'] as $key) {
            $value = $request->route($key);

            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    private function queryDetails(Request $request): array
    {
        $query = $request->query();
        unset($query['page']);

        $search = trim((string) ($query['search'] ?? ''));
        $action = ActivityLog::ACTION_VIEWED;
        $details = '';

        if ($search !== '') {
            unset($query['search']);
            $action = ActivityLog::ACTION_SEARCHED;
            $details = ' Search term: "'.Str::limit($search, 80).'".';
        }

        if ($query !== []) {
            if ($action === ActivityLog::ACTION_VIEWED) {
                $action = ActivityLog::ACTION_FILTERED;
            }

            $details .= ' Filters: '.Str::limit(http_build_query($query), 120).'.';
        }

        return [$action, $details];
    }

    private function viewDescription(Request $request, string $module, string $routeName, ?Model $subject): string
    {
        if ($module === ActivityLog::MODULE_SYSTEM) {
            return 'Opened an unrecognised URL ('.Str::limit($request->path(), 120).') and was redirected to the dashboard.';
        }

        $label = $this->labelFor($module);
        $suffix = Str::afterLast($routeName, '.');
        $id = $subject?->getKey();

        return match ($routeName) {
            'dashboard' => 'Opened the dashboard.',
            'inventory.index' => 'Opened the inventory overview.',
            'activity-logs.index' => 'Opened the activity log.',
            default => match ($suffix) {
                'index' => "Opened the {$label} list.",
                'create' => "Opened the form to add a new {$label}.",
                'edit' => "Opened the form to edit {$label}".($id !== null ? " #{$id}" : '').'.',
                'show' => "Viewed {$label}".($id !== null ? " #{$id}" : '').'.',
                default => "Opened {$label}.",
            },
        };
    }

    private function labelFor(string $module): string
    {
        return match ($module) {
            ActivityLog::MODULE_DASHBOARD => 'dashboard',
            ActivityLog::MODULE_INVENTORY => 'inventory',
            ActivityLog::MODULE_PRODUCT => 'Product',
            ActivityLog::MODULE_CATEGORY => 'Category',
            ActivityLog::MODULE_BRAND => 'Brand',
            ActivityLog::MODULE_USER => 'User',
            ActivityLog::MODULE_ACTIVITY => 'Activity Log',
            ActivityLog::MODULE_AUTH => 'account',
            default => 'page',
        };
    }
}
