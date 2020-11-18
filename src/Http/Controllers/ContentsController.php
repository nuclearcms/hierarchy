<?php

namespace Nuclear\Hierarchy\Http\Controllers;

use Umomega\Foundation\Http\Controllers\Controller;
use Nuclear\Hierarchy\Content;
use Nuclear\Hierarchy\ContentType;
use Nuclear\Hierarchy\Http\Requests\StoreContent;
use Nuclear\Hierarchy\Http\Requests\UpdateContent;
use Nuclear\Hierarchy\Http\Requests\UpdateContentSettings;
use Nuclear\Hierarchy\Http\Requests\UpdateContentState;
use Nuclear\Hierarchy\Http\Requests\TranslateContent;

class ContentsController extends Controller
{

	/**
	 * Returns relevent information before creating a content
	 *
	 * @param int|null $parent
	 * @return json
	 */
	public function precreate($parent = null)
	{
		if(is_null($parent)) {
			return  [
				'action' => 'populate',
				'types' => ContentType::where('is_visible', true)->orderBy('name')->get()
			];
		}

		// Check first if parent exists or sterile
		$parent = Content::findOrFail($parent);

		if($parent->is_sterile) return [
			'action' => 'redirect',
			'parent' => compact('parent'),
			'message' => __('hierarchy::contents.content_cannot_have_children')
		];

		return [
			'action' => 'populate',
			'types' => $parent->contentType->getAllowedChildrenTypes(),
			'parent' => $parent
		];
	}

	/**
	 * Stores the new content
	 *
	 * @param StoreContent $request
	 * @param int|null $parent
	 * @return json
	 */
	public function store(StoreContent $request, $parent = null)
	{	
		// Check first if parent exists or sterile
		if(!is_null($parent)) {
			$parent = Content::findOrFail($parent);

			if($parent->is_sterile) abort(422, __('hierarchy::contents.content_cannot_have_children'));
		}

		// Proceed to saving
		$validated = $request->validated();
		if($parent) $validated['parent_id'] = $parent->id;

		$content = Content::create($validated);

		activity()->on($content)->log('ContentStored');

		return [
			'message' => __('hierarchy::contents.created'),
			'payload' => $content
		];
	}

	/**
	 * Retrieves the content information
	 *
	 * @param Content $content
	 * @return json
	 */
	public function show(Content $content)
	{
		return $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published', 'schema', 'extensions']);
	}

	/**
	 * Updates the content
	 *
	 * @param UpdateContent $request
	 * @param Content $content
	 * @return json
	 */
	public function update(UpdateContent $request, Content $content)
	{
		$this->validateContentIsEditable($content);

		$content->update($request->validated());

		$extensionFieldNames = $content->schema['fields'];

		foreach($extensionFieldNames as $name => $type) {
			$content->getExtension($name)->update(['value' => $request->get($name)]);
		}

		activity()->on($content)->log('ContentUpdated');

		return [
			'message' => __('hierarchy::contents.edited'),
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published'])
		];
	}

	/**
	 * Updates the content settings
	 *
	 * @param UpdateContentSettings $request
	 * @param Content $content
	 * @return json
	 */
	public function updateSettings(UpdateContentSettings $request, Content $content)
	{
		$this->validateContentIsEditable($content);

		$content->update($request->validated());

		activity()->on($content)->log('ContentSettingsUpdated');

		return [
			'message' => __('hierarchy::contents.edited_settings'),
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published'])
		];
	}

	/**
	 * Updates the content state
	 *
	 * @param UpdateContentState $request
	 * @param Content $content
	 * @return json
	 */
	public function updateState(UpdateContentState $request, Content $content)
	{
		$state = $request->get('state');

		if($state != 'is_locked') $this->validateContentIsEditable($content);

		if($state == 'is_published') {
			$content->status = $content->is_published ? 30 : 50;
			$message = $content->is_published ? 'published_content' : 'unpublished_content';
		} else {
			$content->{$state} = !$content->{$state};
			$message = str_replace('is_', '', $state);
			$message = ($content->{$state} ? $message : 'un' . $message) . '_content';
		}

		$content->save();

		activity()->on($content)->log('ContentStateUpdated');

		return [
			'message' => __('hierarchy::contents.' . $message),
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published'])
		];
	}

	/**
	 * Translates the content
	 *
	 * @param TranslateContent $request
	 * @param Content $content
	 * @return json
	 */
	public function translate(TranslateContent $request, Content $content)
	{
		$this->validateContentIsEditable($content);

		$content->setTranslation('title', $request->get('locale'), $request->get('title_translation'));
		$content->save();

		activity()->on($content)->log('ContentTranslated');

		return [
			'message' => __('hierarchy::contents.translated'),
			'payload' => $content,
			'action' => ['locale', $request->get('locale')] 
		];
	}

	/**
	 * Deletes a content translation
	 *
	 * @param Content $content
	 * @param string $locale
	 * @return json
	 */
	public function destroyTranslation(Content $content, $locale)
	{
		$this->validateContentIsEditable($content);
		
		$title = $content->getTranslation('title', $locale);
		$content->forgetAllTranslations($locale);

		$extensionFields = $content->schema['fields'];
		foreach($extensionFields as $field => $type) {
			$content->getExtension($field)->forgetAllTranslations($locale)->save();
		}

		$content->save();

		activity()->withProperties(compact('title'))->log('ContentTranslationDestroyed');

		return [
			'message' => __('foundation::general.deleted_translation'),
			'fallback' => ['name' => 'contents.edit', 'params' => ['id' => $content->id]]
		];
	}

	/**
	 * Deletes a content
	 *
	 * @param Content $content
	 * @return json
	 */
	public function destroy(Content $content)
	{
		$this->validateContentIsEditable($content);

		$name = $content->title;

		$content->deleteSubtree(true);

		activity()->withProperties(compact('name'))->log('ContentDestroyed');

		return [
			'message' => __('hierarchy::contents.deleted'),
			'redirect' => 'contents.index'
		];
	}

	/**
	 * Checks if the content is editable
	 *
	 * @param Content $content
	 */
	protected function validateContentIsEditable($content)
	{
		if($content->is_locked) abort(403, __('hierarchy::contents.content_is_locked'));
	}

}