-- Run this once if the appointments table has already been created.
ALTER TABLE `appointments`
  ADD COLUMN `reminder_at` DATETIME NULL AFTER `notes`,
  ADD COLUMN `reminder_sent_at` DATETIME NULL AFTER `reminder_at`,
  ADD COLUMN `reminder_locked_at` DATETIME NULL AFTER `reminder_sent_at`,
  ADD COLUMN `reminder_last_attempt_at` DATETIME NULL AFTER `reminder_locked_at`,
  ADD COLUMN `reminder_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `reminder_last_attempt_at`,
  ADD COLUMN `reminder_error` VARCHAR(500) NULL AFTER `reminder_attempts`,
  ADD KEY `appointments_reminder_due_index` (`status`,`reminder_sent_at`,`reminder_at`);

-- Schedule existing future appointments. Same-day reservations remain silent.
UPDATE `appointments`
SET `reminder_at` = CASE
  WHEN DATE(`created_at`) = `appointment_date` THEN NULL
  WHEN DATE(`created_at`) = DATE_SUB(`appointment_date`, INTERVAL 1 DAY)
    THEN TIMESTAMP(`appointment_date`, '08:00:00')
  ELSE TIMESTAMP(DATE_SUB(`appointment_date`, INTERVAL 1 DAY), '18:00:00')
END
WHERE `appointment_date` >= CURDATE()
  AND `reminder_sent_at` IS NULL;
