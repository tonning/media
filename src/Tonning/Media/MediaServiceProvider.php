<?php 

namespace Tonning\Media;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @param Router $router
     */
	public function boot(Router $router)
	{
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../../../routes.php';
        }

		$this->publishes([
            __DIR__.'/../../config/media.php' => config_path('media.php')
        ]);

        $router->model('media', 'Tonning\Media\Media');
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['media'] = $this->app->share(function ($app) {
            return new Media();
        });

        $this->app->alias('media', 'Tonning\Media\Media');
	}

}
