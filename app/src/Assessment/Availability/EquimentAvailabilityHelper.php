<?php
namespace Assessment\Availability;

use DateTime;
use PDO;

abstract class EquimentAvailabilityHelper {

	/**
	 * EquimentAvailabilityHelper constructor.
	 * @param PDO $oDatabaseConnection
	 */
	public function __construct(private PDO $oDatabaseConnection) {

	}

	/**
	 * Get the already opened connection to the assessment database
	 * @return PDO
	 */
	public final function getDatabaseConnection() : PDO{
		return $this->oDatabaseConnection;
	}


	public final function getEquipmentItems() : array{
		$aRows = $this->oDatabaseConnection->query("SELECT * FROM equipment ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
		return array_column($aRows, null, 'id');
	}

	/**
	 * This function checks if a given quantity is available in the passed time frame
	 * @param int      $equipment_id Id of the equipment item
	 * @param int      $quantity How much should be available
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return bool True if available, false otherwise
	 */
	abstract public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end) : bool;

	/**
	 * Calculate all items that are short in the given period
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return array Key/valyue array with as indices the equipment id's and as values the shortages
	 */
	abstract public function getShortages(DateTime $start, DateTime $end) : array;

}
