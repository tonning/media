<?php 

namespace Tonning\Media;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tonning\Media\Media
 */
class MediaFacade extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'media'; }
}