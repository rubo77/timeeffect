# Login Attempts Table for Brute Force Protection
# This table tracks failed login attempts to prevent flooding

DROP TABLE IF EXISTS `te_login_attempts`;
CREATE TABLE `te_login_attempts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt (IPv4 or IPv6)',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the attempt occurred',
  `success` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for successful login, 0 for failed',
  PRIMARY KEY (`id`),
  KEY `ip_time` (`ip_address`, `attempt_time`),
  KEY `username_time` (`username`, `attempt_time`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=MyISAM COMMENT='Tracks login attempts for brute force protection';