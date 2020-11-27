<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContent extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge([
            'title' => 'required|array|min:1',
            'title.*' => 'required|max:255',
            'meta_title' => 'required|array|min:1',
            'meta_description' => 'required|array|min:1',
            'author' => 'required|array|min:1',
            'cover_image' => 'required|array|min:1',
            'keywords' => 'required|array|min:1',
            'status' => 'required|integer',
            'tags' => 'nullable|array'
        ], get_schema_for($this->input('content_type_id'))['rules']);
    }
}