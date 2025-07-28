-- Migration: Add login attempts table for brute force protection
-- Date: 2025-01-01
-- Description: Creates the login_attempts table to track failed login attempts
--              and implement brute force protection

CREATE TABLE IF NOT EXISTS `<%db_prefix%>login_attempts` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt (IPv4 or IPv6)',
  `username` varchar(50) NOT NULL default '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'When the attempt occurred',
  `success` tinyint(1) NOT NULL default 0 COMMENT '1 for successful login, 0 for failed',
  PRIMARY KEY  (`id`),
  KEY `ip_time` (`ip_address`, `attempt_time`),
  KEY `username_time` (`username`, `attempt_time`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=MyISAM COMMENT='Tracks login attempts for brute force protection';