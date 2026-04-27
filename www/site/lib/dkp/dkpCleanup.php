<?php
/*===========================================================
CLASS DESCRIPTION
Provides static utility methods for cleaning up stale guild
and user data from the WebDKP database.
============================================================*/

enum DeleteCount: string {
	case PointHistory     = 'pointhistory';
	case Awards           = 'awards';
	case Points           = 'points';
	case LootTableData    = 'loottable_data';
	case LootTableSection = 'loottable_section';
	case LootTable        = 'loottable';
	case Settings         = 'settings';
	case Tables           = 'tables';
	case RemoteCustom     = 'remote_custom';
	case Users            = 'users';
	case GuildsUnclaimed  = 'guilds_unclaimed';
	case GuildsDeleted    = 'guilds_deleted';
	case Accounts = 'accounts';
	case AccountPermissions = 'account_permissions';
}

class dkpCleanup {

	private static $DELETE_BATCH_SIZE = 5000;
	private static $DELETE_GUILD_BATCH_SIZE = 1000;
	private static $EMPTY_COUNTS = [
			DeleteCount::PointHistory->value     => 0,
			DeleteCount::Awards->value           => 0,
			DeleteCount::Points->value           => 0,
			DeleteCount::LootTableData->value    => 0,
			DeleteCount::LootTableSection->value => 0,
			DeleteCount::LootTable->value        => 0,
			DeleteCount::Settings->value         => 0,
			DeleteCount::Tables->value           => 0,
			DeleteCount::RemoteCustom->value     => 0,
			DeleteCount::Users->value            => 0,
			DeleteCount::GuildsUnclaimed->value  => 0,
			DeleteCount::GuildsDeleted->value    => 0,
			DeleteCount::Accounts->value         => 0,
			DeleteCount::AccountPermissions->value => 0,
	];

