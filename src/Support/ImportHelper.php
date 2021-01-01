<?php

namespace Nuclear\Hierarchy\Support;

use Nuclear\Hierarchy\Content;
use Nuclear\Hierarchy\ContentType;

class ImportHelper {

	/** @var string */
	protected $directory = null;

	/**
	 * Reads the contents of the supplied file path
	 *
	 * @param string $file
	 */
	public function getContentsForImport($file)
	{
		$this->directory = dirname($file);

		return json_decode(file_get_contents($file), true);
	}

	/**
	 * Gets a content
	 *
	 * @param int $id
	 * @return Content|null
	 */
	public function getContent($id)
	{
		if(is_null($id)) return null;

		return Content::find($id);
	}

	/**
	 * Gets a content type
	 *
	 * @param int|null $id
	 * @return ContentType|null
	 */
	public function getContentType($id)
	{
		if(is_null($id)) return null;

		return ContentType::find($id);
	}

	/**
	 * Gets the default parent
	 *
	 * @param int|null $parent
	 * @return Content|null
	 */
	public function getDefaultParent($parent)
	{
		if(is_null($parent)) return null;

		return Content::find($parent);
	}

	/**
	 * Gets the default content type
	 *
	 * @param int|null $type
	 * @return ContentType|null
	 */
	public function getDefaultContentType($type)
	{
		if(is_null($type)) return null;

		return ContentType::find($type);
	}

	/**
	 * Gets the default content type by parent
	 *
	 * @param Content $parent
	 * @return ContentType|null
	 */
	public function getDefaultContentTypeByParent(Content $parent)
	{
		$childrenTypes = $parent->contentType->allowed_children_types;

		if(count($childrenTypes) == 1) return ContentType::find($childrenTypes[0]);

		return null;
	}

	/**
	 * Creates a content
	 *
	 * @param array $data
	 * @return Content
	 */
	public function createContent($data)
	{
		$content = Content::create($data);

		foreach($content->getSchema()['fields'] as $name => $d) {
			$value = isset($data[$name]) ? $data['name'] : null;

			if($d['type'] == 'TextEditorField') {
				$value = ['blocks' => [['data' => ['text' => $value], 'type' => 'paragraph']];
			}

			$value = [config('app.locale') => $value];
		}
	}

}