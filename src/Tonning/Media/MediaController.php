<?php 

namespace Tonning\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		if ($request->ajax()) {
			return Media::latest()->get();
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param MediaRequest $request
	 * @param Media $repository
	 * @return Response
	 */
	public function store(MediaRequest $request, Media $media)
	{
		$file = $request->file('file');

		$file = $media->add($file);

		if ($request->ajax())
		{
			return [
				'success' => true,
				'id'      => $file->id,
				'url'     => $file->url
			];
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param Media $media
	 * @return Response
	 */
	public function update(Request $request, Media $media)
	{
		$media->$request->field = $request->value;
		$media->save();

		return ['success' => true];
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Request $request
	 * @param Media $media
	 * @return Response
	 */
	public function destroy(Request $request, Media $media)
	{
		$media->delete($media);
		$media->deleteFiles($media);

		if ($request->ajax())
		{
			return ['success' => true];
		}

		return redirect()->back();

	}
}
