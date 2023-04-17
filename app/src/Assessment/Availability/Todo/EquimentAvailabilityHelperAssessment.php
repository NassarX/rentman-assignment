<?php
namespace Assessment\Availability\Todo;

use Assessment\Availability\EquimentAvailabilityHelper;
use DateTime;
use Exception;

class EquimentAvailabilityHelperAssessment extends EquimentAvailabilityHelper {

	/**
	 * This function checks if a given quantity is available in the passed time frame
	 * @param int      $equipment_id Id of the equipment item
	 * @param int      $quantity How much should be available
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return bool True if available, false otherwise
	 */
	public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end) : bool {
		throw new Exception("This is not implemented yet");
	}

	/**
	 * Calculate all items that are short in the given period
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return array Key/valyue array with as indices the equipment id's and as values the shortages
	 */
	public function getShortages(DateTime $start, DateTime $end) : array {
		throw new Exception("This is not implemented yet");
	}

}
