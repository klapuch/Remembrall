<?php
declare(strict_types = 1);
namespace Remembrall\Constraint;

use Klapuch\Validation;

final class UrlRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return (new Validation\NegateRule(
			new Validation\EmptyRule()
		))->satisfied($subject);
	}

	public function apply($subject): void {
		(new Validation\FriendlyRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			'Url must be filled'
		))->apply($subject);
	}
}