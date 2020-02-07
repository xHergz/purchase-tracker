<?php
    function ToBool($var) {
        if (!is_string($var)) {
            return (bool)$var;
        }
        switch (strtolower($var)) {
            case '1':
            case 'true':
                return true;
            default:
                return false;
        }
    }

    function ToSqlSafeBool($var) {
        $boolean = ToBool($var);
        if ($boolean) {
            return 1;
        }
        return 0;
    }
?>