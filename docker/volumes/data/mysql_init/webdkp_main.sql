SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `content_news` (
  `id` int(11) NOT NULL,
  `title` varchar(512) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `images` varchar(512) NOT NULL,
  `imagesingallery` varchar(512) NOT NULL,
  `files` varchar(512) NOT NULL,
  `address` varchar(512) NOT NULL,
  `createdby` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `content_news` (`id`, `title`, `content`, `status`, `created`, `images`, `imagesingallery`, `files`, `address`, `createdby`) VALUES
(1, 'Server Upgrade coming!', 'Notice the site is slow?  ;) we did too.\r\nWe\'re upgrading the server hardware tonight.  \r\nThe server will be offline for 1 hr starting 9:00pm (EST)\r\nSorry for the issues guys.\r\nThanks,\r\nWebDKP Management', 1, '2008-12-07 17:10:07', '', '', '', '', 0);

CREATE TABLE `content_news_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `content_news_tags_map` (
  `id` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  `tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_awards` (
  `id` int(11) NOT NULL,
  `tableid` int(11) NOT NULL,
  `guild` int(11) NOT NULL,
  `playercount` int(11) NOT NULL,
  `points` decimal(11,2) NOT NULL,
  `reason` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `location` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `awardedby` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `foritem` int(11) NOT NULL DEFAULT '0',
  `transfer` int(1) NOT NULL DEFAULT '0',
  `zerosumauto` int(1) NOT NULL DEFAULT '0',
  `linked` int(11) NOT NULL DEFAULT '0',
  `itemid` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_guilds` (
  `id` int(11) NOT NULL,
  `gname` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `gfaction` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `gserver` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `claimed` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `dkp_guilds` (`id`, `gname`, `gfaction`, `gserver`, `claimed`) VALUES
(1, 'Totus Solus', 'Alliance', 'Stormscale', 1);

CREATE TABLE `dkp_itemcache` (
  `id` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `name` varchar(256) CHARACTER SET utf8 NOT NULL,
  `link` varchar(256) NOT NULL,
  `quality` varchar(256) NOT NULL,
  `icon` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_loottable` (
  `id` int(11) NOT NULL,
  `name` varchar(256) CHARACTER SET utf8 NOT NULL,
  `guild` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_loottable_data` (
  `id` int(11) NOT NULL,
  `section` int(11) NOT NULL,
  `loottable` int(11) NOT NULL,
  `name` varchar(256) CHARACTER SET utf8 NOT NULL,
  `cost` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_loottable_section` (
  `id` int(11) NOT NULL,
  `loottable` int(11) NOT NULL,
  `name` varchar(256) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `dkp_permissions` (`id`, `name`) VALUES
(1, 'BackupCreate'),
(2, 'BackupRestore'),
(3, 'RecalculateDKP'),
(4, 'DKPTables'),
(5, 'AccountEditGuild'),
(6, 'AccountSecondaryUsers'),
(7, 'ChangeSettings'),
(8, 'AllTableAccess'),
(9, 'TableAddPlayer'),
(10, 'TableDeletePlayer'),
(11, 'TableAddPoints'),
(12, 'TableEditHistory'),
(13, 'TableEditPlayers'),
(14, 'TableUploadLog'),
(15, 'Repair'),
(16, 'LootTable');

CREATE TABLE `dkp_pointhistory` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `award` int(11) NOT NULL,
  `guild` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_points` (
  `id` int(11) NOT NULL,
  `tableid` int(11) NOT NULL DEFAULT '1',
  `user` int(11) NOT NULL DEFAULT '0',
  `points` decimal(11,2) NOT NULL DEFAULT '0.00',
  `lifetime` decimal(11,2) NOT NULL DEFAULT '0.00',
  `guild` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_proaccountlog` (
  `id` int(11) NOT NULL,
  `type` varchar(66) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `guild` int(11) NOT NULL DEFAULT '0',
  `txn` varchar(66) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_remote_custom` (
  `id` int(11) NOT NULL,
  `guild` int(11) NOT NULL,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `dkp_remote_style` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` varchar(512) NOT NULL,
  `createdby` varchar(256) NOT NULL,
  `file` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `dkp_remote_style` (`id`, `name`, `description`, `createdby`, `file`) VALUES
(1, 'Standard WebDKP', 'The standard blue and gray table used on WebDKP.com', 'Zedd', 'standard'),
(2, 'Custom', 'Select this to build your own style! With a custom style you can directly edit the CSS behind the table so it fits in flawlessly with your site. After\r\n						 selecting this option you\'ll see an button to edit the CSS near the top of the\r\n						 page.', 'You', 'custom'),
(3, 'Dark Gray', 'A Dark Gray style that is similar to the tables used on <a href=\'http://www.wowhead.com\'>Wowhead.com</a>.', 'Zedd', 'darkgray');

CREATE TABLE `dkp_servers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `totalguilds` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `dkp_servers` (`id`, `name`, `totalguilds`) VALUES
(1, 'Stormscale', 1);

CREATE TABLE `dkp_settings` (
  `id` int(11) NOT NULL,
  `guild` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `value` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `dkp_settings` (`id`, `guild`, `name`, `value`) VALUES
(1, 1, 'Proaccount', '0'),
(2, 1, 'Prostatus', ''),
(3, 1, 'LootTableEnabled', '0'),
(4, 1, 'TiersEnabled', '0'),
(5, 1, 'TierSize', '50'),
(6, 1, 'ZerosumEnabled', '0'),
(7, 1, 'LifetimeEnabled', '1'),
(8, 1, 'CombineAltsEnabled', '0'),
(9, 1, 'SetsEnabled', '0'),
(10, 1, 'DisabledSets', ''),
(11, 1, 'RemoteStyle', '1');

CREATE TABLE `dkp_tables` (
  `id` int(11) NOT NULL,
  `guild` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `tableid` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `dkp_tables` (`id`, `guild`, `name`, `tableid`) VALUES
(1, 1, 'Main Table', 1);

CREATE TABLE `dkp_userpermissions` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL DEFAULT '0',
  `permissions` text NOT NULL,
  `tables` text NOT NULL,
  `isadmin` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `dkp_userpermissions` (`id`, `user`, `permissions`, `tables`, `isadmin`) VALUES
(1, 1, '', '', 1);

CREATE TABLE `dkp_users` (
  `id` int(11) NOT NULL,
  `name` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `guild` int(11) NOT NULL DEFAULT '0',
  `faction` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `server` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `class` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `main` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `part_bbcode` (
  `id` int(11) NOT NULL,
  `iid` int(11) NOT NULL,
  `createdby` varchar(256) NOT NULL,
  `lastupdate` datetime NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `part_html` (
  `id` int(11) NOT NULL,
  `iid` int(11) NOT NULL,
  `createdby` varchar(256) NOT NULL,
  `lastupdate` datetime NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `paymenthistory` (
  `id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL DEFAULT '',
  `amount` double(5,2) NOT NULL DEFAULT '0.00',
  `fee` double(5,2) NOT NULL DEFAULT '0.00',
  `income` double(5,2) NOT NULL DEFAULT '0.00',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `guild` int(11) NOT NULL DEFAULT '0',
  `txn` varchar(122) NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `security_permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `category` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `security_permissions` (`id`, `name`, `category`) VALUES
(1, 'Control Panel', 'Site'),
(2, 'Create Page', 'Site'),
(3, 'Edit Page', 'Site'),
(4, 'Edit Permissions', 'Site Security'),
(5, 'Edit User Groups', 'Site Security'),
(6, 'Edit Users', 'Site Security'),
(7, 'Manage Parts', 'Site'),
(8, 'Manage Themes', 'Site');

CREATE TABLE `security_reset_password` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `requestkey` varchar(256) NOT NULL,
  `request` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `security_securepages` (
  `id` int(11) NOT NULL,
  `pageid` int(11) NOT NULL,
  `allowedGroups` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `security_usergroups` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `permissions` varchar(512) NOT NULL,
  `defaultuser` tinyint(1) NOT NULL DEFAULT '0',
  `visitor` tinyint(1) NOT NULL DEFAULT '0',
  `system` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `security_usergroups` (`id`, `name`, `permissions`, `defaultuser`, `visitor`, `system`) VALUES
(1, 'Visitor', '', 0, 1, 0),
(2, 'User', '', 1, 0, 0),
(3, 'Admin', ',1,2,3,4,5,6,7,8', 1, 0, 1);

CREATE TABLE `security_users` (
  `id` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(49) NOT NULL,
  `firstname` varchar(128) NOT NULL,
  `lastname` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `usergroup` int(11) NOT NULL,
  `guild` int(11) DEFAULT NULL,
  `registerdate` datetime NOT NULL,
  `lastlogin` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `security_users` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `usergroup`, `guild`, `registerdate`, `lastlogin`) VALUES
(1, 'Zedd', '150f3cb9ff4215aaa5ec290bc9292f9d24119866397f896e9', '', '', 'scott@zeddic.com', 2, 1, '2019-12-01 23:08:57', '2019-12-01 23:08:57');

CREATE TABLE `site_controlpanel_items` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `type` int(11) NOT NULL,
  `link` varchar(256) NOT NULL,
  `image` varchar(256) NOT NULL,
  `parent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `site_files` (
  `id` int(11) NOT NULL,
  `title` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `originalname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `context` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `uploaddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `site_folders` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `site_images` (
  `id` int(11) NOT NULL,
  `title` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `comment` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `originalname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `context` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `thumbnail` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `square` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `small` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `medium` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `large` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `uploaddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `site_layouts` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `common` tinyint(1) NOT NULL,
  `system` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_layouts` (`id`, `name`, `filename`, `common`, `system`) VALUES
(1, 'Columns2', 'Columns2', 0, 0),
(2, 'Columns1', 'Columns1', 0, 0),
(3, 'EditAlone', 'EditAlone', 0, 0),
(4, 'Columns3', 'Columns3', 0, 0),
(5, 'Columns2Right', 'Columns2Right', 0, 0);

CREATE TABLE `site_pages` (
  `id` int(11) NOT NULL,
  `url` varchar(512) NOT NULL,
  `title` varchar(512) NOT NULL,
  `useTemplate` int(11) NOT NULL DEFAULT '1',
  `template` int(11) NOT NULL DEFAULT '1',
  `isTemplate` int(11) NOT NULL DEFAULT '0',
  `layout` int(11) NOT NULL DEFAULT '0',
  `system` int(11) NOT NULL DEFAULT '0',
  `area1` varchar(512) NOT NULL,
  `area2` varchar(512) NOT NULL,
  `area3` varchar(512) NOT NULL,
  `area4` varchar(512) NOT NULL,
  `area5` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_pages` (`id`, `url`, `title`, `useTemplate`, `template`, `isTemplate`, `layout`, `system`, `area1`, `area2`, `area3`, `area4`, `area5`) VALUES
(1, 'templates/mastertemplate', 'Master Template', 0, 0, 1, 1, 0, '', '', '', '', ''),
(2, 'index', 'Home', 1, 1, 0, 1, 0, '-1,1', '-1', '0,-1', '0,-1', '0,-1'),
(3, 'convert', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(4, 'admin/template', '', 0, 0, 1, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(5, 'admin/index', 'Control Panel', 1, 4, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(6, 'admin/security', 'Control Panel', 1, 4, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(7, 'dkp/index', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(8, 'dkp/admin/index', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(9, 'dkp/awards', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(10, 'dkp/player', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(11, 'browse', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(12, 'dkp/server', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(13, 'addon', '', 0, 0, 0, 5, 0, '-1', '-1', '0,-1', '0,-1', '0,-1'),
(14, 'errors/404', '', 1, 1, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(15, 'dkp/loot', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(16, 'dkp/loottable', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(17, 'login', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(18, 'dkp', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(19, 'remote', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(20, 'dkp/remotepreview', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(21, 'dkp/remote', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(22, 'join', '', 0, 0, 0, 5, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(23, 'welcome', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(24, 'dkp/admin/settings', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(25, 'dkp/admin/dkptables', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(26, 'dkp/admin/remotestyle', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(27, 'dkp/admin/download', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(28, 'dkp/admin/upload', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(29, 'dkp/log', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(30, 'dkp/admin/manage', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(31, 'dkp/admin/playeralts', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(32, 'dkp/admin/remote', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(33, 'dkp/admin/createaward', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(34, 'dkp/award', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(35, 'dkp/admin/updateguild', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(36, 'dkp/admin/updateaccount', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(37, 'dkp/admin/armory', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(38, 'servermissing', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(39, 'loot', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(40, 'dkp/admin/backup', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(41, 'dkp/admin/editremotestyle', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(42, 'dkp/admin/loottable', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(43, 'dkp/admin/officeraccounts', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(44, 'dkp/admin/ads', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(45, 'dkp/admin/editofficeraccount', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(46, 'account', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(47, 'screenshots', '', 0, 0, 0, 5, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(48, 'dkp/admin/editdkptable', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(49, 'dkp/admin/editloottable', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(50, 'dkp/sets', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(51, 'dkp/admin/deletetable', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(52, 'dkp/admin/editaward', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(53, 'forgot', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(54, 'admin/login', 'Control Panel', 1, 4, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(55, 'reset', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(56, 'dkp/admin/repair', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(57, 'edit/pagesettings', 'Page Settings', 0, 0, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(58, 'edit/partlibrary', 'Control Panel', 0, 0, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(59, 'admin/content/template', '', 1, 4, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(60, 'admin/content/index', '', 1, 59, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(61, 'admin/webpages', 'Control Panel', 1, 4, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(62, 'admin/themes', 'Control Panel', 1, 4, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(63, 'admin/library', 'Control Panel', 1, 4, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(64, 'forsale', '', 0, 0, 0, 5, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(66, 'admin/content/news/index', '', 1, 59, 0, 1, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(67, 'managedkp', '', 0, 0, 0, 1, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(70, 'edit/newpage', 'Create Page', 0, 0, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(71, 'test/index', '', 1, 1, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(72, 'dkp/admin /index', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(74, '/index', '', 0, 0, 0, 5, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(76, 'supportwebdkp', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(77, 'admin/databasefunctions', 'WebDKP Database Management', 1, 4, 0, 2, 1, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(78, 'clean', '', 0, 0, 0, 2, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1'),
(80, './index', '', 0, 0, 0, 5, 0, '0,-1', '0,-1', '0,-1', '0,-1', '0,-1');

CREATE TABLE `site_parts` (
  `id` int(11) NOT NULL,
  `definition` int(11) NOT NULL,
  `instanceName` varchar(256) NOT NULL,
  `defaultView` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `border` int(11) NOT NULL DEFAULT '1',
  `options` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `site_part_library` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `systemName` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `system` int(11) NOT NULL,
  `directory` varchar(256) NOT NULL,
  `createdby` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_part_library` (`id`, `name`, `systemName`, `description`, `system`, `directory`, `createdby`) VALUES
(1, 'HTML', 'html', 'A part that allows you to enter static html.', 0, 'site/parts/html/', ''),
(2, 'BBCode', 'bbcode', 'A simple BBCode based text editor. Also includes the ability to upload photos.', 0, 'site/parts/bbcode/', ''),
(3, 'Login', 'login', 'A module that lets users log into the site.', 0, 'site/parts/login/', ''),
(4, 'Navigation', 'navigation', 'A part that allows you to create a site navigation list.', 0, 'site/parts/navigation/', '');

CREATE TABLE `site_part_library_options` (
  `id` int(11) NOT NULL,
  `partDefinition` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `type` int(11) NOT NULL,
  `defaultValue` varchar(256) NOT NULL,
  `choices` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_part_library_options` (`id`, `partDefinition`, `name`, `type`, `defaultValue`, `choices`) VALUES
(1, 3, 'Size', 2, 'Normal', 'Small$|$Normal'),
(2, 3, 'Grab Focus', 1, '', '');

CREATE TABLE `site_status` (
  `id` int(11) NOT NULL,
  `theme` int(11) NOT NULL,
  `setup` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_status` (`id`, `theme`, `setup`) VALUES
(1, 1, 1);

CREATE TABLE `site_themes` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `directory` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `createdby` varchar(256) NOT NULL,
  `dateadded` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_themes` (`id`, `name`, `directory`, `description`, `createdby`, `dateadded`) VALUES
(1, 'Default', 'default', 'The default theme.', 'Scott Bailey', '2008-06-07 02:25:30'),
(2, 'Control Panel Theme', 'control_panel', 'The control panel theme. This theme is used internally by the system soley for the control panel pages.', 'Scott Bailey', '2008-06-07 02:25:30');

CREATE TABLE `site_theme_map` (
  `id` int(11) NOT NULL,
  `path` varchar(256) NOT NULL,
  `theme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `site_theme_map` (`id`, `path`, `theme`) VALUES
(1, '/admin', 2);


ALTER TABLE `content_news`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_news_tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_news_tags_map`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_awards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild` (`guild`),
  ADD KEY `tableid` (`tableid`),
  ADD KEY `date` (`date`),
  ADD KEY `reason` (`reason`(255),`date`,`points`);

ALTER TABLE `dkp_guilds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guildName` (`gname`,`gserver`),
  ADD KEY `claimed` (`claimed`);

ALTER TABLE `dkp_itemcache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `itemid` (`itemid`),
  ADD KEY `name` (`name`(255));

ALTER TABLE `dkp_loottable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild` (`guild`);

ALTER TABLE `dkp_loottable_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loottable` (`loottable`,`section`,`name`(255));

ALTER TABLE `dkp_loottable_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loottable` (`loottable`);

ALTER TABLE `dkp_permissions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_pointhistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `award` (`award`),
  ADD KEY `guild` (`guild`);

ALTER TABLE `dkp_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tableid` (`tableid`,`user`,`guild`),
  ADD KEY `guild` (`guild`),
  ADD KEY `user` (`user`);

ALTER TABLE `dkp_proaccountlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`,`guild`);

ALTER TABLE `dkp_remote_custom`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_remote_style`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_servers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild` (`guild`);

ALTER TABLE `dkp_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guild` (`guild`,`tableid`);

ALTER TABLE `dkp_userpermissions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `dkp_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`,`server`);

ALTER TABLE `part_bbcode`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `part_html`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `paymenthistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guild` (`guild`);

ALTER TABLE `security_permissions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `security_reset_password`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `security_securepages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `security_usergroups`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `security_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_controlpanel_items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_folders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_images`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_layouts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_pages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_parts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_part_library`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_part_library_options`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_status`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_themes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `site_theme_map`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `content_news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `content_news_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `content_news_tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_awards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_guilds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `dkp_itemcache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_loottable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_loottable_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_loottable_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `dkp_pointhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_proaccountlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_remote_custom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dkp_remote_style`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `dkp_servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `dkp_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `dkp_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `dkp_userpermissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `dkp_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `part_bbcode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `part_html`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `paymenthistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `security_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `security_reset_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `security_securepages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `security_usergroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `security_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `site_controlpanel_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_layouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `site_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

ALTER TABLE `site_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_part_library`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `site_part_library_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `site_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `site_themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `site_theme_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
