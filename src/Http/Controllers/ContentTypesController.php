<?php

namespace Nuclear\Hierarchy\Http\Controllers;

use Umomega\Foundation\Http\Controllers\Controller;
use Nuclear\Hierarchy\ContentType;
use Nuclear\Hierarchy\ContentField;
use Nuclear\Hierarchy\Http\Requests\StoreContentType;
use Nuclear\Hierarchy\Http\Requests\UpdateContentType;
use Illuminate\Http\Request;
use Spatie\Searchable\Search;

class ContentTypesController extends Controller
{

	/**
	 * Returns a list of content types
	 *
	 * @param Request $request
	 * @return json
	 */
	public function index(Request $request)
	{
		return ContentType::orderBy($request->get('s', 'name'), $request->get('d', 'asc'))->paginate(15);
	}

	/**
	 * Returns a list of content types filtered by search
	 *
	 * @param Request $request
	 * @return json
	 */
	public function search(Request $request)
	{
		return ['data' => (new Search())
			->registerModel(ContentType::class, 'name')
			->search($request->get('q'))
			->map(function($contentType) {
				return $contentType->searchable;
			})];
	}

	/**
	 * Stores the new content type
	 *
	 * @param StoreContentType $request
	 * @return json
	 */
	public function store(StoreContentType $request)
	{
		$contentType = ContentType::create($request->validated());

		activity()->on($contentType)->log('ContentTypeStored');

		return [
			'message' => __('hierarchy::contenttypes.created'),
			'payload' => $contentType
		];
	}

	/**
	 * Retrieves the content type information
	 *
	 * @param ContentType $contentType
	 * @return json
	 */
	public function show(ContentType $contentType)
	{
		return $contentType;
	}

	/**
	 * Retrieves the content type fields
	 *
	 * @param ContentType $contentType
	 * @return json
	 */
	public function fields(ContentType $contentType)
	{
		return $contentType->fields()->orderBy('position')->get();
	}

	/**
	 * Sorts the content type fields
	 *
	 * @param Request $request
	 * @param ContentType $contentType
	 * @return json
	 */
	public function sort(Request $request, ContentType $contentType)
	{
		$sorted = $request->get('sorted');

		$i = 1;

		foreach($sorted as $id)
		{
			ContentField::where('id', $id)->update(['position' => $i]);
			$i++;
		}

		return;
	}

	/**
	 * Updates the content type
	 *
	 * @param UpdateContentType $request
	 * @param ContentType $contentType
	 * @return json
	 */
	public function update(UpdateContentType $request, ContentType $contentType)
	{
		$contentType->update($request->validated());

		activity()->on($contentType)->log('ContentTypeUpdated');

		return [
			'message' => __('hierarchy::contenttypes.edited'),
			'payload' => $contentType
		];
	}

	/**
	 * Bulk deletes content types
	 *
	 * @param Request $request
	 * @return json
	 */
	public function destroyBulk(Request $request)
	{
		$items = $this->validate($request, ['items' => 'required|array'])['items'];
		
		$names = ContentType::whereIn('id', $items)->pluck('name')->toArray();
		
		ContentType::whereIn('id', $items)->delete();

		activity()->withProperties(compact('names'))->log('ContentTypesDestroyedBulk');

		return ['message' => __('hierarchy::contenttypes.deleted_multiple')];
	}

	/**
	 * Deletes a content type
	 *
	 * @param ContentType $contentType
	 * @return json
	 */
	public function destroy(ContentType $contentType)
	{
		$name = $contentType->name;

		$contentType->delete();

		activity()->withProperties(compact('name'))->log('ContentTypeDestroyed');

		return ['message' => __('hierarchy::contenttypes.deleted')];
	}

}