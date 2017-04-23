<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Invitation which has not been used yet
 */
final class UnusedInvitation implements Invitation {
	private $code;
	private $database;
	private $origin;

	public function __construct(Invitation $origin, string $code, \PDO $database) {
		$this->code = $code;
		$this->database = $database;
		$this->origin = $origin;
	}

	public function accept(): void {
		if ($this->accepted($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is accepted or does not exist'
			);
		}
		$this->origin->accept();
	}

	public function deny(): void {
		if ($this->decided($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is denied or does not exist'
			);
		}
		$this->origin->deny();
	}

	public function print(Output\Format $format): Output\Format {
		if ($this->decided($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is denied or does not exist'
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
			AND accepted = TRUE',
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