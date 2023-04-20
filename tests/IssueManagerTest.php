<?php

namespace Emarsys\Issues\Test;
use DateTime;
use Emarsys\Issues\IssueManager;
use Emarsys\Issues\NotWorkingHourException;
use Emarsys\Issues\InvalidTurnaroundTimeException;
use PHPUnit\Framework\TestCase;

class IssueManagerTest extends TestCase {
    private IssueManager $issueManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->issueManager = new IssueManager();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->issueManager);
    }

    public function workingHourIssuesProvider(): array
    {
        return array(
          array(new DateTime("2023-04-17 10:15"), 2, new DateTime("2023-04-17 12:15")), // from Monday, 2h
          array(new DateTime("2023-04-17 10:15"), 8, new DateTime("2023-04-18 10:15")), // from Monday, 8h
          array(new DateTime("2023-04-17 10:15"), 16, new DateTime("2023-04-19 10:15")), // from Monday, 16h
          array(new DateTime("2023-04-20 10:15"), 16, new DateTime("2023-04-24 10:15")), // from Thursday, 16h
        );
    }

    /**
     * @test
     * @dataProvider workingHourIssuesProvider
     */
    public function testShouldCalculateWithinWorkingHours($start, $turnaroundTime, $expectedEnd) {
        $dateTimeResult = $this->issueManager::calculateDueDate($start, $turnaroundTime);
        $this->assertEquals($expectedEnd->getTimestamp(), $dateTimeResult->getTimestamp());
    }

    public function notWorkingHourIssuesProvider(): array
    {
        return array(
            array(new DateTime("2023-04-17 7:12"), 2), // from Monday, but too early
            array(new DateTime("2023-04-17 8:59"), 7), // from Monday, but too early
            array(new DateTime("2023-04-17 17:00"), 16), // from Monday, but too late
            array(new DateTime("2023-04-17 21:41"), 4), // from Monday, but too late
            array(new DateTime("2023-04-22 13:10"), 4), // from Saturday
            array(new DateTime("2023-04-23 10:10"), 3), // from Sunday
        );
    }

    /**
     * @dataProvider notWorkingHourIssuesProvider
     * @test
     */
    public function testShouldThrowNotWorkingHoursException($start, $turnaroundTime) {
        $this->expectException(NotWorkingHourException::class);
        $this->issueManager::calculateDueDate($start, $turnaroundTime);
    }

    public function invalidTurnaroundTimeProvider(): array
    {
        return array(
            array(new DateTime("2023-04-17 9:00"), 0), // turnaround time shouldn't be zero
            array(new DateTime("2023-04-17 9:00"), -1), // turnaround time shouldn't be negative
        );
    }

    /**
     * @dataProvider invalidTurnaroundTimeProvider
     * @test
     */
    public function testShouldThrowInvalidTurnaroundTimeException($start, $turnaroundTime) {
        $this->expectException(InvalidTurnaroundTimeException::class);
        $this->issueManager::calculateDueDate($start, $turnaroundTime);
    }
}