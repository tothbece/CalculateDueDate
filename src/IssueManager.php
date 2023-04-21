<?php

namespace Emarsys\Issues;

use DateTime;

class IssueManager {

    /**
     * @throws InvalidTurnaroundTimeException
     * @throws NotWorkingHourException
     */
    public static function calculateDueDate(DateTime $startDatetime, int $turnaroundTime): DateTime
    {
        if ($turnaroundTime <= 0) throw new InvalidTurnaroundTimeException();

        $startDayOfWeek = intval($startDatetime->format("N")); // 1 = Monday, 7 = Sunday
        if ($startDayOfWeek >= 6) throw new NotWorkingHourException();

        $startTimeHour = intval($startDatetime->format("G")); // 0-23
        if ($startTimeHour < 9 || $startTimeHour >= 17) throw new NotWorkingHourException();

        return new DateTime();
    }


}