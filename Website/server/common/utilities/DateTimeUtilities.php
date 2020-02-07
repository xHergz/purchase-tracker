<?php
    function DaysBetweenDates($dateOne, $dateTwo) {
        $earlierDate = new DateTime($dateOne);
        $laterDate = new DateTime($dateTwo);

        return $laterDate->diff($earlierDate)->format('%a');
    }

?>