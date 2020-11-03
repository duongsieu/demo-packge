<?php

namespace GGPHP\User\Providers;

use Config;
use GGPHP\User\Models\User;
use GGPHP\User\Repositories\Contracts\UserRepository;
use GGPHP\User\Repositories\Eloquent\UserRepositoryEloquent;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as AuthServiceProvider;
use Illuminate\Support\Collection;
use Laravel\Passport\Passport;

class UserServiceProvider extends AuthServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem)
    {
        // Custom exception
        $this->app->bind(
            Handler::class,
            UserException::class
        );

        if (function_exists('config_path')) { // function not available and 'publish' not relevant in Lumen
            $this->publishes([
                __DIR__ . '/../config/user.php' => config_path('user.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../../database/migrations/update_or_create_users_table.php.stub' => $this->getMigrationFileName($filesystem),
            ], 'migrations');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../config/user_constant.php', 'constants'
        );

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'view-users');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'lang-user');

        // set auth config system
        $setDriver = Config::set('auth.guards.api.driver', 'passport');
        $setProviders = Config::set('auth.providers.users.model', User::class);
        $setConfigRepository = Config::set('repository.fractal.serializer', 'League\Fractal\Serializer\JsonApiSerializer');

        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(config('constants.TOKEN.REFRESH_TOKEN_EXPIRE_IN')));
        Passport::refreshTokensExpireIn(now()->addDays(config('constants.TOKEN.REFRESH_TOKEN_EXPIRE_IN')));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_update_or_create_users_table.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_update_or_create_users_table.php")
            ->first();
    }
}
