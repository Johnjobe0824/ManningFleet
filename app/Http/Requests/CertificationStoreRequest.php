<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificationStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'training_center' => 'required|string|max:255',
            'batch_no' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'user_id' => ['required', 'exists:users,id'],
            'status' => 'nullable|in:pending,approved,rejected',
        ];
    }
}
