<?php

namespace GGPHP\User;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{

    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;
    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * The namespace implementation.
     */
    protected static $namespace = '\GGPHP\User\Http\Controllers';

    /**
     * Register routes for bread.
     *
     * @return void
     */
    public function all()
    {
        $this->forGuest();
        $this->forUser();
        $this->forAuth();
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forGuest()
    {
        $this->router->group(['middleware' => []], function ($router) {
            \Route::post('login', 'AuthController@login')->name('login');
            \Route::post('password/forgot/request', 'ForgotPasswordController@getResetToken');
            \Route::post('password/forgot/reset', 'ResetPasswordController@reset');
            \Route::post('sign-up', 'AuthController@signUp');
        });
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forUser()
    {
        $this->router->group(['middleware' => []], function ($router) {
            \Route::resource('users', 'UserController')->only('index', 'store', 'show', 'update', 'destroy');

        });
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forAuth()
    {

        $this->router->group(['middleware' => []], function ($router) {
            \Route::get('me', 'AuthController@authenticated');
            \Route::post('logout', 'AuthController@logout');
            \Route::post('password/change', 'AuthController@changePassword');

        });
    }

    /**
     * Binds the routes into the controller.
     *
     * @param  callable|null  $callback
     * @param  array  $options
     * @return void
     */
    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'namespace' => static::$namespace,
        ];

        $options = array_merge($defaultOptions, $options);

        \Route::group($options, function ($router) use ($callback) {
            $callback(new static($router));
        });
    }
}
