<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentField extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255|alpha_dash',
            'label' => 'required|max:255',
            'type' => 'required',
            'description' => 'nullable',
            'search_priority' => 'required|integer',
            'is_visible' => 'required|boolean',
            'rules' => 'nullable',
            'default_value' => 'nullable',
            'options' => 'nullable|json'

        ];
    }
}