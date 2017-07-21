<?php
use Phinx\Migration\AbstractMigration;

class IntervalColumn extends AbstractMigration {
	public function change() {
		$this->execute('DROP VIEW IF EXISTS readable_subscriptions');
		$this->execute('ALTER TABLE subscriptions ALTER COLUMN "interval" TYPE interval USING interval::interval');
		$this->execute(
			"CREATE FUNCTION readable_subscriptions() RETURNS TABLE(id integer, user_id integer, part_id integer, \"interval\" interval, last_update timestamp without time zone, snapshot character varying, interval_seconds integer)
			LANGUAGE sql
			AS $$
			SET intervalstyle = 'ISO_8601';
			SELECT *, extract(epoch from interval)::integer AS interval_seconds
			FROM subscriptions;
			$$;"
		);
	}
}
