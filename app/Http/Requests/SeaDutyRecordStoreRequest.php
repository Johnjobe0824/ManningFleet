<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeaDutyRecordStoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'user_id'     => ['required', 'exists:users,id'],
            'vessel_id'   => ['required', 'exists:vessels,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'date_from'   => ['required', 'date'],
            'date_to'     => ['required', 'date', 'after_or_equal:date_from'],
            'authority'   => ['required', 'string', 'max:255'],
            'tst'         => ['required', 'string', 'max:255'],
            'tmc'         => ['required', 'string', 'max:255'],
            'status'      => ['required', 'in:Pending,Approved,Rejected'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

}
