# phpMyAdmin MySQL-Dump
# version 2.5.1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 23. Oktober 2004 um 20:38
# Server Version: 4.0.15
# PHP-Version: 4.3.3
# Datenbank: `timeeffect`
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `auth`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `permissions` varchar(255) NOT NULL default '',
  `gids` varchar(255) NOT NULL default '',
  `allow_nc` smallint(1) NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `firstname` varchar(128) default NULL,
  `lastname` varchar(128) default NULL,
  `email` varchar(255) default NULL,
  `telephone` varchar(64) default NULL,
  `facsimile` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  KEY `gids` (`gids`),
  KEY `username` (`username`,`password`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `auth`
#

INSERT INTO `auth` VALUES (1, 'admin,agent', '', 1, '<%admin_user%>', '<%admin_password%>', 'Administrator', '', '', '', '');

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `customer`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `readforeignefforts` smallint(1) NOT NULL default '1',
  `user` int(32) unsigned NOT NULL default '0',
  `active` enum('yes','no') NOT NULL default 'yes',
  `customer_name` varchar(64) NOT NULL default '',
  `customer_desc` text,
  `customer_budget` int(10) unsigned NOT NULL default '0',
  `customer_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `customer_logo` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`customer_name`),
  KEY `active` (`active`),
  KEY `access` (`access`),
  KEY `gid` (`gid`),
  KEY `user` (`user`),
  KEY `readforeignefforts` (`readforeignefforts`),
  FULLTEXT KEY `description` (`customer_desc`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `customer`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `effort`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `effort`;
CREATE TABLE `effort` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rw-rw-r--',
  `project_id` int(32) unsigned NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `begin` time NOT NULL default '00:00:00',
  `end` time NOT NULL default '00:00:00',
  `description` text,
  `note` text,
  `billed` date default NULL,
  `rate` int(32) unsigned NOT NULL default '1',
  `user` int(32) unsigned default NULL,
  `last` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`project_id`,`date`,`begin`,`end`,`billed`,`rate`,`user`),
  KEY `gid` (`gid`),
  KEY `access` (`access`),
  FULLTEXT KEY `note` (`note`,`description`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `effort`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `gids`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `gids`;
CREATE TABLE `gids` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `gids`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `group`
#
# Erzeugt am: 22. Oktober 2004 um 15:49
# Aktualisiert am: 22. Oktober 2004 um 15:49
#

DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `level` smallint(1) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

#
# Daten für Tabelle `group`
#

INSERT INTO `group` VALUES (1, 65535, 'admin');
INSERT INTO `group` VALUES (2, 6, 'agent');
INSERT INTO `group` VALUES (3, 4, 'client');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `project`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `user` int(32) unsigned NOT NULL default '0',
  `customer_id` int(32) unsigned NOT NULL default '0',
  `project_name` varchar(64) NOT NULL default '',
  `project_desc` text,
  `project_budget` int(10) unsigned NOT NULL default '0',
  `project_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `last` timestamp(14) NOT NULL,
  `closed` enum('No','Yes') NOT NULL default 'No',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`project_name`,`customer_id`,`closed`),
  KEY `gid` (`gid`),
  KEY `access` (`access`),
  KEY `user` (`user`),
  FULLTEXT KEY `description` (`project_desc`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `project`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rate`
#
# Erzeugt am: 23. Oktober 2004 um 20:37
# Aktualisiert am: 23. Oktober 2004 um 20:37
#

DROP TABLE IF EXISTS `rate`;
CREATE TABLE `rate` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  `price` float NOT NULL default '0',
  `currency` enum('€','EUR','USD') NOT NULL default '€',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`customer_id`,`name`,`price`,`currency`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `rate`
#


