<?php

namespace App\Http\Requests\PageData;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'component_type' => ['sometimes', 'string', 'max:100'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'data' => ['sometimes', 'array'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}