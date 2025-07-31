-- Migration script to add new fields for user registration and password recovery features
-- Run this SQL script on existing TimeEffect installations to add the new functionality
-- Replace <%table_prefix%> with your actual table prefix (e.g. te_)

-- Add new fields to the auth table
ALTER TABLE `<%table_prefix%>auth` 
ADD COLUMN `confirmed` tinyint(1) NOT NULL DEFAULT '1' AFTER `facsimile`,
ADD COLUMN `confirmation_token` varchar(64) DEFAULT NULL AFTER `confirmed`,
ADD COLUMN `reset_token` varchar(64) DEFAULT NULL AFTER `confirmation_token`,
ADD COLUMN `reset_expires` datetime DEFAULT NULL AFTER `reset_token`;

-- Add indexes for performance
ALTER TABLE `<%table_prefix%>auth`
ADD KEY `confirmation_token` (`confirmation_token`),
ADD KEY `reset_token` (`reset_token`);

-- Mark all existing users as confirmed since they're already in the system
UPDATE `<%table_prefix%>auth` SET `confirmed` = 1 WHERE `confirmed` = 0;