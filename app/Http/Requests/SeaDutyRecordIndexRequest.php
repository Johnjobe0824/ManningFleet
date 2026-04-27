<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeaDutyRecordIndexRequest extends FormRequest
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
            'field' => ['in:user_id,vessel_id,position_id,date_from,date_to,residency,authority,tst,tmc,status,created_at,updated_at'],
            'order' => ['in:asc,desc'],
            'perPage' => ['numeric'],
        ];
    }
}
