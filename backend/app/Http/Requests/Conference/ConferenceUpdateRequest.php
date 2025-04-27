<?php

namespace App\Http\Requests\Conference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ConferenceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $conference = $this->route('conference');
        return [
            'university_id' => 'exists:universities,id',
            'name' => 'string|max:255',
            'title' => 'string|max:255',
            'slug' => [
                'string',
                'max:255',
                Rule::unique('conferences')->ignore($conference->id)
            ],
            'description' => 'nullable|string',
            'location' => 'string|max:255',
            'venue_details' => 'nullable|string',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'primary_color' => 'string|max:255',
            'secondary_color' => 'string|max:255',
            'is_latest' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}