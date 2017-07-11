<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

class UniqueExpressionAndUrl extends AbstractMigration {
	public function up() {
		$this->execute(
			'ALTER TABLE "parts"
			ADD CONSTRAINT "parts_page_url_expression"
			UNIQUE ("page_url", "expression")'
		);
	}

	public function down() {
		$this->execute(
			'ALTER TABLE "parts"
			DROP CONSTRAINT "parts_page_url_expression"'
		);
	}
}
