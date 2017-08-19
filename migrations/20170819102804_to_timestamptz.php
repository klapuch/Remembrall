<?php

use Phinx\Migration\AbstractMigration;

class ToTimestamptz extends AbstractMigration {
	public function change() {
		$this->execute(
			<<< SQL
DO $$
DECLARE
  reflection RECORD;
BEGIN
  FOR reflection IN WITH info AS (
	SELECT columns.table_name, columns.column_name, columns.data_type
	FROM information_schema.columns
	INNER JOIN information_schema.tables ON tables.table_name = columns.table_name
	WHERE columns.table_schema = 'public'
	AND table_type = 'BASE TABLE'
	AND data_type = 'timestamp without time zone'
  )
  SELECT *
  FROM info
  EXCEPT
  SELECT info.*
  FROM info
  INNER JOIN pg_catalog.pg_inherits ON inhrelid = table_name::regclass
  ORDER BY table_name
  LOOP
  EXECUTE format(
	'ALTER TABLE %I ALTER COLUMN %I TYPE TIMESTAMP WITH TIME ZONE USING %I',
	reflection.table_name,
	reflection.column_name,
	reflection.column_name
  );
END LOOP;
END;
$$;
SQL
		);
	}
}
