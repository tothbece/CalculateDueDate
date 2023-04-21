<?php

namespace Emarsys\Issues;

use DateTime;

class IssueManager {

    /**
     * @throws InvalidTurnaroundTimeException
     */
    public static function calculateDueDate(DateTime $startDatetime, int $turnaroundTime): DateTime
    {
        if ($turnaroundTime <= 0) throw new InvalidTurnaroundTimeException();
        return new DateTime();
    }
}