CREATE TABLE `<%db_prefix%>auth` (
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
) TYPE=MyISAM;

INSERT INTO `<%db_prefix%>auth` VALUES (1, 'admin,agent', '<%admin_user%>', '<%admin_password%>', 'Administrator', '', '', '', '');


CREATE TABLE `<%db_prefix%>customer` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `active` enum('yes','no') NOT NULL default 'yes',
  `customer_name` varchar(64) NOT NULL default '',
  `customer_desc` text,
  `customer_budget` int(10) unsigned NOT NULL default '0',
  `customer_budget_currency` enum('€','EUR','USD') NOT NULL,
  `customer_logo` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`customer_name`),
  FULLTEXT KEY `description` (`customer_desc`),
  KEY `active` (`active`)
) TYPE=MyISAM;

CREATE TABLE `<%db_prefix%>effort` (
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
) TYPE=MyISAM;

CREATE TABLE `<%db_prefix%>group` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `level` smallint(1) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


INSERT INTO `<%db_prefix%>group` VALUES (1, 65535, 'admin');
INSERT INTO `<%db_prefix%>group` VALUES (2, 6, 'agent');
INSERT INTO `<%db_prefix%>group` VALUES (3, 4, 'client');


CREATE TABLE `<%db_prefix%>project` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '0',
  `project_name` varchar(64) NOT NULL default '',
  `project_desc` text,
  `project_budget` int(10) unsigned NOT NULL default '0',
  `project_budget_currency` enum('€','EUR','USD') NOT NULL,
  `last` timestamp(14) NOT NULL,
  `closed` enum('No','Yes') NOT NULL default 'No',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  FULLTEXT KEY `description` (`project_desc`),
  KEY `id_2` (`id`,`project_name`,`customer_id`,`closed`)
) TYPE=MyISAM;

CREATE TABLE `<%db_prefix%>rate` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  `price` float NOT NULL default '0',
  `currency` enum('€','EUR','USD') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`customer_id`,`name`,`price`,`currency`)
) TYPE=MyISAM;

