<?php

namespace Nuclear\Hierarchy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslateContent extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title_translation' => 'required|max:255',
            'locale' => 'required|in:' . implode(',', config('app.locales'))
        ];
    }
}