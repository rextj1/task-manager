<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Native\Mobile\UI\Theme;

class SettingsService
{
    private static ?array $baseTheme = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $value = DB::table('app_settings')->where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return match ($key) {
            'notifications_enabled' => $value === '1',
            default => $value,
        };
    }

    public function notificationsEnabled(): bool
    {
        return (bool) $this->get('notifications_enabled', true);
    }

    public function save(array $settings): void
    {
        $priority = $settings['default_priority'] ?? 'medium';
        $theme = $settings['theme_preference'] ?? 'system';
        $notifications = ($settings['notifications_enabled'] ?? true) ? '1' : '0';

        if (! in_array($priority, ['low', 'medium', 'high'], true)) {
            throw ValidationException::withMessages(['default_priority' => 'Choose a valid default priority.']);
        }
        if (! in_array($theme, ['system', 'light', 'dark'], true)) {
            throw ValidationException::withMessages(['theme_preference' => 'Choose system, light or dark appearance.']);
        }

        $now = now();
        foreach ([
            'default_priority' => $priority,
            'theme_preference' => $theme,
            'notifications_enabled' => $notifications,
        ] as $key => $value) {
            DB::table('app_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => $now, 'created_at' => $now]);
        }

        $this->applyTheme($theme);
        app(TaskReminderService::class)->synchronizeAll();
    }

    public function applyTheme(?string $preference = null): void
    {
        $preference ??= $this->get('theme_preference', 'system');
        self::$baseTheme ??= config('native-ui.theme', []);
        $tokens = self::$baseTheme;
        if ($preference === 'system') {
            Theme::load($tokens);

            return;
        }

        $selected = $tokens[$preference] ?? $tokens['light'] ?? [];
        $tokens['light'] = $selected;
        $tokens['dark'] = $selected;
        Theme::load($tokens);
    }
}
