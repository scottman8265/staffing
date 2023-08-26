<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 5:25 AM
 */

require_once('Process.php');

class Arrays {
	public $branchArray;
	public $twoDigitArray;
	public $execArray;
	public $jcmsTestArray;
	public $teamMemberArray;
	public $staffingPosArray;
	public $jcmsTestsByPosition;
	public $branchString;
	private $lnk;


	public function __construct() {

		$this->lnk = new Process();

	}

	public function setBranchArray($branches = null) {

		$arr = [];

		$sql = "SELECT branchNum, branchName, regional, director, location, posProfile, target FROM branchInfo.branches";

		if (!is_null($branches)) {
			$sql                .= " WHERE branchNum IN (" . $branches . ")";
			$this->branchString = $branches;
		}

		$qry = $this->lnk->query($sql);

		foreach ($qry as $x) {
			$arr[$x['branchNum']] = ['bName'      => $x['branchName'],
			                         'regional'   => $x['regional'],
			                         'director'   => $x['director'],
			                         'location'   => $x['location'],
			                         'target'     => $x['target'],
			                         'posProfile' => $x['posProfile']];
		}
		$this->branchArray = $arr;
	}

	public function setTeamMemberArray($branches = null) {

		$arr = [];

		$sql = "SELECT position, empID, dateInPos, tmName FROM staffing.branchStaffing";

		if (!is_null($branches)) {
			$sql .= " WHERE branchNum IN (" . $branches . ")";
		}

		$sql .= " ORDER BY branchNum";

		$qry = $this->lnk->query($sql);

		foreach ($qry as $x) {

			$arr[$x['empID']] = ['pos'    => $x['position'],
			                     'dateIn' => $x['dateInPos'],
			                     'name'   => $x['tmName']];


		}

		$this->teamMemberArray = $arr;
	}

	public function setStaffingPosArray() {
		$arr = [];
		$qry = $this->lnk->query("SELECT posID, posName, jcmsTests, jcmsFrequency, jcmsGracePeriod FROM staffing.positions");

		foreach ($qry as $x) {
			$arr[$x['posID']] = ['posName'         => $x['posName'],
			                     'jcmsTests'       => $x['jcmsTests'],
			                     'jcmsFrequency'   => $x['jcmsFrequency'],
			                     'jcmsGracePeriod' => $x['jcmsGracePeriod']];
		}

		$this->staffingPosArray = $arr;
	}

	public function setTwoDigitArray() {
		$arr = [];
		$sql = "SELECT branchNum, _2DigNum FROM branchInfo.branches WHERE _2DigNum IS NOT NULL";
		$qry = $this->lnk->query($sql);

		foreach ($qry as $x) {
			$arr[$x['_2DigNum']] = $x['branchNum'];
		}

		$this->twoDigitArray = $arr;
	}

	public function setExecArray() {
		$arr = [];

		$sql = "SELECT regionID, fName, lName, eMail, position, login FROM branchInfo.opsExecs WHERE active = 1";
		$qry = $this->lnk->query($sql);

		foreach ($qry as $x) {
			$arr[$x['regionID']] = ['fName'    => $x['fName'],
			                        'lName'    => $x['lName'],
			                        'email'    => $x['email'],
			                        'position' => $x['position'],
			                        'login'    => $x['login']];
		}

		$this->execArray = $arr;
	}

	public function setJCMSTestArr() {

		$arr = [];

		$qry = $this->lnk->query("SELECT displayName, testName, testID FROM branchInfo.jcmsTestinfo WHERE testID < 16");

		foreach ($qry as $x) {
			$arr[$x['testID']] = ['testName' => $x['testName'], 'displayName' => $x['displayName']];
		}

		$this->jcmsTestArray = $arr;
	}

	public function setJCMSTestsByPosition() {
		$arr = [];

		$qry = $this->lnk->query("SELECT posID, jcmsTests, jcmsFrequency, jcmsGracePeriod FROM staffing.positions WHERE jcmsTESTS IS NOT NULL");

		foreach ($qry as $x) {
			if (strlen($x['jcmsTests'] >= 1)) {
				$testIDs = explode(":", $x['jcmsTests']);
				foreach ($testIDs as $y) {
					if (isset($tests[$y]['testName'])) {
						$arr[$x['posID']][] = ['testID'      => $y,
						                       'frequency'   => $x['jcmsFrequency'],
						                       'gracePeriod' => $x['jcmsGracePeriod']];
					}
				}
			}
		}

		return $arr;

	}

	public function getBranchArray() {
		return $this->branchArray;
	}

	public function getBranchName($branch) {
		return $this->branchArray[$branch]['bName'];
	}

	public function getTmName($empID) {
		return $this->teamMemberArray[$empID]['name'];
	}

	public function getTmDateInPos($empID) {
		return $this->teamMemberArray[$empID]['dateIn'];
	}

	public function getTmPosition($empID) {
		return $this->teamMemberArray[$empID]['pos'];
	}

	public function getPosName($posID) {
		return $this->staffingPosArray[$posID]['posName'];
	}

	public function getTmNameFromAod($empID) {
		$qry = $this->lnk->query("SELECT concat(aodFname, ' ', aodLname) as name FROM staffing.aodData WHERE aodEmpID = " . $empID);
		return $qry ? $qry[0]['name'] : 'No Name Found';
	}

	public function getSftyTpWkNum() {
		$today = new DateTime();
		$dayOfWeek = $today->format('w');


		switch ($dayOfWeek) {
			case 5:
			case 6:
			case 0:
				return $today->format("W");
				break;
			default:
				return $today->format("W") - 1;
		}
	}

}