<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentField extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'label' => 'required|max:255',
            'description' => 'nullable',
            'search_priority' => 'required|integer',
            'visible' => 'required|boolean',
            'rules' => 'nullable',
            'default_value' => 'nullable',
            'options' => 'nullable|json'

        ];
    }
}