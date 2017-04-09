<?php
declare(strict_types = 1);
namespace Remembrall\Constraint;

use Klapuch\Validation;

final class IntervalRule implements Validation\Rule {
	public const MIN = 30,
		MAX = 1439;

	public function satisfied($subject): bool {
		return (new Validation\ChainedRule(
			new Validation\NegateRule(new Validation\EmptyRule()),
			new Validation\RangeRule(self::MIN, self::MAX)
		))->satisfied($subject);
	}

	public function apply($subject): void {
		(new Validation\ChainedRule(
			new Validation\FriendlyRule(
				new Validation\NegateRule(new Validation\EmptyRule()),
				'Interval must be filled'
			),
			new Validation\FriendlyRule(
				new Validation\RangeRule(self::MIN, self::MAX),
				sprintf('Interval must be greater than %d', self::MIN)
			)
		))->apply($subject);
	}
}