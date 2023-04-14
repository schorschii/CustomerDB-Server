<?php
/* Recommended Cleanup Commands:
   php console.php housekeeping 365 1   -   purge users not logged in since one year and no uploaded data
   php console.php housekeeping 730 0   -   purge users not logged in since two years, no matter if they have uploaded data
*/

class housekeeping {

	private $dbh;
	private $stmt;

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function cleanup(int $inactiveSinceDays, bool $onlyIfNoEntries) {
		if($onlyIfNoEntries) {
			$this->stmt = $this->dbh->prepare(
				'DELETE c FROM Client c WHERE (SELECT COUNT(id) FROM Customer WHERE client_id = c.id) = 0 AND (SELECT COUNT(id) FROM Voucher WHERE client_id = c.id) = 0 AND last_login < NOW() - INTERVAL '.($inactiveSinceDays).' DAY'
			);
		} else {
			$this->stmt = $this->dbh->prepare(
				'DELETE c FROM Client c WHERE last_login < NOW() - INTERVAL '.($inactiveSinceDays).' DAY'
			);
		}
		if(!$this->stmt->execute()) return false;
		echo 'Purged '.$this->stmt->rowCount().' client records'."\n";
		return true;
	}

}
