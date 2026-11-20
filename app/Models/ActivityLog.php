<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    public const MODULE_AUTH = 'Auth';

    public const MODULE_DASHBOARD = 'Dashboard';

    public const MODULE_INVENTORY = 'Inventory';

    public const MODULE_PRODUCT = 'Product';

    public const MODULE_CATEGORY = 'Category';

    public const MODULE_BRAND = 'Brand';

    public const MODULE_USER = 'User';

    public const MODULE_ACTIVITY = 'Activity Log';

    public const MODULE_SYSTEM = 'System';

    public const ACTION_LOGGED_IN = 'Logged in';

    public const ACTION_FAILED_LOGIN = 'Failed login';

    public const ACTION_INACTIVE_LOGIN = 'Inactive login';

    public const ACTION_LOGGED_OUT = 'Logged out';

    public const ACTION_REGISTERED = 'Registered';

    public const ACTION_CREATED = 'Created';

    public const ACTION_UPDATED = 'Updated';

    public const ACTION_DEACTIVATED = 'Deactivated';

    public const ACTION_RESTORED = 'Restored';

    public const ACTION_VIEWED = 'Viewed';

    public const ACTION_SEARCHED = 'Searched';

    public const ACTION_FILTERED = 'Filtered';

    public const ACTION_STOCK_ADJUSTED = 'Stock adjusted';

    public const ACTION_BLOCKED = 'Blocked';

    public const ACTION_REDIRECTED = 'Redirected';

    public const MODULES = [
        self::MODULE_AUTH,
        self::MODULE_DASHBOARD,
        self::MODULE_INVENTORY,
        self::MODULE_PRODUCT,
        self::MODULE_CATEGORY,
        self::MODULE_BRAND,
        self::MODULE_USER,
        self::MODULE_ACTIVITY,
        self::MODULE_SYSTEM,
    ];

    public const ACTIONS = [
        self::ACTION_LOGGED_IN,
        self::ACTION_FAILED_LOGIN,
        self::ACTION_INACTIVE_LOGIN,
        self::ACTION_LOGGED_OUT,
        self::ACTION_REGISTERED,
        self::ACTION_CREATED,
        self::ACTION_UPDATED,
        self::ACTION_DEACTIVATED,
        self::ACTION_RESTORED,
        self::ACTION_VIEWED,
        self::ACTION_SEARCHED,
        self::ACTION_FILTERED,
        self::ACTION_STOCK_ADJUSTED,
        self::ACTION_BLOCKED,
        self::ACTION_REDIRECTED,
    ];

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<ActivityLog>  $query
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public static function record(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?User $user = null,
    ): self {
        $actor = $user ?? auth()->user();

        return static::create([
            'user_id' => $actor?->id,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    public function subjectUrl(): ?string
    {
        if (! $this->subject_id) {
            return null;
        }

        return match ($this->module) {
            self::MODULE_PRODUCT => route('products.show', $this->subject_id),
            self::MODULE_CATEGORY => route('categories.edit', $this->subject_id),
            self::MODULE_BRAND => route('brands.edit', $this->subject_id),
            self::MODULE_USER => route('users.edit', $this->subject_id),
            default => null,
        };
    }

    public function badgeClass(): string
    {
        return match ($this->action) {
            self::ACTION_DEACTIVATED, self::ACTION_LOGGED_OUT, self::ACTION_BLOCKED => 'badge-inactive',
            self::ACTION_FAILED_LOGIN, self::ACTION_INACTIVE_LOGIN => 'badge-out',
            self::ACTION_UPDATED, self::ACTION_VIEWED, self::ACTION_SEARCHED, self::ACTION_FILTERED, self::ACTION_REDIRECTED => 'bg-secondary',
            default => 'bg-success',
        };
    }
}
