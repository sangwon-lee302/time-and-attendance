<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class AttendanceCorrectionApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    #[Override]
    protected function prepareForValidation(): void
    {
        $date = $this->route('attendance')->date->format('Y-m-d');

        $clockedInTime = $this->input('new_clocked_in_at');
        $clockedOutTime = $this->input('new_clocked_out_at');

        $newClockedInAt = $this->mergeTimeWithDate($date, $clockedInTime);
        $newClockedOutAt = $this->mergeTimeWithDate($date, $clockedOutTime);

        $this->merge([
            'new_clocked_in_at' => $newClockedInAt,
            'new_clocked_out_at' => $newClockedOutAt,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_clocked_in_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'new_clocked_out_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:new_clocked_in_at'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.new_started_at' => ['required_with:breaks.*.new_ended_at', 'date_format:Y-m-d H:i:s'],
            'breaks.*.new_ended_at' => ['required_with:breaks.*.new_started_at', 'date_format:Y-m-d H:i:s', 'after:breaks.*.new_started_at'],
            'remarks' => ['required', 'string', 'max:65535'],
        ];
    }

    /**
     * Merge the given time with the date to create a datetime string.
     */
    private function mergeTimeWithDate(string $date, string $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d G:i', "$date $time")
            ->format('Y-m-d H:i:s');
    }
}
