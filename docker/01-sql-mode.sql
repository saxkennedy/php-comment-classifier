-- Runs before the data import. Relaxes sql_mode so the provided dump's
-- 0000-00-00 ship dates don't cause a problem for some MySQL versions.
SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
