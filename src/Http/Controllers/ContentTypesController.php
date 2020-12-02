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
		$validated = $request->validated();
		// Just save the ids
		$validated['allowed_children_types'] = collect($validated['allowed_children_types'])->pluck('id')->toArray();

		$contentType = ContentType::create($validated);

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
		$contentType->allowed_children_types = $contentType->getAllowedChildrenTypes();

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
	 * Retrieves the content type contents
	 *
	 * @param Request $request
	 * @param ContentType $contentType
	 * @return json
	 */
	public function contents(Request $request, ContentType $contentType)
	{
		$s = $request->get('s', 'created_at');

		if($s == 'title') $s .= '->' . app()->getLocale();

		return $contentType->contents()->orderBy($s, $request->get('d', 'desc'))->with('contentType')->paginate();
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

		\Cache::forget('contentType.' . $contentType->id);

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
		$validated = $request->validated();
		// Just save the ids
		$validated['allowed_children_types'] = collect($validated['allowed_children_types'])->pluck('id')->toArray();

		$contentType->update($validated);
		$contentType->allowed_children_types = $contentType->getAllowedChildrenTypes();

		activity()->on($contentType)->log('ContentTypeUpdated');

		return [
			'message' => __('hierarchy::contenttypes.edited'),
			'payload' => $contentType,
			'event' => 'content-tree-modified'
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

		return [
			'message' => __('hierarchy::contenttypes.deleted_multiple'),
			'event' => 'content-tree-modified'
		];
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

		return [
			'message' => __('hierarchy::contenttypes.deleted'),
			'event' => 'content-tree-modified'
		];
	}

	/**
	 * Duplicates the content type
	 *
	 * @param ContentType $contentType
	 * @return json
	 */
	public function duplicate(ContentType $contentType)
	{
		$clone = $contentType->duplicate();

		activity()->on($contentType)->log('ContentTypeDuplicated');

		return [
			'message' => __('hierarchy::contenttypes.duplicated'),
			'payload' => $clone
		];
	}
}