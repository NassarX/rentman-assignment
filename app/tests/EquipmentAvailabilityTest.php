<?php

use Assessment\Availability\Todo\EquimentAvailabilityHelperAssessment;
use PHPUnit\Framework\TestCase;
use DateTime;

class EquipmentAvailabilityTest extends TestCase
{
    private $equipmentAvailability;

    private $pdoMock;

    private $mockedTimeLine1 = array(
        array(
            "id" => "33",
            "date" => "2019-04-09",
            "planned_qty" => "4",
            "available_qty" => "10"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-10",
            "planned_qty" => "4",
            "available_qty" => "10"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-11",
            "planned_qty" => "4",
            "available_qty" => "10"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-12",
            "planned_qty" => "4",
            "available_qty" => "10"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-13",
            "planned_qty" => "4",
            "available_qty" => "10"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-14",
            "planned_qty" => "3",
            "available_qty" => "11"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-15",
            "planned_qty" => "3",
            "available_qty" => "11"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-16",
            "planned_qty" => "3",
            "available_qty" => "11"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-17",
            "planned_qty" => "9",
            "available_qty" => "5"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-18",
            "planned_qty" => "9",
            "available_qty" => "5"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-19",
            "planned_qty" => "3",
            "available_qty" => "11"
        ),
        array(
            "id" => "33",
            "date" => "2019-04-20",
            "planned_qty" => "7",
            "available_qty" => "7"
        )
    );

    private $mockedTimeLine2 = array(
        array(
            "id" => "14",
            "date" => "2019-04-14",
            "stock" => "2",
            "planned_qty" => "4",
            "available_qty" => "-2"
        ),
        array(
            "id" => "14",
            "date" => "2019-04-15",
            "stock" => "2",
            "planned_qty" => "4",
            "available_qty" => "-2"
        ),
        array(
            "id" => "14",
            "date" => "2019-04-16",
            "stock" => "2",
            "planned_qty" => "4",
            "available_qty" => "-2"
        )
    );


