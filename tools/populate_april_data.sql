-- Population Script for April 2026 (Dishub Monitoring System)
-- Adds 20 records to bbm and servis with varied statuses

-- 1. Get user IDs and Vehicle IDs (IDs found in previous search: Users: 10+, Vehicles: 1+)
-- Using assumed valid IDs from previous successful populations

SET FOREIGN_KEY_CHECKS = 0;

-- Insert BBM Records for April 2026
INSERT INTO bbm (id_user, id_kendaraan, tanggal, liter, jenis_bbm, biaya, status_admin, created_at) VALUES
(4, 1, '2026-04-02 08:30:00', 35, 'Pertalite', 350000, 'APPROVED', '2026-04-02 08:30:00'),
(5, 1, '2026-04-05 10:15:00', 40, 'Pertamax', 540000, 'APPROVED', '2026-04-05 10:15:00'),
(6, 1, '2026-04-08 09:00:00', 25, 'Pertalite', 250000, 'PENDING', '2026-04-08 09:00:00'),
(7, 1, '2026-04-10 14:20:00', 45, 'Solar', 450000, 'REJECTED', '2026-04-10 14:20:00'),
(8, 1, '2026-04-12 11:45:00', 30, 'Pertamax', 405000, 'PENDING', '2026-04-12 11:45:00'),
(9, 1, '2026-04-15 07:30:00', 50, 'Pertalite', 500000, 'APPROVED', '2026-04-15 07:30:00'),
(10,1, '2026-04-16 16:10:00', 20, 'Dexlite', 320000, 'PENDING', '2026-04-16 16:10:00'),
(11,1, '2026-04-17 09:20:00', 42, 'Pertamax', 567000, 'PENDING', '2026-04-17 09:20:00'),
(12,1, '2026-04-17 14:00:00', 28, 'Pertalite', 280000, 'PENDING', '2026-04-17 14:00:00'),
(13,1, '2026-04-17 18:30:00', 33, 'Pertalite', 330000, 'PENDING', '2026-04-17 18:30:00');

-- Insert Servis Records for April 2026
INSERT INTO servis (id_user, id_kendaraan, tanggal, jenis_servis, biaya, status_admin, created_at) VALUES
(4, 1, '2026-04-01 13:00:00', 'Ganti Oli', 450000, 'APPROVED', '2026-04-01 13:00:00'),
(5, 1, '2026-04-04 15:30:00', 'Service Rutin', 850000, 'APPROVED', '2026-04-04 15:30:00'),
(6, 1, '2026-04-07 11:00:00', 'Tune Up', 600000, 'REJECTED', '2026-04-07 11:00:00'),
(7, 1, '2026-04-09 10:00:00', 'Ganti Ban', 1200000, 'PENDING', '2026-04-09 10:00:00'),
(8, 1, '2026-04-11 09:15:00', 'Balancing', 350000, 'APPROVED', '2026-04-11 09:15:00'),
(9, 1, '2026-04-13 14:00:00', 'Ganti Aki', 950000, 'PENDING', '2026-04-13 14:00:00'),
(10,1, '2026-04-15 11:30:00', 'Service Rem', 300000, 'APPROVED', '2026-04-15 11:30:00'),
(11,1, '2026-04-16 10:45:00', 'Ganti Busi', 150000, 'PENDING', '2026-04-16 10:45:00'),
(12,1, '2026-04-17 08:30:00', 'Kuras Radiator', 250000, 'PENDING', '2026-04-17 08:30:00'),
(13,1, '2026-04-17 15:00:00', 'Service AC', 1100000, 'PENDING', '2026-04-17 15:00:00');

SET FOREIGN_KEY_CHECKS = 1;
