<?php

namespace Tonning\Media;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;

class Media extends Model {

	protected $fillable = ['filename', 'extension', 'title', 'alt', 'url', 'path', 'height', 'width', 'size', 'thumbnail'];

	public function scopeNewest($query)
	{
		return $query->orderBy('id', 'decs');
	}

	/**
	 * Add a new media file
	 *
	 * @param $file
	 * @return static
	 * @throws Exception
	 */
	public function add($file)
	{
		if (!file_exists($file))
		{
			App::abort(500, 'File ' . $file . ' does not exist');
		}

		$uploadPath = $this->findOrCreateUploadDirectory();

		$prefix = $this->filenamePrefix();

		$filename = $prefix . $file->getClientOriginalName();

		$image = ($this->isImage($file)) ? Image::make($file) : false;

//		$thumbnailUrl = ($image) ? $this->saveImageSizes($file, $prefix) : '';

		$media = $this->create([
			'filename'  => $filename,
			'extension' => $file->getClientOriginalExtension(),
			'title'     => $this->getBaseFilename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension()),
			'height'	=> ($image) ? $image->height() : '',
			'width'		=> ($image) ? $image->width() : '',
			'size'      => $file->getClientSize(),
			'url'       => $this->getUploadUrl($filename),
			'path'      => $uploadPath,
			'thumbnail' => ($image) ? $this->saveImageSizes($file, $prefix) : ''
		]);

		($image) ? $image->save($uploadPath . '/' . $filename) : null;

		return $media;
	}

	public function deleteFile($file)
	{
		if (File::exists($file))
		{
			File::delete($file);
		}
	}

	public function deleteFiles(Media $file)
	{
		$this->deleteFile($file->path . '/' . $file->filename);

		foreach ($this->getAdditionalImageSizeFiles($file) as $thumbnail)
		{
			$this->deleteFile($file->path . '/' . $thumbnail);
		}
	}

	public function getAdditionalImageSizeFiles(Media $file)
	{
		foreach (config('media.sizes') as $size)
		{
			$images[] = $this->getBaseFilename($file->filename, '.' . $file->extension) . '-' . $size['height'] . 'x' . $size['width'] . '.' . $file->extension;
		}

		return $images;
	}

	public function thumbnail($id)
	{
		$thumbnail = $this->findOrFail($id);

		return $thumbnail->url;
	}


	private function saveImageSizes($file, $prefix = null)
	{
		$sizes = config('media.sizes');

		$uploadPath = $this->findOrCreateUploadDirectory();

		$filename = $prefix . $file->getClientOriginalName();

		$extension = '.' . $file->getClientOriginalExtension();

		$baseFilename = $this->getBaseFilename($filename, $extension);

		foreach ($sizes as $sizeName => $size)
		{
			$newFileSizeName = $baseFilename . '-' . $size['width'] . 'x' . $size['height'] . $extension;

			if ($sizeName == 'thumbnail')
			{
				$thumbnailUrl = $this->getUploadUrl($newFileSizeName);
			}

			Image::make($file)->resize(null, $size['height'], function ($constraint)
			{
				$constraint->aspectRatio();
			})->crop($size['width'], $size['height'])->save($uploadPath . '/' . $newFileSizeName);
		}

		return $thumbnailUrl;
	}

	public function getBaseFilename($filename, $extension)
	{
		return str_replace($extension, '', $filename);
	}

	/**
	 * Create upload directory based on year and month
	 *
	 * @return string
	 */
	private function findOrCreateUploadDirectory()
	{
		$now = Carbon::now();

		$basePath = $this->createMediaLibraryBaseDirectory();

		$uploadPath = $basePath . '/' . $now->year . '/' . $now->month;

		if (!File::exists($uploadPath))
		{
			File::makeDirectory($uploadPath, '493', true, true);
		}

		return $uploadPath;
	}


	/**
	 * Get the full url
	 *
	 * @param $filename
	 * @return string
	 */
	public function getUploadUrl($filename)
	{
		$now = Carbon::now();

		$uploadUri = URL::to('/') . config('media.uri') . '/' . $now->year . '/' . $now->month . '/' . $filename;

		return $uploadUri;
	}

	/**
	 * Create the base medialibrary directory if it does not exist.
	 */
	private function createMediaLibraryBaseDirectory()
	{
		$mediaLibraryBaseDirectory = config('media.path');

		if (!File::exists($mediaLibraryBaseDirectory))
		{
			File::makeDirectory($mediaLibraryBaseDirectory, '493', true, true);
		}

		return $mediaLibraryBaseDirectory;
	}

	/**
	 * Create a unique filename
	 *
	 * @return string
	 */
	public function filenamePrefix()
	{
		$prefix = str_random(6) . '_';

		return $prefix;
	}


	/**
	 * @param Media $media
	 * @param string $size
	 * @return string
	 */
	public function getThumbnail(Media $media, $size = 'thumbnail')
	{
		$height = config('media.sizes.' . $size . '.height');
		$width = config('media.sizes.' . $size . '.width');

		$extension = '.' . $media->extension;

		$baseFilenameWithUrl = str_replace($extension, '', $media->url);

		$thumbnail = $baseFilenameWithUrl . '-' . $height . 'x' . $width . $extension;

		if (File::exists($media->path . '/' . $media->filename))
		{
			return $thumbnail;
		}
	}

	public function image($id)
	{
		$media = $this->find($id);


		if ($media) {
			return '<img src="' . $media->url . '" height="' . $media->height . '" width="' . $media->width . '" alt="' . $media->alt . '">';
		}

		return '<img src="' . $this->placeholder('Image not found', 'ID: ' . $id) . '" height="300" width="300">';
	}

	public function url($id)
	{
		$media = $this->find($id);

		if ($media) {
			return $media->url;
		}

		return $this->placeholder('File not found', 'ID: ' . $id);
	}

	public function placeholder($text = 'Placeholder', $secondaryText = null)
	{
		$filename = ($secondaryText) ? $text . '-' . $secondaryText : $text;
		$file = str_slug($filename) . '.jpg';

		$centerText = ($secondaryText) ?: $text;

		if (! File::exists(public_path('temp/' . $file))) {
			// create Image from file
			$img = Image::canvas(400, 400, '#ccc');

			// Top left
			$img->text($text, 10, 20, function($font) {
				$font->file(4);
				$font->size(46);
				$font->color('#000000');
			});

			// Top right
			$img->text($text, 390, 20, function($font) {
				$font->file(4);
				$font->size(46);
				$font->color('#000000');
				$font->align('right');
			});

			// Center
			$img->text($centerText, 200, 200, function($font) {
				$font->file(4);
				$font->size(46);
				$font->color('#000000');
				$font->align('center');
			});

			// Bottom left
			$img->text($text, 10, 380, function($font) {
				$font->file(4);
				$font->size(46);
				$font->color('#000000');
				$font->align('left');
			});

			// Bottom right
			$img->text($text, 390, 380, function($font) {
				$font->file(4);
				$font->size(46);
				$font->color('#000000');
				$font->align('right');
			});

			$img->save(public_path('temp/' . $file));
		}

		return url('temp/' . $file);
	}

	public function isImage($file)
	{
		if(substr($file->getMimeType(), 0, 5) == 'image') {
			return true;
		}

		return false;
	}
}
