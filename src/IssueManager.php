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

        $startDayOfWeek = self::dayOfWeekFromTimestamp($startDatetime->getTimestamp());
        if ($startDayOfWeek >= 6) throw new NotWorkingHourException();

        $startTimeHour = intval($startDatetime->format("G")); // 0-23
        if ($startTimeHour < 9 || $startTimeHour >= 17) throw new NotWorkingHourException();

        $startTimestamp = $startDatetime->getTimestamp();
        $turnaroundTimeInMinutes = $turnaroundTime*60;
        $endTimestamp = $startTimestamp;

        while ($turnaroundTimeInMinutes >= self::minutesUntilEndOfDay($startDatetime)) {
            $endTimestamp += 24*60*60; // add 1 day
            $turnaroundTimeInMinutes -= 8*60; // remove 8 hours of needed time
            // checking if we reached a weekend (Saturday)
            if ( self::dayOfWeekFromTimestamp($endTimestamp) == 6 )
                $endTimestamp += 2*24*60*60; // shift to Monday
        }
        $endTimestamp += $turnaroundTimeInMinutes * 60; // adding remaining minutes
        // NOTE: it could be negative if we jumped too further in time
        //       when skipping not working hours

        return (new DateTime())->setTimestamp($endTimestamp);

    }

    /**
     * @throws Exception
     */
    private static function minutesUntilEndOfDay(DateTime $dateTime): int {
        $date = $dateTime->format("Y-m-d");
        $endOfDay = new DateTime($date." 17:00");
        $deltaInSeconds = $endOfDay->getTimestamp()-$dateTime->getTimestamp();
        return intval($deltaInSeconds/60);
    }


    // 1 = Monday, 7 = Sunday
    private static function dayOfWeekFromTimestamp(int $timestamp): int {
        return intval((new DateTime())->setTimestamp($timestamp)->format("N"));
    }

}