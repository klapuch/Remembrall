BEGIN TRANSACTION;
SELECT truncate_tables('postgres');
SELECT restart_sequences();
SELECT * FROM unit_tests.begin();
ROLLBACK TRANSACTION;