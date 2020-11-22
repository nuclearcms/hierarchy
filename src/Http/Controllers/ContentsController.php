<?php

namespace Nuclear\Hierarchy\Http\Controllers;

use Umomega\Foundation\Http\Controllers\Controller;
use Nuclear\Hierarchy\Content;
use Nuclear\Hierarchy\ContentType;
use Umomega\Tags\Tag;
use Nuclear\Hierarchy\Http\Requests\StoreContent;
use Nuclear\Hierarchy\Http\Requests\UpdateContent;
use Nuclear\Hierarchy\Http\Requests\UpdateContentSettings;
use Nuclear\Hierarchy\Http\Requests\UpdateContentState;
use Nuclear\Hierarchy\Http\Requests\MoveContent;
use Nuclear\Hierarchy\Http\Requests\TranslateContent;
use Spatie\Searchable\Search;
use Illuminate\Http\Request;

class ContentsController extends Controller
{

	/**
	 * Returns a list of contents
	 *
	 * @param Request $request
	 * @return json
	 */
	public function index(Request $request)
	{
		$s = $request->get('s', 'created_at');

		if($s == 'title') $s .= '->' . app()->getLocale();

		$contents = Content::orderBy($s, $request->get('d', 'desc'))->with('contentType');

		if($request->get('f', 'all') != 'all') {
			$contents = $contents->filteredByStatus($request->get('f'));
		}

		return $contents->paginate();
	}

	/**
	 * Returns root contents with the tree structure
	 *
	 * @return json
	 */
	public function roots()
	{
		return $this->compileVisibleTree(
			Content::with('contentType')
				->orderBy('parent_id')
				->orderBy('position')
				->whereNull('parent_id')->get());
	}

	/**
	 * Recursively compiles the given contents for a visible tree
	 *
	 * @param Collection $contents
	 * @return Collection
	 */
	protected function compileVisibleTree($contents)
	{
		foreach($contents as $content)
		{
			$content->tree = [];
			
			if(!$content->hides_children && !$content->contentType->hides_children)
			{
				$content->tree = $this->compileVisibleTree(
					$content->children()
						->with('contentType')
						->orderBy('parent_id')
						->orderBy('position')
						->get());
			}
		}

		return $contents;
	}

	/**
	 * Returns a list of content filtered by search
	 *
	 * @param Request $request
	 * @return json
	 */
	public function search(Request $request)
	{
		return ['data' => (new Search())
			->registerModel(Content::class, function($aspect) {
				$aspect
					->addSearchableAttribute('title')
					->addSearchableAttribute('keywords')
					->addSearchableAttribute('meta_title')
					->addSearchableAttribute('meta_description')
					->addSearchableAttribute('meta_author')
					->with('contentType');
			})
			->search($request->get('q'))
			->map(function($content) {
				return $content->searchable;
			})];
	}

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
			'payload' => $content,
			'event' => 'content-tree-modified'
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
		return $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published', 'schema', 'extensions', 'tags']);
	}


	/**
	 * Retrieves the tag contents
	 *
	 * @param Request $request
	 * @param Tag $tag
	 * @return json
	 */
	public function tagged(Request $request, Tag $tag)
	{
		$s = $request->get('s', 'created_at');

		if($s == 'title') $s .= '->' . app()->getLocale();

		return Content::withAnyTags([$tag])->orderBy($s, $request->get('d', 'desc'))->with('contentType')->paginate();
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

		if($content->contentType->is_taggable) {
			$content->tags()->sync(collect($request->get('tags'))->pluck('id')->toArray());
		}

		activity()->on($content)->log('ContentUpdated');

		return [
			'message' => __('hierarchy::contents.edited'),
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published']),
			'event' => 'content-tree-modified'
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
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published']),
			'event' => 'content-tree-modified'
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
			$content->status = $content->is_published ? Content::DRAFT : Content::PUBLISHED;
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
			'payload' => $content->setAppends(['contentType', 'locales', 'ancestors', 'is_published']),
			'event' => 'content-tree-modified'
		];
	}

	/**
	 * Moves a content
	 *
	 * @param MoveContent $request
	 * @param Content $content
	 * @param Content $parent
	 * @return json
	 */
	public function move(MoveContent $request, Content $content, Content $parent = null)
	{
		$this->validateContentIsEditable($content);

		if($parent) {
			if((!is_null($content->parent_id) && ($content->parent->is_locked || $parent->is_locked)) || (is_null($content->parent_id) && $parent->is_locked)) abort(403, __('hierarchy::contents.content_is_locked'));

			if($parent->is_sterile) abort(403, __('hierarchy::contents.parent_is_sterile'));

			if(!in_array($content->contentType->id, collect($parent->contentType->allowed_children_types)->pluck('id')->toArray())) abort(403, __('hierarchy::contents.parent_does_not_allow_type'));
		}
		
		if((is_null($content->parent_id) && is_null($parent)) || (!is_null($parent) && $content->parent_id == $parent->id)) {
			$content->position = $request->get('position');
			$content->save();
		} else {
			$content->moveTo($request->get('position'), $parent);
		}

		return [
			'message' => __('hierarchy::contents.moved_content'),
			'event' => 'content-tree-modified'
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
			'action' => ['locale', $request->get('locale')],
			'event' => 'content-tree-modified'
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
			'fallback' => ['name' => 'contents.edit', 'params' => ['id' => $content->id]],
			'event' => 'content-tree-modified'
		];
	}

	/**
	 * Bulk deletes contents
	 *
	 * @param Request $request
	 * @return json
	 */
	public function destroyBulk(Request $request)
	{
		$items = $this->validate($request, ['items' => 'required|array'])['items'];
		
		$names = Content::whereIn('id', $items)->where('is_locked', false)->pluck('title')->toArray();
		
		Content::whereIn('id', $items)->where('is_locked', false)->delete();

		activity()->withProperties(compact('names'))->log('ContentsDestroyedBulk');

		return [
			'message' => __('hierarchy::contents.deleted_multiple'),
			'event' => 'content-tree-modified'
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
			'redirect' => 'contents.index',
			'event' => 'content-tree-modified'
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