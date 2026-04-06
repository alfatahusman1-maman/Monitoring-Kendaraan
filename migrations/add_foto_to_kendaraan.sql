-- Migration: Add foto column to kendaraan table
-- This migration adds photo upload functionality to the kendaraan (vehicles) table

ALTER TABLE `kendaraan` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;

-- Description:
-- The 'foto' column stores the filename of the vehicle photo
-- Photos are stored in the /uploads/ directory
-- Files are automatically generated with timestamp to ensure uniqueness
