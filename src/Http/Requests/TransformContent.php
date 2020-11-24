<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransformContent extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content_type_id' => 'required|integer'
        ];
    }
}