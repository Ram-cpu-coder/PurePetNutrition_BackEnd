<?php
function v_required($value): bool {
    return !is_null($value) && $value !== '';
}

function v_maxlen($value, int $max): bool {
    return is_string($value) ? mb_strlen($value) <= $max : false;
}

function v_int($value): bool {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

function v_float($value): bool {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

function v_between_int($value, int $min, int $max): bool {
    if (!v_int($value)) return false;
    $iv = (int)$value;
    return $iv >= $min && $iv <= $max;
}

function v_email($value): bool {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function v_url($value): bool {
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
}

function v_enum($value, array $allowed): bool {
    return in_array($value, $allowed, true);
}