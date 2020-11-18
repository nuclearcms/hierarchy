<?php

use Nuclear\Hierarchy\ContentType;

if (! function_exists('get_schema_for')) {

	/**
	 * Returns the form schema for a content type
	 *
	 * @param int $contentTypeId
	 * @return array
	 */
	function get_schema_for($contentTypeId)
	{
		return Cache::rememberForever('contentType.' . $contentTypeId, function() use ($contentTypeId) {

			$fieldsData = ContentType::findOrFail($contentTypeId)->fields()->where('is_visible', true)->orderBy('position')->get();

			$rules = [];
			$fields = [];
			$schema = [];

			foreach($fieldsData as $field)
			{
				$fields[$field->name] = $field->type;
				$schema[] = [
					'type' => $field->type,
					'name' => $field->name,
					'label' => $field->label,
					'options' => json_decode($field->options, true),
					'default_value' => $field->default_value,
					'hint' => $field->description
				];

				$rules[$field->name] = 'required|array|min:1';
				if(!empty($field->rules)) $rules[$field->name . '.*'] = $field->rules;
			}

			return compact('rules', 'fields', 'schema');
		});
	}

}