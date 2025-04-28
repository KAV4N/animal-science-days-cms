<?php
namespace App\Http\Requests\Conference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConferenceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'university_id' => 'required|exists:universities,id',
            
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:conferences',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'venue_details' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'primary_color' => 'required|string|max:255',
            'secondary_color' => 'required|string|max:255',

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