<?php

use Phinx\Migration\AbstractMigration;

class FunctionIso8601 extends AbstractMigration {
	public function change() {
		$this->execute(
			<<< SQL
CREATE FUNCTION to_ISO8601(TIMESTAMPTZ) RETURNS TEXT
LANGUAGE plpgsql IMMUTABLE STRICT
AS $$
BEGIN
	RETURN to_char ($1, 'YYYY-MM-DD"T"HH24:MI:SS"Z"');
END
$$;
SQL
		);
	}
}
