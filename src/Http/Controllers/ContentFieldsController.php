<?php

namespace Nuclear\Hierarchy\Http\Controllers;

use Cache;
use Umomega\Foundation\Http\Controllers\Controller;
use Nuclear\Hierarchy\ContentField;
use Nuclear\Hierarchy\ContentType;
use Nuclear\Hierarchy\Http\Requests\StoreContentField;
use Nuclear\Hierarchy\Http\Requests\UpdateContentField;
use Illuminate\Http\Request;

class ContentFieldsController extends Controller
{

	/**
	 * Stores the new content field
	 *
	 * @param StoreContentType $request
	 * @param ContentType $contentType
	 * @return json
	 */
	public function store(StoreContentField $request, ContentType $contentType)
	{
		$contentField = new ContentField($request->validated());
		$contentField->position = $contentType->fields()->count() + 1;

		$contentType->fields()->save($contentField);

		Cache::forget('contentType.' . $contentType->id);

		activity()->on($contentField)->log('ContentFieldStored');

		return [
			'message' => __('hierarchy::contentfields.created'),
			'payload' => $contentField
		];
	}

	/**
	 * Retrieves the content field information
	 *
	 * @param ContentType $contentType
	 * @param ContentField $contentField
	 * @return json
	 */
	public function show(ContentType $contentType, ContentField $contentField)
	{
		$contentField->contentType = $contentType;

		return $contentField;
	}

	/**
	 * Updates the content field
	 *
	 * @param UpdateContentField $request
	 * @param ContentType $contentType
	 * @param ContentField $contentField
	 * @return json
	 */
	public function update(UpdateContentField $request, ContentType $contentType, ContentField $contentField)
	{
		$contentField->update($request->validated());
		$contentField->contentType = $contentType;

		Cache::forget('contentType.' . $contentType->id);

		activity()->on($contentField)->log('ContentFieldUpdated');

		return [
			'message' => __('hierarchy::contentfields.edited'),
			'payload' => $contentField
		];
	}

	/**
	 * Deletes a content type
	 *
	 * @param int $contentType
	 * @param ContentField $contentField
	 * @return json
	 */
	public function destroy($contentType, ContentField $contentField)
	{
		$name = $contentField->name;

		$contentField->delete();

		Cache::forget('contentType.' . $contentType);

		activity()->withProperties(compact('name'))->log('ContentFieldDestroyed');

		return ['message' => __('hierarchy::contentfields.deleted')];
	}

}