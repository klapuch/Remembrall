<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

final class UpdateTriggerNotifiedSubscription extends AbstractMigration {
	public function up() {
		$this->execute(
			'CREATE FUNCTION notify_subscriptions() RETURNS trigger
				LANGUAGE plpgsql
				AS $$
			begin
				INSERT INTO notifications (subscription_id, notified_at) VALUES (new.id, NOW());
				return new;
			end
			$$;'
		);
		$this->execute(
			'CREATE TRIGGER "subscriptions_au" AFTER UPDATE ON "subscriptions" FOR EACH ROW
			EXECUTE PROCEDURE notify_subscriptions();'
		);
	}

	public function down() {
		$this->execute('DROP TRIGGER "subscriptions_au" ON "subscriptions";');
		$this->execute('DROP FUNCTION notify_subscriptions();');
	}
}
