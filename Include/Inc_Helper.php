<?php
class Inc_Helper {
    /**
     * return true: valid, false: not valid
     */
    static function validateAlphanumericUnderscore ($str)  {
        $isValid = preg_match('/^[A-Za-z0-9_]+$/', $str);
        return !empty($isValid);
    }
    static function validateEmail ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    static function validateDate ($input, $dateFormat = 'Y-m-d') {
        return date($dateFormat, strtotime($input)) == $input;
    }
    static function requireField ($input) {
        return !empty($input);
    }
}
