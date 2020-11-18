<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentState extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'state' => 'required|max:255|string'
        ];
    }
}