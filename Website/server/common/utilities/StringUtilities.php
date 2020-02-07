<?php
    function str_replace_first($find, $replace, $string) {
        $pos = strpos($string, $find);
        if ($pos !== false) {
            return substr_replace($string, $replace, $pos, strlen($find));
        }
        return $string;
    }

    // Source: https://stackoverflow.com/a/6041773/8070411
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // Source: https://stackoverflow.com/a/381275/8070411
    function IsNullOrEmptyString($str){
        return (!isset($str) || trim($str) === '');
    }
?>