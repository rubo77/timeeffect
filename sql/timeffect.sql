# phpMyAdmin MySQL-Dump
# version 2.5.1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 16. März 2004 um 09:31
# Server Version: 3.23.52
# PHP-Version: 4.3.2
# Datenbank: `timeeffect`
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `auth`
#
# Erzeugt am: 16. März 2004 um 08:55
# Aktualisiert am: 16. März 2004 um 09:12
#

DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `permissions` varchar(255) NOT NULL default '',
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `firstname` varchar(128) default NULL,
  `lastname` varchar(128) default NULL,
  `email` varchar(255) default NULL,
  `telephone` varchar(64) default NULL,
  `facsimile` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`firstname`,`lastname`,`password`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

#
# Daten für Tabelle `auth`
#

INSERT INTO `auth` VALUES (1, 'admin,agent', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', '', '', '', '');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `customer`
#
# Erzeugt am: 16. März 2004 um 09:04
# Aktualisiert am: 16. März 2004 um 09:04
#

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `active` enum('yes','no') NOT NULL default 'yes',
  `customer_name` varchar(64) NOT NULL default '',
  `customer_desc` text,
  `customer_budget` int(10) unsigned NOT NULL default '0',
  `customer_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `customer_logo` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`customer_name`),
  FULLTEXT KEY `description` (`customer_desc`),
  KEY `active` (`active`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `customer`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `effort`
#
# Erzeugt am: 16. März 2004 um 09:04
# Aktualisiert am: 16. März 2004 um 09:04
#

DROP TABLE IF EXISTS `effort`;
CREATE TABLE `effort` (
  `id` int(32) unsigned NOT NULL auto_increment,
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
  FULLTEXT KEY `note` (`note`,`description`),
  KEY `id_2` (`id`,`project_id`,`date`,`begin`,`end`,`billed`,`rate`,`user`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `effort`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `group`
#
# Erzeugt am: 16. März 2004 um 08:55
# Aktualisiert am: 16. März 2004 um 08:55
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
# Erzeugt am: 16. März 2004 um 09:04
# Aktualisiert am: 16. März 2004 um 09:04
#

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '0',
  `project_name` varchar(64) NOT NULL default '',
  `project_desc` text,
  `project_budget` int(10) unsigned NOT NULL default '0',
  `project_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `last` timestamp(14) NOT NULL,
  `closed` enum('No','Yes') NOT NULL default 'No',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  FULLTEXT KEY `description` (`project_desc`),
  KEY `id_2` (`id`,`project_name`,`customer_id`,`closed`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `project`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rate`
#
# Erzeugt am: 16. März 2004 um 09:04
# Aktualisiert am: 16. März 2004 um 09:04
#

DROP TABLE IF EXISTS `rate`;
CREATE TABLE `rate` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  `price` float NOT NULL default '0',
  `currency` enum('€','EUR','USD') NOT NULL default 'EUR',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`customer_id`,`name`,`price`,`currency`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `rate`
#


