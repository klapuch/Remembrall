<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeInvitation implements Invitation {
	public function accept(): void {
	}

	public function deny(): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}