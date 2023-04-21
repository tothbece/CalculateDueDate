<?php

namespace Emarsys\Issues;

use DateTime;

class IssueManager {

    public static function calculateDueDate(DateTime $startDatetime, int $turnaroundTime): DateTime
    {
        return new DateTime();
    }
}