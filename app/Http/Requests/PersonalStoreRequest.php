<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rank_id' => ['required', 'exists:ranks,id'],
            'serial_no' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable','string', 'max:255'],
            'gender' => ['required','string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'civil_status' => ['required', 'string','max:255'],
            'user_id' => ['required','exists:users,id'],
            'age' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'string', 'max:255'],
        ];
    }
}
