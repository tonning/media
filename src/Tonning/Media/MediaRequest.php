<?php 

namespace Tonning\Media;

use App\Http\Requests\Request;

class MediaRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'file' => 'mimes:jpeg,bmp,png|max:3000'
		];
	}

}
