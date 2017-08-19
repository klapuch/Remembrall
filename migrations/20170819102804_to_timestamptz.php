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
		$this->execute(
			<<< SQL
DROP FUNCTION readable_subscriptions();
CREATE FUNCTION readable_subscriptions() RETURNS TABLE(id integer, user_id integer, part_id integer, "interval" interval, last_update timestamp with time zone, snapshot character varying, interval_seconds integer)
	LANGUAGE sql
	AS $$
		SET intervalstyle = 'ISO_8601';
		SELECT *, extract(epoch from interval)::integer AS interval_seconds
		FROM subscriptions;
	$$;
SQL

		);
	}
}
