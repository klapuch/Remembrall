<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Invitation which has not been used yet
 */
final class UnusedInvitation implements Invitation {
	private $origin;
	private $code;
	private $database;

	public function __construct(Invitation $origin, string $code, \PDO $database) {
		$this->origin = $origin;
		$this->code = $code;
		$this->database = $database;
	}

	public function accept(): void {
		if ($this->accepted($this->code)) {
			throw new \UnexpectedValueException(
				sprintf(
					'The invitation with code "%s" is accepted or does not exist',
					$this->code
				)
			);
		}
		$this->origin->accept();
	}

	public function decline(): void {
		if ($this->decided($this->code)) {
			throw new \UnexpectedValueException(
				sprintf(
					'The invitation with code "%s" is declined or does not exist',
					$this->code
				)
			);
		}
		$this->origin->decline();
	}

	public function print(Output\Format $format): Output\Format {
		if ($this->decided($this->code)) {
			throw new \UnexpectedValueException(
				sprintf(
					'The invitation with code "%s" is declined or does not exist',
					$this->code
				)
			);
		}
		return $this->origin->print($format);
	}

	private function accepted(string $code): bool {
		return !$this->known($this->code) || (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?
			AND accepted IS TRUE',
			[$code]
		))->field();
	}

	private function decided(string $code): bool {
		return !$this->known($this->code) || (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?
			AND decided_at IS NOT NULL',
			[$code]
		))->field();
	}

	private function known(string $code): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?',
			[$code]
		))->field();
	}
}