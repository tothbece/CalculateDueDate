<?php

namespace Emarsys\Issues;

use DateTime;
use Exception;

class IssueManager {

    /**
     * @throws InvalidTurnaroundTimeException
     * @throws NotWorkingHourException
     * @throws Exception
     */
    public static function calculateDueDate(DateTime $startDatetime, int $turnaroundTime): DateTime
    {
        if ($turnaroundTime <= 0) throw new InvalidTurnaroundTimeException();

        $startDayOfWeek = intval($startDatetime->format("N")); // 1 = Monday, 7 = Sunday
        if ($startDayOfWeek >= 6) throw new NotWorkingHourException();

        $startTimeHour = intval($startDatetime->format("G")); // 0-23
        if ($startTimeHour < 9 || $startTimeHour >= 17) throw new NotWorkingHourException();

        $startTimestamp = $startDatetime->getTimestamp();
        $turnaroundTimeInMinutes = $turnaroundTime*60;
        if ($turnaroundTimeInMinutes < self::minutesUntilEndOfDay($startDatetime)) {
            return (new DateTime())->setTimestamp($startTimestamp+$turnaroundTimeInMinutes*60);
        }
        $endTimestamp = $startTimestamp;
        while ($turnaroundTimeInMinutes >= self::minutesUntilEndOfDay($startDatetime)) {
            $endTimestamp += 24*60*60;
            $turnaroundTimeInMinutes -= 8*60;
        }
        $endTimestamp += $turnaroundTimeInMinutes * 60;
        return (new DateTime())->setTimestamp($endTimestamp);

    }

    /**
     * @throws Exception
     */
    private static function minutesUntilEndOfDay(DateTime $dateTime): int {
        $date = $dateTime->format("Y-m-d");
        $endOfDay = new DateTime($date." 16:00");
        $deltaInSeconds = $endOfDay->getTimestamp()-$dateTime->getTimestamp();
        return intval($deltaInSeconds/60);
    }

}