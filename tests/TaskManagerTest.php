<?php

namespace Emarsys\Tasks\Test;
use Emarsys\Tasks\TaskManager;
use Emarsys\Tasks\NotWorkingHourException;
use PHPUnit\Framework\TestCase;

class TaskManagerTest extends TestCase {
    private TaskManager $taskManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->taskManager = new TaskManager();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->taskManager);
    }

    public function workingHourTasksProvider(): array
    {
        return array(
          array("2023-04-17 10:15", 2, "2023-04-17 12:15"), // from Monday, 2h
          array("2023-04-17 10:15", 8, "2023-04-18 10:15"), // from Monday, 8h
          array("2023-04-17 10:15", 16, "2023-04-19 10:15"), // from Monday, 16h
          array("2023-04-20 10:15", 16, "2023-04-24 10:15"), // from Thursday, 16h
        );
    }

    /**
     * @test
     * @dataProvider workingHourTasksProvider
     */
    public function testShouldCalculateWithinWorkingHours($start, $turnaroundTime, $expectedEnd) {
        $dateTimeResult = $this->taskManager->calculateDueDate($start, $turnaroundTime);
        $formattedResult = $dateTimeResult->format("Y-m-d H:i");
        $this->assertEquals($expectedEnd, $formattedResult);
    }

    public function notWorkingHourTasksProvider(): array
    {
        return array(
            array("2023-04-17 7:12", 2), // from Monday, but too early
            array("2023-04-17 8:59", 7), // from Monday, but too early
            array("2023-04-17 17:00", 16), // from Monday, but too late
            array("2023-04-17 21:41", 4), // from Monday, but too late
            array("2023-04-22 13:10", 4), // from Saturday
            array("2023-04-23 10:10", 3), // from Sunday
        );
    }

    /**
     * @dataProvider notWorkingHourTasksProvider
     * @test
     */
    public function testShouldThrowNotWorkingHoursException($start, $turnaroundTime) {
        $this->expectException(NotWorkingHourException::class);
        $this->taskManager->calculateDueDate($start, $turnaroundTime);
    }
}