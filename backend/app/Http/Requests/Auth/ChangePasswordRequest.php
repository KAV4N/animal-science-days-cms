<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $user = $this->user();

        // For first login, we don't require current password
        if ($user && $user->first_login) {
            return [
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
                'new_password_confirmation' => ['required', 'string', 'min:8'],
            ];
        }

        // For regular password changes, require current password
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'new_password_confirmation' => ['required', 'string', 'min:8'],
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
