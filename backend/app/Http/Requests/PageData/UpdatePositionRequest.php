<?php

namespace App\Http\Requests\PageData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PageMenu;

class UpdatePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menu = $this->route('menu');
        $maxPosition = $menu->pageData()->count() - 1; // Zero-based indexing

        return [
            'position' => ['required', 'integer', 'min:0', "max:{$maxPosition}"],
        ];
    }

    /**
     * Get custom error messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'position.required' => 'A position value is required.',
            'position.integer' => 'Position must be an integer.',
            'position.min' => 'Position cannot be negative.',
            'position.max' => 'Position cannot exceed the maximum available position.',
        ];
    }
}