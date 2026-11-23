<?php

use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Native\Mobile\UI\Theme;

uses(RefreshDatabase::class);

it('persists local preferences and applies an explicit theme palette', function () {
    $service = app(SettingsService::class);
    $service->save([
        'default_priority' => 'high',
        'theme_preference' => 'dark',
    ]);

    expect($service->get('default_priority'))->toBe('high')
        ->and($service->get('theme_preference'))->toBe('dark')
        ->and(Theme::get('light.background'))->toBe(config('native-ui.theme.dark.background'))
        ->and(Theme::get('dark.background'))->toBe(config('native-ui.theme.dark.background'));
});

it('rejects unknown local preference values', function () {
    expect(fn () => app(SettingsService::class)->save([
        'default_priority' => 'urgent',
        'theme_preference' => 'neon',
    ]))->toThrow(ValidationException::class);
});
