<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],

            // 休憩は複数：空でもOK。入力された場合のみチェックを強める
            'break_start' => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end' => ['array'],
            'break_end.*' => ['nullable', 'date_format:H:i'],

            'memo' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start.*.date_format' => '休憩時間が不適切な値です',
            'break_end.*.date_format' => '休憩時間が不適切な値です',
            'memo.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $in  = $this->input('clock_in');
            $out = $this->input('clock_out');

            // 出勤・退勤の前後関係
            if ($in && $out && $in >= $out) {
                $v->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breakStarts = $this->input('break_start', []);
            $breakEnds   = $this->input('break_end', []);

            $count = max(count($breakStarts), count($breakEnds));
            for ($i = 0; $i < $count; $i++) {
                $bs = $breakStarts[$i] ?? null;
                $be = $breakEnds[$i] ?? null;

                if (!$bs && !$be) continue;

                // 片方だけ入力はNG（要件にないけど、実務的に必要なので入れておく）
                if ($bs && !$be) {
                    $v->errors()->add("break_end.$i", '休憩時間が不適切な値です');
                    continue;
                }
                if (!$bs && $be) {
                    $v->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    continue;
                }

                // 休憩開始 < 休憩終了
                if ($bs && $be && $bs >= $be) {
                    $v->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    continue;
                }

                // 休憩と勤務時間の整合
                if ($in && $bs && $bs < $in) {
                    $v->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                }
                if ($out && $bs && $bs > $out) {
                    $v->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                }
                if ($out && $be && $be > $out) {
                    $v->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
