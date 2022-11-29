<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Nuclear\Hierarchy\ContentType;

class UpdateContent extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $contentTypeId = $this->input('content_type_id');

        $rules = \Cache::rememberForever('contentType.' . $contentTypeId . '.rules', function() use ($contentTypeId) {

            $fieldsData = ContentType::findOrFail($contentTypeId)->fields()->orderBy('position')->get();

            $rules = [];

            foreach($fieldsData as $field)
            {
                if(!$field->is_visible) continue;

                $rules[$field->name] = 'required|array|min:1';
                if(!empty($field->rules)) $rules[$field->name . '.*'] = $field->rules;
            }

            return $rules;
        });

        return array_merge([
            'title' => 'required|array|min:1',
            'title.*' => 'required|max:255',
            'slug' => 'nullable|array|min:1',
            'slug.*' => 'nullable|max:255',
            'meta_title' => 'required|array|min:1',
            'meta_description' => 'required|array|min:1',
            'meta_author' => 'required|array|min:1',
            'cover_image' => 'required|array|min:1',
            'keywords' => 'required|array|min:1',
            'status' => 'required|integer',
            'tags' => 'nullable|array'
        ], $rules);
    }
}