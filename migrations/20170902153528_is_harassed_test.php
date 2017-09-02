<?php
use Phinx\Migration\AbstractMigration;

class IsHarassedTest extends AbstractMigration {
	public function change() {
		$this->execute(
			'CREATE FUNCTION is_invitation_harassed(subscription participants.subscription_id%TYPE, email participants.email%TYPE, attempts INTEGER = 5, release INTEGER = 12)
	RETURNS BOOLEAN AS $BODY$
DECLARE harassed BOOLEAN NOT NULL DEFAULT TRUE;
BEGIN
	EXECUTE format(
		$$SELECT EXISTS (
			SELECT 1
			FROM invitation_attempts
			WHERE participant_id = (
	  			SELECT id
		  		FROM participants
		  		WHERE subscription_id = %L
		  		AND email = %L
	  		)
	  		AND attempt_at + INTERVAL \'1 HOUR\' * %L > NOW()
	  		HAVING COUNT(*) >= %L
	  	)$$,
		subscription,
		email,
		release,
		attempts
	)
	INTO harassed;
	RETURN harassed;
END;
$BODY$
LANGUAGE plpgsql;'
		);
	}
}
