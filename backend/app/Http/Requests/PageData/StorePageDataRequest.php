<?php

namespace App\Http\Requests\PageData;

use Illuminate\Foundation\Http\FormRequest;

class StorePageDataRequest extends FormRequest
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
            'component_type' => ['required', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:255'],
            'data' => ['required', 'array'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}