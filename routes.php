<?php
Route::group([
    'prefix'       => 'admin',
    'namespace'    => 'Tonning\Media'
], function ()
{
    Route::resource('media', 'MediaController');
});
