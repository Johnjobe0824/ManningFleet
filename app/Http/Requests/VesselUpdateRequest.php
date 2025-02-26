<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class VesselUpdateRequest extends FormRequest
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
            'vessel_name' => 'required|string|max:255',
            'vessel_bow_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vessels', 'vessel_bow_no')->ignore($this->route('vessel')),
            ],
            'category' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ];
    }
    
}
