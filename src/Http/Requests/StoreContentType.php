<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentType extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'color' => 'required|array',
            'visible' => 'required|boolean',
            'hides_children' => 'required|boolean',
            'taggable' => 'required|boolean',
            'allowed_children_types' => 'nullable|array'
        ];
    }
}