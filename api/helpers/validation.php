<?php
function validate(array $data, array $rules): array
{
    $errors = [];

    foreach ($rules as $key => $ruleSet) {
        $rulesArray = explode('|', $ruleSet);
        $value = $data[$key] ?? null;

        foreach ($rulesArray as $rule) {
            if ($rule === 'required' && ($value === null || $value === '')) {
                $errors[$key][] = 'この項目は必須です';
            }

            if (str_starts_with($rule, 'max:')) {
                $max = (int) explode(':', $rule)[1];
                if (is_string($value) && mb_strlen($value) > $max) {
                    $errors[$key][] = "最大{$max}文字までです";
                }
            }

            if ($rule === 'numeric' && !is_numeric($value)) {
                $errors[$key][] = '数値である必要があります';
            }
        }
    }

    return $errors;
}
