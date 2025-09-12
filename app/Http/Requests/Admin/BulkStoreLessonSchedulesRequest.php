<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Carbon;
use App\Models\LessonSchedule;

class BulkStoreLessonSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', Rule::exists('lessons', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.start_datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'items.*.end_datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_datetime'],
            'items.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (is_array($items)) {
            foreach ($items as $idx => $row) {
                if (is_array($row)) {
                    if (array_key_exists('is_active', $row)) {
                        $casted = filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($casted !== null) {
                            $items[$idx]['is_active'] = $casted;
                        }
                        // invalid values are left as-is to be caught by validation
                    } else {
                        $items[$idx]['is_active'] = true; // default when not provided
                    }
                    // Normalize datetime-local (YYYY-MM-DDTHH:MM) or other formats to Y-m-d H:i:s
                    if (! empty($row['start_datetime'])) {
                        try {
                            $items[$idx]['start_datetime'] = \Illuminate\Support\Carbon::parse(str_replace('T', ' ', (string) $row['start_datetime']))->format('Y-m-d H:i:s');
                        } catch (\Throwable $e) {
                            // keep original; validation will catch invalid date
                        }
                    }
                    if (! empty($row['end_datetime'])) {
                        try {
                            $items[$idx]['end_datetime'] = \Illuminate\Support\Carbon::parse(str_replace('T', ' ', (string) $row['end_datetime']))->format('Y-m-d H:i:s');
                        } catch (\Throwable $e) {
                            // keep original; validation will catch invalid date
                        }
                    }
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    /**
     * Add custom validation to prevent overlapping schedules.
     */
    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $v) {
            $data = $this->all();
            if (!isset($data['lesson_id']) || !isset($data['items']) || !is_array($data['items'])) {
                return;
            }

            $lessonId = (int) $data['lesson_id'];
            $items = $data['items'];

            // Check overlaps within payload (pairwise) and against DB
            $count = count($items);
            for ($i = 0; $i < $count; $i++) {
                $a = $items[$i] ?? null;
                if (!is_array($a)) { continue; }
                $aStart = $a['start_datetime'] ?? null;
                $aEnd = $a['end_datetime'] ?? null;
                if (empty($aStart) || empty($aEnd)) { continue; }
                try {
                    $aStartAt = Carbon::parse($aStart);
                    $aEndAt = Carbon::parse($aEnd);
                } catch (\Throwable $e) {
                    continue; // base rules will flag invalid format
                }

                // In-payload overlap
                for ($j = $i + 1; $j < $count; $j++) {
                    $b = $items[$j] ?? null;
                    if (!is_array($b)) { continue; }
                    $bStart = $b['start_datetime'] ?? null;
                    $bEnd = $b['end_datetime'] ?? null;
                    if (empty($bStart) || empty($bEnd)) { continue; }
                    try {
                        $bStartAt = Carbon::parse($bStart);
                        $bEndAt = Carbon::parse($bEnd);
                    } catch (\Throwable $e) {
                        continue;
                    }
                    // Overlap if a.start < b.end && a.end > b.start  (half-open)
                    if ($aStartAt->lt($bEndAt) && $aEndAt->gt($bStartAt)) {
                        $v->errors()->add('items.'.$i.'.start_datetime', '同一送信内で時間帯が重複しています');
                        $v->errors()->add('items.'.$j.'.start_datetime', '同一送信内で時間帯が重複しています');
                    }
                }

                // DB overlap for same lesson
                if (isset($aStartAt, $aEndAt) && LessonSchedule::hasOverlap($lessonId, $aStartAt, $aEndAt)) {
                    $v->errors()->add('items.'.$i.'.start_datetime', '既存スケジュールと時間帯が重複しています');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'lesson_id' => 'レッスン',
            'items' => 'スケジュール',
            'items.*.start_datetime' => '開始日時',
            'items.*.end_datetime' => '終了日時',
            'items.*.is_active' => '有効フラグ',
        ];
    }
}
