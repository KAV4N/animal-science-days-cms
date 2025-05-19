<?php

namespace App\Http\Requests\PageMenu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageMenuRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('page_menus')->where(function ($query) {
                    return $query->where('conference_id', $this->route('conference')->id);
                })->ignore($this->route('menu')->id),
            ],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}