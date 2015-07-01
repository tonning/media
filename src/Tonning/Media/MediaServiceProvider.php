<?php 

namespace Tonning\Media;

use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
            __DIR__.'/../../config/media.php' => config_path('media.php')
        ]);
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
		$this->app->bindShared('media', function () {
            return $this->app->make('Tonning\Media\Media');
        });

        $app->alias('media', 'Tonning\Media\Media');

	}

}