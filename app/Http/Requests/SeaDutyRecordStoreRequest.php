<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SeaDutyRecord;
use Carbon\Carbon;


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
   public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'vessel_id' => ['required', 'exists:vessels,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'residency' => ['required', 'string', 'max:255'],
            'authority' => ['required', 'string', 'max:255'],
            'tst' => ['required', 'string', 'max:255'],
            'tmc' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:Pending,Approved,Rejected'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }


   public function withValidator($validator)
    {
        $validator->after(function ($validator) {

        $dateFrom = Carbon::parse($this->date_from);
        $dateTo = $this->date_to ? Carbon::parse($this->date_to) : null;

        $exists = SeaDutyRecord::where('vessel_id', $this->vessel_id)
            ->where('position_id', $this->position_id)
            ->where(function ($query) use ($dateFrom, $dateTo) {

                // Case 1: Existing record has NO end date (ongoing)
                $query->where(function ($q) use ($dateFrom) {
                    $q->whereNull('date_to')
                      ->where('date_from', '<=', $dateFrom);
                });

                // OR

                // Case 2: Normal overlap logic
                $query->orWhere(function ($q) use ($dateFrom, $dateTo) {
                    $q->where(function ($qq) use ($dateFrom, $dateTo) {
                        $qq->where('date_from', '<=', $dateTo ?? now())
                           ->where(function ($qqq) use ($dateFrom) {
                               $qqq->where('date_to', '>=', $dateFrom)
                                   ->orWhereNull('date_to');
                           });
                    });
                });

            })
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'position_id',
                'This position is already assigned to this vessel within the selected date range by another user.'
            );
        }
    });
}

}




