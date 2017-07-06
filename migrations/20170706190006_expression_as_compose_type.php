<?php
declare(strict_types = 1);
use Phinx\Migration\AbstractMigration;

final class ExpressionAsComposeType extends AbstractMigration {
	public function up() {
		$this->execute(
			'CREATE TYPE expression AS (
				value character varying,
				language languages
			)'
		);
		$this->execute('ALTER TABLE parts ADD COLUMN expression2 expression');
		$this->execute('UPDATE parts SET expression2 = ROW(expression, language)');
		$this->execute('ALTER TABLE parts DROP COLUMN expression');
		$this->execute('ALTER TABLE parts DROP COLUMN language');
		$this->execute('ALTER TABLE parts RENAME COLUMN expression2 TO expression');
		$this->execute('ALTER TABLE parts ALTER COLUMN expression SET NOT NULL');
	}

	public function down() {
		$this->execute('ALTER TABLE parts ADD COLUMN expression2 character varying');
		$this->execute('ALTER TABLE parts ADD COLUMN language languages');
		$this->execute(
			'UPDATE parts
			SET expression2 = (expression).value,
			language = (expression).language'
		);
		$this->execute('ALTER TABLE parts DROP COLUMN expression');
		$this->execute('DROP TYPE expression');
		$this->execute('ALTER TABLE parts RENAME COLUMN expression2 TO expression');
		$this->execute('ALTER TABLE parts ALTER COLUMN expression SET NOT NULL');
		$this->execute('ALTER TABLE parts ALTER COLUMN language SET NOT NULL');
	}
}