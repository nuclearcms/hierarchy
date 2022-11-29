<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentSettings extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_visible' => 'required|boolean',
            'is_sterile' => 'required|boolean',
            'is_locked' => 'required|boolean',
            'priority' => 'required|numeric',
            'published_at' => 'required|date',
            'unpublished_at' => 'nullable|date',
            'status' => 'required|integer',
            'hides_children' => 'required|boolean',
            'children_display_mode' => 'required|string|in:tree,list',
        ];
    }
}