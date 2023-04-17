<?php

use Assessment\Availability\Solution\EquimentAvailabilityHelperSolution;
use Assessment\Availability\Todo\EquimentAvailabilityHelperAssessment;

include "../../bootstrap.php";

try {
//connect to the database
	$sDsn = "mysql:dbname=" . getenv('DATABASE_NAME') . ";host=" . getenv('PMA_HOST');
	$oDatabaseConnection = new PDO($sDsn, getenv('PMA_USER'), getenv('MYSQL_ROOT_PASSWORD'));

	//load right class
	if (getenv('USE_SOLUTION')) {
		if (class_exists('Assessment\\Solution\\EquimentAvailabilityHelperSolution')) {
			$oAssessment = new EquimentAvailabilityHelperSolution($oDatabaseConnection);
		} else {
			throw new Exception("Smart one! But of course the solution is not shipped");
		}
	} else {
		$oAssessment = new EquimentAvailabilityHelperAssessment($oDatabaseConnection);
	}

	//check if form is submitted
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		//yes, process
		header("Content-type: application/json");
		if (isset($_POST['equipment_id'], $_POST['quantity'], $_POST['start'], $_POST['end'])) {
			$aResponse = $oAssessment->isAvailable($_POST['equipment_id'], $_POST['quantity'], new DateTime($_POST['start']), new DateTime($_POST['end']));
		} else if (isset($_POST['start'], $_POST['end'])) {
			$aResponse = $oAssessment->getShortages(new DateTime($_POST['start']), new DateTime($_POST['end']));
		} else {
			throw new Exception("Invalid post request");
		}
		echo json_encode($aResponse, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
		exit;

	} elseif($_GET['readme']??false) {
		header("Content-Type: text/markdown");
		$sContents = file_get_contents("../../instructions/availability.md");
		if($_GET['download']??false){
			header('Content-Disposition: attachment; filename="availability.md"');
			header("Content-Length: ".strlen($sContents));
		}
		echo $sContents;
		exit;


	} elseif($_GET['json']??false) {
		header("Content-type: application/json");
		$aEquipment = array_column($oDatabaseConnection->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC),null,'id');
		$aEquipment = array_map(function(array $aRecord):array{
			$aRecord['id'] = intval($aRecord['id']);
			$aRecord['stock'] = intval($aRecord['stock']);
			$aRecord['planning'] = [];
			return $aRecord;
		},$aEquipment);

		foreach ($oDatabaseConnection->query("SELECT * FROM planning")->fetchAll(PDO::FETCH_ASSOC) as $aPlanning){
			$aPlanning['id'] = intval($aPlanning['id']);
			$aPlanning['equipment'] = intval($aPlanning['equipment']);
			$aPlanning['quantity'] = intval($aPlanning['quantity']);
			$aEquipment[$aPlanning['equipment']]['planning'][] = $aPlanning;
		}

		echo json_encode(array("equipment"=>$aEquipment), JSON_PRETTY_PRINT);
		exit;
	} else {

		//no, show form
		$sOptions = '';
		foreach ($oAssessment->getEquipmentItems() as $aItem) {
			$sOptions .= '<option value="' . $aItem['id'] . '">id: '.$aItem['id'].' - ' . htmlentities($aItem['name']) . "</option>" . PHP_EOL;
		}
		echo '<h3>Is available</h3>
	<form method="post">
		<table>
			<tr>
				<th>Equipment</th>
				<td>
					<select name="equipment_id">
						' . $sOptions . '
					</select>
				</td>
			</tr>
			<tr>
				<th>Quantity</th>
				<td><input name="quantity" type="number" min="0" step="1" value="1""></td>
			</tr>
			<tr>
				<th>Start</th>
				<td><input name="start" type="datetime-local" ></td>
			</tr>
			<tr>
				<th>End</th>
				<td><input name="end" type="datetime-local" ></td>
			</tr>
			<tr><td colspan="2"><input type="submit"></td></tr>
		</table>
	</form>';

		echo '<h3>Get shortages</h3>
	<form method="post">
		<table>
			<tr>
				<th>Start</th>
				<td><input name="start" type="datetime-local" ></td>
			</tr>
			<tr>
				<th>End</th>
				<td><input name="end" type="datetime-local" ></td>
			</tr>
			<tr><td colspan="2"><input type="submit"></td></tr>
		</table>
	</form>';

	}
}catch (Throwable $e){
	echo 'Error: '.$e->getMessage();
}

