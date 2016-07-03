<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeReports implements Reports {
	private $reports;

	public function __construct(array $reports = []) {
		$this->reports = $reports;
	}

	public function iterate(): array {
		return $this->reports;
	}

	public function archive(Part $part) {
		
	}
}