<?php
include_once("lib/dkp/dkpCleanupConstants.php");

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
		
		// Delete inactive security_users whose guild is stale (not all inactive users globally).
		$staleUserIds = [];
		if (!empty($staleGuildIds)) {
			$staleGuildIdList = implode(',', $staleGuildIds);
			$staleUserResult = $sql->Query("SELECT id FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval AND guild IN ($staleGuildIdList)");
			while ($row = mysqli_fetch_array($staleUserResult)) {
				$staleUserIds[] = (int)$row['id'];
			}
		}
		dkpCleanup::deleteSecurityUsersByIds($staleUserIds, $dryrun, $deleteCounts, $log);
	
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
			$orphanedUsersDeleted = $sql->QueryItem("SELECT COUNT(*) FROM dkp_users WHERE guild IN (SELECT id FROM dkp_guilds WHERE claimed = 0) AND NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id)");
			$counts[DeleteCount::Users->value] += $orphanedUsersDeleted;
		} else {
			do {
				$sql->Query("DELETE FROM dkp_users WHERE guild IN (SELECT id FROM dkp_guilds WHERE claimed = 0) AND NOT EXISTS (SELECT 1 FROM dkp_points p WHERE p.user = dkp_users.id) LIMIT $DELETE_BATCH_SIZE");
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
	 * Finds and deletes guilds that haven't been logged into for X months and have 0 data
	 * present.
	 */
	static function deleteStaleGuildsWithNoPoints($dryrun = false) {
		global $sql;
		$staleInterval = "24 MONTH"; // Interval definition
		$DELETE_GUILD_BATCH_SIZE = self::$DELETE_GUILD_BATCH_SIZE;

		$log = '';
		$log .= "<b>=== Stale Guilds without Points Cleanup ===</b><br>";
		$log .= "Started: " . date('Y-m-d H:i:s') . "<br>";
		$log .= "Mode: <b>" . ($dryrun ? "DRY RUN (no deletes will be performed)" : "LIVE DELETE") . "</b><br>";
		$log .= "Criteria: No user signup in 24 months + no dkp_points table.<br><br>";

		$deleteCounts = self::$EMPTY_COUNTS;
		$totals = dkpCleanup::getExistingEntityTotals();

		// Find guilds that match the criteria.
		$result = $sql->Query("
			SELECT g.id FROM dkp_guilds g
			JOIN security_users su ON su.guild = g.id
			WHERE
			claimed = 1
			AND NOT EXISTS (
				SELECT 1 FROM security_users active
				WHERE active.guild = g.id
				AND active.lastlogin >= NOW() - INTERVAL $staleInterval
			)
			AND NOT EXISTS (
				SELECT 1 FROM dkp_points u WHERE u.guild = g.id
			)
			AND NOT EXISTS (
				SELECT 1 FROM dkp_awards a WHERE a.guild = g.id
			)
			GROUP BY g.id
		");

		$staleGuildIds = [];
		while ($row = mysqli_fetch_array($result)) {
			$staleGuildIds[] = (int)$row['id'];
		}

		$log .= "<b>--- Phase 1: Processing Targeted Guilds ---</b><br>";
		$log .= "Found " . count($staleGuildIds) . " stale guilds with no points.<br><br>";

		if (!empty($staleGuildIds)) {
			// Process deletions in chunks
			foreach (array_chunk($staleGuildIds, $DELETE_GUILD_BATCH_SIZE) as $chunk) {
				$log .= "Processing guild IDs: " . implode(', ', $chunk) . "<br>";
				dkpCleanup::deleteGuildData($chunk, $dryrun, $deleteCounts, $log);
			}

			// Delete associated security_users accounts and permissions
			$staleGuildIdList = implode(',', $staleGuildIds);
			$staleUserResult = $sql->Query("SELECT id FROM security_users WHERE lastlogin < NOW() - INTERVAL $staleInterval AND guild IN ($staleGuildIdList)");
			$staleUserIds = [];
			while ($row = mysqli_fetch_array($staleUserResult)) {
				$staleUserIds[] = (int)$row['id'];
			}
			dkpCleanup::deleteSecurityUsersByIds($staleUserIds, $dryrun, $deleteCounts, $log);

			// Final orphan cleanup and totals update
			if (!$dryrun) {
				dkpCleanup::cleanupOrphans(false, $deleteCounts, $log);
				dkpCleanup::updateServerTotals($log);
			}
		}

		$log .= "<b>--- Summary ---</b><br>";
		dkpCleanup::logDeleteCounts($deleteCounts, $totals, $dryrun, $log);

		return $log;
	}

	/*===========================================================
	Finds and deletes guilds with spam/bot names and security_users
	with known bad usernames or email domains, along with all
	associated data. Uses BAD_WORDS and BAD_EMAIL_DOMAINS constants.

	@param bool $dryrun  Count rows to be deleted but do nothing.
	@return string       HTML log of actions taken / would be taken.
	============================================================*/
	static function deleteBadContent($dryrun = false) {
		global $sql;
		$DELETE_GUILD_BATCH_SIZE = self::$DELETE_GUILD_BATCH_SIZE;

		$log = '';
		$log .= "<b>=== WebDKP Bad Content Cleanup ===</b><br>";
		$log .= "Started: " . date('Y-m-d H:i:s') . "<br>";
		$log .= "Mode: <b>" . ($dryrun ? "DRY RUN (no deletes will be performed)" : "LIVE DELETE") . "</b><br>";
		$log .= "<br>";

		$counts = self::$EMPTY_COUNTS;
		$totals = dkpCleanup::getExistingEntityTotals();

		// --- Phase 1: Find bad guilds by name ---
		$guildWordConds = implode(' OR ', array_map(
			fn($w) => "gname LIKE '%" . $sql->Escape($w) . "%'",
			BAD_WORDS
		));
		$badGuildResult = $sql->Query("SELECT id, gname FROM dkp_guilds WHERE ($guildWordConds) AND NOT EXISTS (SELECT 1 FROM dkp_points WHERE guild = dkp_guilds.id)");
		$badGuildIds = [];
		$badGuildNames = [];
		while ($row = mysqli_fetch_array($badGuildResult)) {
			$badGuildIds[] = (int)$row['id'];
			$badGuildNames[] = htmlspecialchars($row['gname']);
		}

		$log .= "<b>--- Phase 1: Bad Guilds (by name) ---</b><br>";
		$log .= "Found: " . count($badGuildIds) . " guilds with spam names<br>";
		$preview = array_slice($badGuildNames, 0, 100);
		foreach ($preview as $name) {
			$log .= "&nbsp;&nbsp;- $name<br>";
		}
		if (count($badGuildNames) > 100) {
			$log .= "&nbsp;&nbsp;... and " . (count($badGuildNames) - 100) . " more<br>";
		}
		$log .= "<br>";

		// --- Phase 2: Find bad security_users by username or email domain ---
		$userWordConds = implode(' OR ', array_map(
			fn($w) => "username LIKE '%" . $sql->Escape($w) . "%'",
			BAD_WORDS
		));
		$domainConds = implode(' OR ', array_map(
			fn($d) => "(email LIKE '%@" . $sql->Escape($d) . "' OR email LIKE '%." . $sql->Escape($d) . "')",
			BAD_EMAIL_DOMAINS
		));
		$badUserResult = $sql->Query("SELECT id, username, email, guild FROM security_users WHERE ($userWordConds OR $domainConds) AND (guild IS NULL OR NOT EXISTS (SELECT 1 FROM dkp_points WHERE guild = security_users.guild))");
		$badUserIds = [];
		$badUserGuildIds = [];
		$badUserLog = [];
		while ($row = mysqli_fetch_array($badUserResult)) {
			$badUserIds[] = (int)$row['id'];
			$badUserLog[] = htmlspecialchars($row['username']) . ' &lt;' . htmlspecialchars($row['email']) . '&gt;';
			if (!empty($row['guild'])) {
				$badUserGuildIds[] = (int)$row['guild'];
			}
		}

		$log .= "<b>--- Phase 2: Bad Users (by username or email) ---</b><br>";
		$log .= "Found: " . count($badUserIds) . " users with spam usernames or bad email domains<br>";
		$preview = array_slice($badUserLog, 0, 100);
		foreach ($preview as $entry) {
			$log .= "&nbsp;&nbsp;- $entry<br>";
		}
		if (count($badUserLog) > 100) {
			$log .= "&nbsp;&nbsp;... and " . (count($badUserLog) - 100) . " more<br>";
		}
		$log .= "<br>";

		// --- Phase 3: Expand to complete sets ---
		// Add guilds owned by bad users to the guild delete list.
		$allBadGuildIds = array_values(array_unique(array_merge($badGuildIds, $badUserGuildIds)));

		// Add all security_users belonging to bad guilds to the user delete list.
		$allBadUserIds = $badUserIds;
		if (!empty($allBadGuildIds)) {
			$guildIdList = implode(',', array_map('intval', $allBadGuildIds));
			$guildUserResult = $sql->Query("SELECT id FROM security_users WHERE guild IN ($guildIdList)");
			while ($row = mysqli_fetch_array($guildUserResult)) {
				$allBadUserIds[] = (int)$row['id'];
			}
			$allBadUserIds = array_values(array_unique($allBadUserIds));
		}

		$log .= "<b>--- Phase 3: Totals After Expansion ---</b><br>";
		$log .= "Total bad guilds to process: " . count($allBadGuildIds) . "<br>";
		$log .= "Total bad security_users to process: " . count($allBadUserIds) . "<br>";
		$log .= "<br>";

		// --- Phase 4: Delete ---
		$log .= "<b>--- Phase 4: Deleting Guild Data ---</b><br>";
		foreach (array_chunk($allBadGuildIds, $DELETE_GUILD_BATCH_SIZE) as $chunk) {
			dkpCleanup::deleteGuildData($chunk, $dryrun, $counts, $log);
		}
		$log .= "<br>";

		$log .= "<b>--- Phase 5: Deleting security_users ---</b><br>";
		dkpCleanup::deleteSecurityUsersByIds($allBadUserIds, $dryrun, $counts, $log);
		$log .= "<br>";

		if (!$dryrun) {
			dkpCleanup::updateServerTotals($log);
		}

		// --- Summary ---
		$log .= "<b>--- Summary ---</b><br>";
		dkpCleanup::logDeleteCounts($counts, $totals, $dryrun, $log);

		return $log;
	}

	
	/**
	 * Deletes security_users by ID, along with their dkp_userpermissions. Handles dry-run counting.
	 */
	private static function deleteSecurityUsersByIds(array $userIds, $dryrun, &$counts, &$log) {
		global $sql;
		$DELETE_BATCH_SIZE = self::$DELETE_BATCH_SIZE;

		if (empty($userIds)) {
			$log .= "No security_users to delete<br>";
			return;
		}

		$idList = implode(',', array_map('intval', $userIds));

		$log .= "<b>--- Deleting dkp_userpermissions ---</b><br>";
		if ($dryrun) {
			$counts[DeleteCount::AccountPermissions->value] += $sql->QueryItem("SELECT COUNT(*) FROM dkp_userpermissions WHERE user IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM dkp_userpermissions WHERE user IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::AccountPermissions->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}

		$log .= "<b>--- Deleting security_users ---</b><br>";
		if ($dryrun) {
			$counts[DeleteCount::Accounts->value] += $sql->QueryItem("SELECT COUNT(*) FROM security_users WHERE id IN ($idList)");
		} else {
			do {
				$sql->Query("DELETE FROM security_users WHERE id IN ($idList) LIMIT $DELETE_BATCH_SIZE");
				$counts[DeleteCount::Accounts->value] += $sql->a_rows;
			} while ($sql->a_rows > 0);
		}
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
