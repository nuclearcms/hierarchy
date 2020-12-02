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

			$fieldsData = ContentType::findOrFail($contentTypeId)->fields()->orderBy('position')->get();

			$rules = [];
			$fields = [];
			$schema = [];

			foreach($fieldsData as $field)
			{
				$fields[$field->name] = ['type' => $field->type, 'field_id' => $field->id];

				if(!$field->is_visible) continue;

				$options = json_decode($field->options, true);

				$schema[] = [
					'type' => ($field->type == 'ContentRelationField' ? 'RelationField' : $field->type),
					'name' => $field->name,
					'label' => $field->label,
					'options' => ($field->type == 'ContentRelationField'
						? (is_array($options)
							? array_merge(['searchroute' => 'contents/search/relatable', 'namekey' => 'title', 'translated' => true, 'multiple' => true], $options)
							: ['searchroute' => 'contents/search/relatable', 'namekey' => 'title', 'translated' => true, 'multiple' => true])
						: $options),
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