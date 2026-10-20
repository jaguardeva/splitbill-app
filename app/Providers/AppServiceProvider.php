<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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

        Password::defaults(fn (): Password => Password::min(8)
            ->mixedCase()
            ->letters()
            ->rules([
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (preg_match('/\s/u', (string) $value)) {
                        $fail('Kata sandi tidak boleh mengandung spasi.');
                    }

                    if (! preg_match('/[\pN\pS\pP]/u', (string) $value)) {
                        $fail('Kata sandi harus mengandung angka atau simbol.');
                    }
                },
            ]),
        );
    }
}
