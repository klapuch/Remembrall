<?php
declare(strict_types = 1);
namespace Remembrall\Constraint;

use Klapuch\Validation;

final class EmailRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return (new Validation\ChainedRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			new Validation\EmailRule()
		))->satisfied($subject);
	}

	public function apply($subject): void {
		(new Validation\ChainedRule(
			new Validation\FriendlyRule(
				new Validation\NegateRule(new Validation\EmptyRule()),
				'Email must be filled'
			),
			new Validation\FriendlyRule(
				new Validation\EmailRule(),
				'Email must be valid'
			)
		))->apply($subject);
	}
}