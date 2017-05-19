<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

final class ForgottenPasswordsExpireAtColumn extends AbstractMigration {
	public function up() {
		$table = $this->table('forgotten_passwords');
		$table->addColumn('expire_at', 'timestamp', ['default' => 'now()'])->update();
		$table->changeColumn('expire_at', 'timestamp')->update();
	}
}