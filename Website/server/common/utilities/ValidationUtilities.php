<?php
    define("DefaultDateFormat", "Y-m-d h:i");

    function ValidateStringLength($string, $minLength, $maxLength) {
        $length = strlen($string);
        return $length >= $minLength && $length <= $maxLength;
    }

    function ValidateNumber($number) {
        return is_numeric($number);
    }

    function ValidateBool($bool) {
        if (is_string($bool)) {
            switch (strtolower($bool)) {
                case 'true':
                case 'false':
                case '1':
                case '0':
                    return true;
                default:
                    return false;
            }
        }
        else if (is_numeric($bool)) {
            switch($bool) {
                case 0:
                case 1:
                    return true;
                default:
                    return false;
            }
        }
        else {
            return is_bool($bool);
        }
    }

    function ValidateDate($date, $format = DefaultDateFormat) {
        $validatedDate = DateTime::createFromFormat($format, $date);
        $dateErrors = DateTime::getLastErrors();
        if ($dateErrors['warning_count'] + $dateErrors['error_count'] > 0) {
            return false;
        }
        return true;
    }
?>