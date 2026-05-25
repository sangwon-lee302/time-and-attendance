<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Override;

class AttendanceCorrectionApplicationRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    #[Override]
    protected function prepareForValidation(): void
    {
        $date   = $this->route('attendance')->date->format('Y-m-d');
        $errors = [];

        $this->merge([
            'new_clocked_in_at' => $this->mergeDateAndTime(
                $date,
                $this->input('new_clocked_in_at'),
                'new_clocked_in_at',
                $errors
            ),
            'new_clocked_out_at' => $this->mergeDateAndTime(
                $date,
                $this->input('new_clocked_out_at'),
                'new_clocked_in_at',
                $errors
            ),
            'breaks' => collect($this->input('breaks', []))
                ->map(function ($break, $index) use ($date, &$errors) {
                    return [
                        ...$break,
                        'new_started_at' => $this->mergeDateAndTime(
                            $date,
                            $break['new_started_at'] ?? null,
                            "breaks.$index.new_started_at",
                            $errors
                        ),
                        'new_ended_at' => $this->mergeDateAndTime(
                            $date,
                            $break['new_ended_at'] ?? null,
                            "breaks.$index.new_ended_at",
                            $errors
                        ),
                    ];
                })
                ->all(),
        ]);

        if (! empty($errors)) {
            throw new HttpResponseException(
                redirect()->back()->withInput()->withErrors($errors)
            );
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()
            ->can('createCorrectionApplication', $this->route('attendance'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_clocked_in_at' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'before_or_equal:new_clocked_out_at',
            ],
            'new_clocked_out_at' => [
                'required',
                'date_format:Y-m-d H:i:s',
            ],
            'breaks'                 => ['nullable', 'array'],
            'breaks.*.break_time_id' => [
                'nullable',
                'integer',
                Rule::exists('break_times', 'id')->where('attendance_id',
                    $this->route('attendance')->id
                ),
            ],
            'breaks.*.new_started_at' => [
                'required_with:breaks.*.new_ended_at',
                'date_format:Y-m-d H:i:s',
                'after_or_equal:new_clocked_in_at',
                'before_or_equal:new_clocked_out_at',
            ],
            'breaks.*.new_ended_at' => [
                'required_with:breaks.*.new_started_at',
                'date_format:Y-m-d H:i:s',
                'after_or_equal:breaks.*.new_started_at',
                'before_or_equal:new_clocked_out_at',
            ],
            'remarks' => ['required', 'string', 'max:65535'],
        ];
    }

    #[Override]
    public function attributes()
    {
        return [
            'new_clocked_in_at'       => '出勤時間',
            'new_clocked_out_at'      => '退勤時間',
            'breaks.*.break_time_id'  => '休憩ID',
            'breaks.*.new_started_at' => '休憩開始時間',
            'breaks.*.new_ended_at'   => '休憩終了時間',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'new_clocked_in_at.before_or_equal'       => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.new_started_at.after_or_equal'  => '休憩時間が不適切な値です',
            'breaks.*.new_started_at.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.new_ended_at.before_or_equal'   => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required'                        => ':attributeを記入してください',
        ];
    }

    /**
     * Merge the given time with the date to create a datetime string.
     */
    private function mergeDateAndTime(string $date, ?string $time, string $field, array &$errors): ?string
    {
        if (blank($time)) {
            return null;
        }

        if (! preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $errors[$field] = '時刻は「時:分」の形式（例: 9:05, 23:59）で入力してください。';

            return null;
        }

        return Carbon::createFromFormat('Y-m-d G:i', "$date $time")
            ->format('Y-m-d H:i:s');
    }
}
