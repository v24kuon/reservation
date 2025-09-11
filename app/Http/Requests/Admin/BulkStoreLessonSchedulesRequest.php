<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
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

    /**
     * Normalize and normalize request items before validation.
     *
     * Iterates over the `items` input (if an array) and:
     * - Ensures `is_active` is a boolean when a recognizable value is provided, defaulting to `true` if omitted.
     * - Attempts to normalize `start_datetime` and `end_datetime` to the `Y-m-d H:i:s` format (accepts ISO-like values such as `YYYY-MM-DDTHH:MM` by replacing `T` with a space).
     * Invalid or unparsable values are left unchanged so standard validation rules can report errors.
     *
     * The transformed `items` array is merged back into the request input.
     *
     * @return void
     */
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
     * Register post-validation checks that prevent overlapping lesson schedules.
     *
     * This attaches an after-hook to the provided validator which:
     * - Exits early if `lesson_id` or `items` are missing or `items` is not an array.
     * - For each item with both `start_datetime` and `end_datetime`, detects:
     *   - Overlaps between items in the same request payload (adds errors to
     *     `items.{index}.start_datetime`).
     *   - Overlaps with existing schedules for the same lesson using
     *     `LessonSchedule::hasOverlap($lessonId, $start, $end)` (adds an error to
     *     `items.{index}.start_datetime`).
     *
     * Errors added by this method use Japanese messages indicating overlap.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
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

                // In-payload overlap
                for ($j = $i + 1; $j < $count; $j++) {
                    $b = $items[$j] ?? null;
                    if (!is_array($b)) { continue; }
                    $bStart = $b['start_datetime'] ?? null;
                    $bEnd = $b['end_datetime'] ?? null;
                    if (empty($bStart) || empty($bEnd)) { continue; }
                    // Overlap if a.start < b.end && a.end > b.start
                    if ($aStart < $bEnd && $aEnd > $bStart) {
                        $v->errors()->add('items.'.$i.'.start_datetime', '同一送信内で時間帯が重複しています');
                        $v->errors()->add('items.'.$j.'.start_datetime', '同一送信内で時間帯が重複しています');
                    }
                }

                // DB overlap for same lesson
                if (LessonSchedule::hasOverlap($lessonId, $aStart, $aEnd)) {
                    $v->errors()->add('items.'.$i.'.start_datetime', '既存スケジュールと時間帯が重複しています');
                }
            }
        });
    }

    /**
     * Provide human-friendly attribute names for validation errors.
     *
     * Returns an associative array mapping request input keys to their display
     * names (used in validation messages). Wildcard keys (e.g. `items.*.start_datetime`)
     * are supported for array item attributes.
     *
     * @return array<string,string> Mapping of request attribute keys to display names.
     */
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
