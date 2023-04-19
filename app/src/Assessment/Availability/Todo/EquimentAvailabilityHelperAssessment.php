<?php

namespace Assessment\Availability\Todo;

use Assessment\Availability\EquimentAvailabilityHelper;
use DateTime;
use Exception;
use PDO;
use PDOException;

class EquimentAvailabilityHelperAssessment extends EquimentAvailabilityHelper
{

    /**
     * This function checks if a given quantity is available in the passed time frame
     * @param int $equipment_id Id of the equipment item
     * @param int $quantity How much should be available
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return bool True if available, false otherwise
     */
    public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end): bool
    {
        // first moment of the day
        $startDate = $start->setTime(0, 0, 0)->format('Y-m-d h:i:s');

        // last moment of the dat
        $endDate = $end->setTime(23, 59, 59)->format('Y-m-d h:i:s');

        $isAvailable = true;
        $equipmentTimeLine = $this->getTimeLine($equipment_id, $startDate, $endDate);
        $result = array();

        // Iterate through the array of equipment data
        foreach ($equipmentTimeLine as $row) {
            $date = $row["date"];
            $availableQty = $row["available_qty"];
            $plannedQty = $row["planned_qty"];

            // Check if the available qty is less than the required qty
            if ($availableQty < $quantity) {
                $isAvailable = false; // Set the flag to false
                $result[] = "On {$date}, planned: {$plannedQty}, available qty: {$availableQty}, sufficient: false"; //debugging
                break; // Break out of the loop early, as we only need to know if any value is less than required qty
            }
            // Output the data for debugging purposes and demonstration of the algorithm, to be removed later
            $output = "On {$date}, planned: {$plannedQty}, available qty: {$availableQty}, sufficient: true";
            $result[] = $output;
        }
        print_r($result);

        return $isAvailable;
    }

    /**
     * Calculate all items that are short in the given period
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return array Key/value array with as indices the equipment id's and as values the shortages
     */
    public function getShortages(DateTime $start, DateTime $end): array
    {
        // first moment of the day
        $startDate = $start->setTime(0, 0, 0)->format('Y-m-d h:i:s');

        // last moment of the dat
        $endDate = $end->setTime(23, 59, 59)->format('Y-m-d h:i:s');

        $equipments = $this->getEquipmentItems();

        $equipmentIds = array_column($equipments, 'id');

        $shortages = [];
        $shortages_debug = [];
        foreach ($equipmentIds as $equipmentId) {
            $equipmentTimeLine = $this->getTimeLine($equipmentId, $startDate, $endDate);

            // Iterate through the equipment timelines and identify shortages
            foreach ($equipmentTimeLine as $item) {
                if ($item['available_qty'] < 0) {
                    $shortages[$equipmentId] = $item['available_qty'];
                    $shortages_debug[$equipmentId][] = "On {$item['date']}, shortage: {$item['available_qty']}"; //debugging
                    //break; we can break out of the loop here on first shortage we found, but I'm leaving it in for debugging purposes
                }
            }
        }
        print_r($shortages_debug);

        return $shortages;
    }

    /**
     * Get equipment timeline date, planned qty & available qty
     * It goes beyond just checking availablity but to return a timeline of equipment stats
     *
     * @param int $equipment_id
     * @param string $start
     * @param string $end
     * @return array
     */
    public function getTimeLine(int $equipment_id, string $start, string $end): array
    {
        try {
            $dbConnection = $this->getDatabaseConnection();
            // Prepare the stored procedure call
            $stmt = $dbConnection->prepare("CALL get_equipment_timeline(:start, :end, :id)");
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            $stmt->bindParam(':id', $equipment_id, PDO::PARAM_INT);
            $stmt->execute();

            $equipmentTimeLine = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

        } catch (PDOException $e) {
            die("Error occurred:" . $e->getMessage());
        }

        return $equipmentTimeLine;
    }
}
