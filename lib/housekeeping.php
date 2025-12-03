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

	function cleanupInactiveUsers(int $sinceDays, bool $onlyIfNoEntries) {
		$this->stmt = $this->dbh->prepare(
			'DELETE c FROM Client c
			WHERE (last_login IS NULL OR last_login < NOW() - INTERVAL '.$sinceDays.' DAY)
			AND pending_activation_token IS NULL '
			.($onlyIfNoEntries
				? 'AND (SELECT COUNT(id) FROM Voucher WHERE client_id = c.id) = 0
				   AND (SELECT COUNT(id) FROM Customer WHERE client_id = c.id) = 0'
				: '')
		);
		if(!$this->stmt->execute()) return false;
		return $this->stmt->rowCount();
	}

	function cleanupUnverifiedUsers(int $sinceDays) {
		$this->stmt = $this->dbh->prepare(
			'DELETE FROM Client WHERE pending_activation_token IS NOT NULL AND created < DATE_SUB(NOW(), INTERVAL '.$sinceDays.' DAY)'
		);
		$this->stmt->execute();
		return $this->stmt->rowCount();
	}

}