	/*===========================================================
	Identifies and deletes all data belonging to stale guilds and
	users. A guild is stale when every security_users owner has
	not logged in within the stale interval. Returns an HTML log.

	@param bool $dryrun  Count rows to be deleted but do nothing.
	@return string       HTML log of actions taken / would be taken.
	============================================================*/
	static function deleteOldData($dryrun = false) {
		global $sql;
		$staleInterval = "5 YEAR";
		$DELETE_BATCH_SIZE     = self::$DELETE_BATCH_SIZE;
		$DELETE_GUILD_BATCH_SIZE = self::$DELETE_GUILD_BATCH_SIZE;

		$log = '';
		$log .= "<b>=== WebDKP Old Data Cleanup ===</b><br>";
		$log .= "Started: " . date('Y-m-d H:i:s') . "<br>";
		$log .= "Mode: <b>" . ($dryrun ? "DRY RUN (no deletes will be performed)" : "LIVE DELETE") . "</b><br>";
		$log .= "<br>";

		// Find all stale guilds. We're looking for guilds where NO user has signed in for the stale interval.
		$result = $sql->Query("
			SELECT g.id FROM dkp_guilds g
			JOIN security_users su ON su.guild = g.id
			WHERE NOT EXISTS (
				SELECT 1 FROM security_users active
				WHERE active.guild = g.id
				AND active.lastlogin >= NOW() - INTERVAL $staleInterval
			)
			GROUP BY g.id
		");
		$staleGuildIds = [];
		while ($row = mysqli_fetch_array($result)) {
			$staleGuildIds[] = (int)$row['id'];
		}
		$staleGuildCount = count($staleGuildIds);
		$totalClaimedGuilds = $sql->QueryItem("SELECT COUNT(*) FROM dkp_guilds WHERE claimed = 1");

		// Delete stale guilds 
		$log .= "<b>--- Phase 1: Deleting Guild Data ---</b><br>";
		$log .= "Stale guilds (all owners inactive 5+ years): $staleGuildCount<br>";
		$log .= "There are currently $totalClaimedGuilds claimed guilds in the system. This will delete " . round(($staleGuildCount / $totalClaimedGuilds) * 100, 2) . "% of claimed guilds.<br>";
		$log .= "All will be unclaimed. Guilds with no remaining dkp_users references will also be deleted (count available after live run).<br>";
		$log .= "<br>";
		$deleteCounts = self::$EMPTY_COUNTS;
		$totals = dkpCleanup::getExistingEntityTotals();

		foreach (array_chunk($staleGuildIds, $DELETE_GUILD_BATCH_SIZE) as $chunk) {
			dkpCleanup::deleteGuildData($chunk, $dryrun, $deleteCounts, $log);
		}
	
		// Delete dkp_userpermissions for stale security_users
		$log .= "<b>--- Deleting dkp_userpermissions for stale users ---</b><br>";
		if ($dryrun) {
			$permTotal = $sql->QueryItem("SELECT COUNT(*) FROM dkp_userpermissions WHERE user IN (SELECT id FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval)");
			$deleteCounts[DeleteCount::AccountPermissions->value] += $permTotal;
		} else {
			do {
				$sql->Query("DELETE FROM dkp_userpermissions WHERE user IN (SELECT id FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval) LIMIT $DELETE_BATCH_SIZE");
				$deleteCounts[DeleteCount::AccountPermissions->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// Delete stale security_users
		$log .= "<b>--- Deleting stale security_users ---</b><br>";
		if ($dryrun) {
			$secUsersTotal = $sql->QueryItem("SELECT COUNT(*) FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval");
			$deleteCounts[DeleteCount::Accounts->value] += $secUsersTotal;
		} else {
			do {
				$sql->Query("DELETE FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval LIMIT $DELETE_BATCH_SIZE");
				$deleteCounts[DeleteCount::Accounts->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}
	
		$log .= "<b>--- Phase 2: Cleaning Up Orphans ---</b><br>";
		if (!$dryrun) {
			dkpCleanup::cleanupOrphans(false, $deleteCounts, $log);
			dkpCleanup::updateServerTotals($log);
		}

		$log .= "<b>--- Phase 3: Summary---</b><br>";
		dkpCleanup::logDeleteCounts($deleteCounts, $totals, $dryrun, $log);

		return $log;
	}

	/**
	 * Populates an array with the counts of the total number of rows in various tables.
	 * Used for calculating delete percentages.
	 */
	private static function getExistingEntityTotals() {
		global $sql;
		$totals = self::$EMPTY_COUNTS;
		$totals[DeleteCount::PointHistory->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_pointhistory");
		$totals[DeleteCount::Awards->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_awards");
		$totals[DeleteCount::Points->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_points");
		$totals[DeleteCount::LootTable->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable");
		$totals[DeleteCount::LootTableData->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable_data");
		$totals[DeleteCount::LootTableSection->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable_section");
		$totals[DeleteCount::Settings->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_settings");
		$totals[DeleteCount::Tables->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_tables");
		$totals[DeleteCount::RemoteCustom->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_remote_custom");
		$totals[DeleteCount::Users->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_users");
		$totals[DeleteCount::GuildsUnclaimed->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_guilds");
		$totals[DeleteCount::GuildsDeleted->value] = $totals[DeleteCount::GuildsUnclaimed->value];
		$totals[DeleteCount::Accounts->value] = $sql->QueryItem("SELECT COUNT(*) FROM security_users");
		$totals[DeleteCount::AccountPermissions->value] = $sql->QueryItem("SELECT COUNT(*) FROM dkp_userpermissions");
		return $totals;
	}

	/*
	Deletes all data associated with a given list of guild IDs.
	Cascades through every child table in dependency order, then
	unclaims/deletes the guild rows themselves.

	Safe to call with any list of IDs. If $guildIds is empty,
	returns immediately with zero counts.

	Note on dkp_users: rows are only deleted when no dkp_points
	reference the user in any guild. In a multi-batch scenario
	(e.g. deleteOldData chunking through many stale guilds) a
	user who spans multiple batches may survive until all their
	dkp_points have been removed across prior batches.

	@param array $guildIds  Integer guild IDs to delete data for.
	@param mixed &$log      Optional HTML log string to append to.
	@return array           Deletion counts keyed by DeleteCount->value.
	*/
	static function deleteGuildData(array $guildIds, $dryrun = true, &$counts = null, &$log = '') {
		global $sql;
		$DELETE_BATCH_SIZE = self::$DELETE_BATCH_SIZE;

		if ($counts === null) {
			$counts = self::$EMPTY_COUNTS;
		}

		if (empty($guildIds)) {
			return;
		}

		$idList    = implode(',', array_map('intval', $guildIds));
		$toDeleteCount = count($guildIds);
		$log .= "Deleting data for $toDeleteCount guilds<br>";

		// dkp_pointhistory — largest table, log each batch
		if ($dryrun) {
			$counts[DeleteCount::PointHistory->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_pointhistory WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_pointhistory WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::PointHistory->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// dkp_awards
		if ($dryrun) {
			$counts[DeleteCount::Awards->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_awards WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_awards WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Awards->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// dkp_points
		if ($dryrun) {
			$counts[DeleteCount::Points->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_points WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_points WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Points->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		dkpCleanup::deleteGuildLootTables($idList, $dryrun, $counts, $log);

		// dkp_settings
		if ($dryrun) {
			$counts[DeleteCount::Settings->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_settings WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_settings WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Settings->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// dkp_tables
		if ($dryrun) {
			$counts[DeleteCount::Tables->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_tables WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_tables WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Tables->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// dkp_remote_custom
		if ($dryrun) {
			$counts[DeleteCount::RemoteCustom->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_remote_custom WHERE guild IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_remote_custom WHERE guild IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::RemoteCustom->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// dkp_users — only those with no remaining dkp_points (anywhere, including in other guilds)
		if ($dryrun) {
			$counts[DeleteCount::Users->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_users WHERE guild IN ($idList) AND NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_users WHERE guild IN ($idList) AND NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Users->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		// Unclaim guilds — names are shared across the system so we can't always delete them
		if ($dryrun) {
			$counts[DeleteCount::GuildsUnclaimed->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_guilds WHERE id IN ($idList)");
		} else {
			$sql->Query("UPDATE dkp_guilds SET claimed = 0 WHERE id IN ($idList)");
			$counts[DeleteCount::GuildsUnclaimed->value] += $sql->a_rows;
		}

		// Delete guilds that have no remaining dkp_users references
		if ($dryrun) {
			$counts[DeleteCount::GuildsDeleted->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_guilds WHERE id IN ($idList) AND NOT EXISTS (SELECT 1 FROM dkp_users u WHERE u.guild = dkp_guilds.id)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_guilds WHERE id IN ($idList) AND NOT EXISTS (SELECT 1 FROM dkp_users u WHERE u.guild = dkp_guilds.id) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::GuildsDeleted->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}
	}

	private static function deleteGuildLootTables($idList, $dryrun = true, &$counts = null, &$log = '') {
		global $sql;

		if ($counts === null) {
			$counts = self::$EMPTY_COUNTS;
		}

		// dkp_loottable_data (child of dkp_loottable)
		if ($dryrun) {
			$wouldDelete = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable_data WHERE loottable IN (SELECT id FROM dkp_loottable WHERE guild IN ($idList))");
			$counts[DeleteCount::LootTableData->value] += $wouldDelete;
		} else {
			$sql->Query("DELETE FROM dkp_loottable_data WHERE loottable IN (SELECT id FROM dkp_loottable WHERE guild IN ($idList))");
			$counts[DeleteCount::LootTableData->value] += $sql->a_rows;
		}

		// dkp_loottable_section (child of dkp_loottable)
		if ($dryrun) {
			$wouldDelete = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable_section WHERE loottable IN (SELECT id FROM dkp_loottable WHERE guild IN ($idList))");
			$counts[DeleteCount::LootTableSection->value] += $wouldDelete;
		} else {
			$sql->Query("DELETE FROM dkp_loottable_section WHERE loottable IN (SELECT id FROM dkp_loottable WHERE guild IN ($idList))");
			$counts[DeleteCount::LootTableSection->value] += $sql->a_rows;
		}

		// dkp_loottable
		if ($dryrun) {
			$wouldDelete = $sql->QueryItem("SELECT COUNT(*) FROM dkp_loottable WHERE guild IN ($idList)");
			$counts[DeleteCount::LootTable->value] += $wouldDelete;
		} else {
			$sql->Query("DELETE FROM dkp_loottable WHERE guild IN ($idList)");
			$counts[DeleteCount::LootTable->value] += $sql->a_rows;
		}

		return $counts;
	}
	

	/**
	 * Scans for dkp_users and dkp_guilds that are unreferenced by anything else and deletes them.
	 * A final cleanup state after doing bulk deletes as accounts, users, and guilds can have cross references between them
	 * that cause them to be missed when doing targeted deletes.
	 */	
	private static function cleanupOrphans($dryrun = false, &$counts = null, &$log = '') {
		$DELETE_BATCH_SIZE = self::$DELETE_BATCH_SIZE;
		global $sql;
		
		if ($counts === null) {
			$counts = self::$EMPTY_COUNTS;
		}

		// dkp_users with no dkp_points entries are in no DKP table and can be removed.
		$log .= "<b>--- Deleting orphaned dkp_users ---</b><br>";
		$orphanedUsersDeleted = 0;
		if ($dryrun) {
			$orphanedUsersDeleted = $sql->QueryItem("SELECT COUNT(*) FROM dkp_users WHERE NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id)");
			$counts[DeleteCount::Users->value] += $orphanedUsersDeleted;	
		} else {
			do {
				$sql->Query("DELETE FROM dkp_users WHERE NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id) LIMIT $DELETE_BATCH_SIZE");
				$orphanedUsersDeleted += $sql->a_rows;
				$counts[DeleteCount::Users->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}
		$log .= "Cleanup: $orphanedUsersDeleted dkp_users rows deleted<br><br>";

		// Unclaimed guilds with no users can be fully removed.
		$log .= "<b>--- Deleting orphaned dkp_guilds ---</b><br>";
		$orphanedGuildsDeleted = 0;
		if ($dryrun) {
			$orphanedGuildsDeleted = $sql->QueryItem("SELECT COUNT(*) FROM dkp_guilds WHERE claimed = 0 AND NOT EXISTS (SELECT 1 FROM dkp_users u WHERE u.guild = dkp_guilds.id)");
			$counts[DeleteCount::GuildsDeleted->value] += $orphanedGuildsDeleted;
		} else {
			do {
				$sql->Query("DELETE FROM dkp_guilds WHERE claimed = 0 AND NOT EXISTS (SELECT 1 FROM dkp_users u WHERE u.guild = dkp_guilds.id) LIMIT $DELETE_BATCH_SIZE");
				$orphanedGuildsDeleted += $sql->a_rows;
				$counts[DeleteCount::GuildsDeleted->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}
		$log .= "Cleanup: $orphanedGuildsDeleted dkp_guilds rows deleted<br><br>";
	}
	
	/**
	 * Logs how many items are/would have been deleted.
	 * 
	 * $counts = An enum map of DeleteCount to number of values that were/would be deleted
	 * $totals = An enum map of DeleteCount to total number of values in the database before deletion (used for percentage calculations)
	 * $dryrun = Whether this is a dry run (true) or live delete (false). Used to determine log messaging.
	 */
	private static function logDeleteCounts(&$counts, &$totals, $dryrun, &$log) {
		$pct = function($deleted, $total) {
			if ($total == 0) return "0%";
			return round(($deleted / $total) * 100, 1) . "%";
		};
		
		$dryrunPhrase = $dryrun ? "(Dryrun: NOTHING DELETED)" : "";
		
		$log .= "<b>=== Deletion Counts $dryrunPhrase ===</b><br>";
		$phrase = $dryrun ? "Would delete" : "Deleted";
		
		foreach ($counts as $key => $val) {
			$before = $totals[$key] ?? 0;
			$percent = $pct($val, $before);
			$formatted_val = number_format($val);
			$log .= "$key: $phrase $formatted_val ($percent)<br>";
		}
		return $log;
	}
	

	/**
	 * The number of guilds per server can get out of syncc when we bulk delete guildes.
	 * This recalculates all of them.
	 */
	 static function updateServerTotals(&$log = '') {
		global $sql;
		
		$sql->Query("
			UPDATE dkp_servers s
			SET s.totalguilds = (
				SELECT COUNT(*) FROM dkp_guilds g WHERE g.gserver = s.name
			)
		");

		$updated = $sql->a_rows;
		$log .= "Updated totalguilds for $updated dkp_servers rows<br>";
		return $log;
	}

	/**
	 * Finds and deletes any servers without guilds.
	 */
	static function deleteEmptyServers($dryrun = false, &$log = '') {
		global $sql;

		if ($dryrun) {
			$count = $sql->QueryItem("SELECT COUNT(*) FROM dkp_servers WHERE totalguilds = 0");
			$log .= "Would delete $count empty dkp_servers rows<br>";
			return $log;
		}

		$deleted = 0;
		do {
			$sql->Query("DELETE FROM dkp_servers WHERE totalguilds = 0 LIMIT " . self::$DELETE_BATCH_SIZE);
			$deleted += $sql->a_rows;
		} while ($sql->a_rows > 0);

		$log .= "Deleted $deleted empty dkp_servers rows<br>";
		return $log;
	}
}
?>
