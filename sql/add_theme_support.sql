-- Migration to add theme preference support
-- Add theme_preference column to auth table

ALTER TABLE `auth` ADD COLUMN `theme_preference` ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'system' AFTER `facsimile`;

-- Add index for better performance on theme lookups
ALTER TABLE `auth` ADD INDEX `theme_preference` (`theme_preference`);