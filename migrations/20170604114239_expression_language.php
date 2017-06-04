<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

final class ExpressionLanguage extends AbstractMigration {
	public function up() {
		$this->execute('CREATE TYPE languages AS ENUM (\'xpath\', \'css\')');
		$this->execute('ALTER TABLE "parts" ADD "language" languages NOT NULL DEFAULT \'xpath\'');
		$this->execute(
			'ALTER TABLE "parts"
			ADD CONSTRAINT "parts_page_url_expression_language" UNIQUE ("page_url", "expression", "language"),
			DROP CONSTRAINT "parts_page_url_expression"'
		);
	}

	public function down() {
		$this->execute('ALTER TABLE "parts" DROP "language"');
		$this->execute('DROP TYPE "languages"');
		$this->execute('DROP CONSTRAINT "parts_page_url_expression_language"');
	}
}
