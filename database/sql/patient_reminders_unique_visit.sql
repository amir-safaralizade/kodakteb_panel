-- Run this only if patient_reminders was created before the unique-visit rule was added.
-- First keep the oldest reminder for each visit and remove accidental duplicates.
DELETE newer FROM `patient_reminders` newer
INNER JOIN `patient_reminders` older
  ON newer.`visit_id` = older.`visit_id` AND newer.`id` > older.`id`
WHERE newer.`visit_id` IS NOT NULL;

ALTER TABLE `patient_reminders`
  DROP INDEX `patient_reminders_visit_index`,
  ADD UNIQUE KEY `patient_reminders_visit_unique` (`visit_id`);
