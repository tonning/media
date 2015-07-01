# Media

## Installation

First, pull in the package through Composer.

```js
"require": {
    "tonning/media": "~0.1"
}
```

And then, if using Laravel 5.1, include the service provider within `app/config/app.php`.

```php
'providers' => [
    Tonning\Media\MediaServiceProvider::class,
    Intervention\Image\ImageServiceProvider::class,
];
```

And, for convenience, add a facade alias to this same file at the bottom:

```php
'aliases' => [
    'Media'     => Tonning\Media\MediaFacade::class,
    'Image'     => Intervention\Image\Facades\Image::class
];
```