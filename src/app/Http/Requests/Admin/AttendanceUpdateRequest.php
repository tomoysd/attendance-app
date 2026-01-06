<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'memo' => ['required', 'string'],

            // breaks は複数行: breaks[0][start], breaks[0][end], breaks[0][id]
            'breaks' => ['array'],
            'breaks.*.id' => ['nullable', 'integer'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'memo.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $in  = $this->input('clock_in');
            $out = $this->input('clock_out');

            $toMin = function (?string $t): ?int {
                if (!$t) return null;
                [$h, $m] = array_map('intval', explode(':', $t));
                return $h * 60 + $m;
            };

            $inM  = $toMin($in);
            $outM = $toMin($out);

            // 1) 出勤 >= 退勤
            if ($inM !== null && $outM !== null && $inM >= $outM) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $i => $row) {
                $s = $row['start'] ?? null;
                $e = $row['end'] ?? null;
                $sM = $toMin($s);
                $eM = $toMin($e);

                // 片方だけ入力（不完全）→休憩不適切
                if (($s && !$e) || (!$s && $e)) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                    continue;
                }
                // 両方空はOK（削除扱い・無視）
                if (!$s && !$e) {
                    continue;
                }

                // start >= end →休憩不適切
                if ($sM !== null && $eM !== null && $sM >= $eM) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }

                // 出勤/退勤が揃っている時だけ、要件どおり範囲チェック
                if ($inM !== null && $outM !== null) {
                    // 2) 休憩開始が出勤前 or 退勤後
                    if ($sM !== null && ($sM < $inM || $sM > $outM)) {
                        $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                    }
                    // 3) 休憩終了が退勤後
                    if ($eM !== null && $eM > $outM) {
                        $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
