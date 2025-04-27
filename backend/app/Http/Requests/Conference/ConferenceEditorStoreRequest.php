<?php

namespace App\Http\Requests\Conference;

use App\Models\ConferenceEditor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConferenceEditorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $conference = $this->route('conference');
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($conference) {
                    $exists = ConferenceEditor::where('conference_id', $conference->id)
                        ->where('user_id', $value)
                        ->exists();
                    if ($exists) {
                        $fail('This user is already an editor for this conference.');
                    }
                },
            ],
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