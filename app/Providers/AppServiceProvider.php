<?php

namespace App\Providers;

use App\Authorization\Ability;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAppNameFromSettings();
        $this->configureAuthorization();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Prefer the database app name for config('app.name') everywhere.
     */
    protected function configureAppNameFromSettings(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $settings = SystemSetting::query()->first();

        if ($settings === null) {
            return;
        }

        Config::set('app.name', $settings->app_name);
    }

    /**
     * Register role-based abilities.
     */
    protected function configureAuthorization(): void
    {
        foreach (Ability::cases() as $ability) {
            Gate::define($ability->value, function (User $user) use ($ability): bool {
                return $user->hasAbility($ability);
            });
        }
    }
}
