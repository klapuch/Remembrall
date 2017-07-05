<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

final class VisitsUnloggedTables extends AbstractMigration {
	public function up() {
		$this->execute('ALTER TABLE "part_visits" SET UNLOGGED');
		$this->execute('ALTER TABLE "page_visits" SET UNLOGGED');
	}

	public function down() {
		$this->execute('ALTER TABLE "part_visits" SET LOGGED');
		$this->execute('ALTER TABLE "page_visits" SET LOGGED');
	}
}