    protected function setUp(): void
    {
        // Create a mock PDO connection
        $this->pdoMock = $this->getMockBuilder(\PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dsn = "mysql:dbname=" . getenv('DATABASE_NAME') . ";host=" . getenv('PMA_HOST');
        try {
            $pdo = new PDO($dsn, getenv('PMA_USER'), getenv('MYSQL_ROOT_PASSWORD'));

            // Initialize the Equipment class before each test
            $this->equipmentAvailability = new \Assessment\Availability\Todo\EquimentAvailabilityHelperAssessment($pdo);
        } catch (PDOException $e) {
            // Handle database connection error
            echo "Failed to connect to database: " . $e->getMessage();
        }
    }


    // Mocked data for testing

    /**
     * Equipment:
     * | id | name                 | stock |
     * |----|----------------------|-------|
     * | 33 | BSS actieve Di Box  | 14    |
     *
     * Planning:
     *
     * | id | equipment | quantity | start | end |
     * | :--- | :--- | :--- | :--- | :--- |
     * | 776 | 33 | 3 | 2019-03-21 | 2019-03-29 |
     * | 779 | 33 | 1 | 2019-04-08 | 2019-04-13 |
     * | 322 | 33 | 3 | 2019-04-09 | 2019-04-18 |
     * | 773 | 33 | 3 | 2019-04-17 | 2019-04-18 |
     * | 772 | 33 | 3 | 2019-04-17 | 2019-04-21 |
     * | 777 | 33 | 4 | 2019-04-20 | 2019-04-27 |
     * | 325 | 33 | 3 | 2019-04-22 | 2019-04-30 |
     * | 327 | 33 | 1 | 2019-04-23 | 2019-05-03 |
     * | 329 | 33 | 4 | 2019-04-26 | 2019-04-27 |
     * | 780 | 33 | 2 | 2019-04-27 | 2019-05-02 |
     * | 330 | 33 | 4 | 2019-05-04 | 2019-05-05 |
     * | 324 | 33 | 3 | 2019-05-04 | 2019-05-06 |
     * | 774 | 33 | 3 | 2019-05-11 | 2019-05-21 |
     */


    public function test_equipment_timeline_works_as_expected()
    {
        // Arrange
        /**
         * | 2019-04-09 : 2019-04-13 | => planned : (1 + 3) -> 4, available : 10
         * | 2019-04-14 : 2019-04-16 | => planned : 3, available : 11
         * | 2019-04-17 : 2019-04-18 | => planned : (3 + 3 + 3) -> 9, available : 5
         * | 2019-04-19 | => planned : 3 , available : 11
         * | 2019-04-20 | 2019-04-21 | => planned : (3 + 4) -> 7, available : 7
         */

        // Test case 1: Test with valid parameters
        $startDate = (new DateTime('2019-04-09'))->format('Y-m-d');
        $endDate = (new DateTime('2019-04-20'))->format('Y-m-d');
        $equipmentId = 33;

        $timeline = $this->equipmentAvailability->getTimeLine($equipmentId, $startDate, $endDate);

        // Assert that the result is an array
        $this->assertIsArray($timeline);

        // Assert that the result has expected keys
        $this->assertArrayHasKey('id', $timeline[0]);
        $this->assertArrayHasKey('date', $timeline[0]);
        $this->assertArrayHasKey('planned_qty', $timeline[0]);
        $this->assertArrayHasKey('available_qty', $timeline[0]);

        // Assert that the 'id' key has expected value
        $this->assertEquals(33, $timeline[0]['id']);

        // Assert that the 'date' key has expected value
        $this->assertEquals('2019-04-09', $timeline[0]['date']);

        // Assert that the 'planned_qty' key has expected value
        $this->assertEquals(4, $timeline[0]['planned_qty']);

        // Assert that the 'available_qty' key is numeric
        $this->assertEquals(10, $timeline[0]['available_qty']);
    }


    // Test case for available quantity
    public function test_equipment_is_Available_works_as_expected()
    {

        // Arrange
        /**
         * | 2019-04-09 : 2019-04-13 | => planned : (1 + 3) -> 4, available : 10
         * | 2019-04-14 : 2019-04-16 | => planned : 3, available : 11
         * | 2019-04-17 : 2019-04-18 | => planned : (3 + 3 + 3) -> 9, available : 5
         * | 2019-04-19 | => planned : 3 , available : 11
         * | 2019-04-20 | 2019-04-21 | => planned : (3 + 4) -> 7, available : 7
         */

        $equipmentId = 33;
        $quantity = 5;
        $start = new DateTime('2019-04-14');
        $end = new DateTime('2019-04-16');

        // Mock the getTimeLine method on the helper object
        $helperMock = $this->getMockBuilder(EquimentAvailabilityHelperAssessment::class)
            ->setConstructorArgs([$this->pdoMock])
            ->setMethods(['getTimeLine'])
            ->getMock();

        // Set the mocked return value for getTimeLine method
        $helperMock->method('getTimeLine')->willReturn($this->mockedTimeLine1);

        // Call the isAvailable method on the mock object
        $availability = $helperMock->isAvailable($equipmentId, $quantity, $start, $end);

        // Assert that the availability is true
        $this->assertTrue($availability);
    }

    // Test case for available quantity
    public function test_GetShortages()
    {
        /**
         * | id | date | stock | planned\_qty | available\_qty |
         * | :--- | :--- | :--- | :--- | :--- |
         * | 14 | 2019-04-09 | 2 | 4 | -2 |
         * | 14 | 2019-04-10 | 2 | 8 | -6 |
         * | 14 | 2019-04-11 | 2 | 8 | -6 |
         * | 14 | 2019-04-12 | 2 | 4 | -2 |
         * | 14 | 2019-04-13 | 2 | 4 | -2 |
         * | 14 | 2019-04-14 | 2 | 4 | -2 |
         * | 14 | 2019-04-15 | 2 | 4 | -2 |
         * | 14 | 2019-04-16 | 2 | 4 | -2 |
         * | 14 | 2019-04-17 | 2 | 4 | -2 |
         * | 14 | 2019-04-18 | 2 | 6 | -4 |
         * | 14 | 2019-04-19 | 2 | 2 | 0 |
         * | 14 | 2019-04-20 | 2 | 2 | 0 |
         */
        $start = new DateTime('2019-04-14');
        $end = new DateTime('2019-04-16');

        // Mock the getTimeLine method on the helper object
        $helperMock = $this->getMockBuilder(EquimentAvailabilityHelperAssessment::class)
            ->setConstructorArgs([$this->pdoMock])
            ->setMethods(['getTimeLine'])
            ->getMock();

        // Mock the getEquipmentItems method on the helper object
//        $helperMock = $this->getMockBuilder(\Assessment\Availability\EquimentAvailabilityHelper::class)
//            ->setConstructorArgs([$this->pdoMock])
//            ->setMethods(['getEquipmentItems'])
//            ->getMock();

        // Set the mocked return value for getEquipmentItems method
        //$helperMock->method('getEquipmentItems')->willReturn($this->mockedTimeLine2);

        // Set the mocked return value for getTimeLine method
        $helperMock->method('getTimeLine')->willReturn($this->mockedTimeLine2);


        // Call the getShortages method on the mock object
        $shortages = $helperMock->getShortages($start, $end);

        // Assert the existence of key 14 and its value -2
        $this->assertArrayHasKey('14', $shortages);
        $this->assertEquals('-2', $shortages['14']);
    }
}