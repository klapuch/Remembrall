<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

/**
 * Constant report without roundtrips
 */
final class ConstantReport implements Report {
	private $recipient;
	private $content;
	private $sentAt;

	public function __construct(
		Access\Subscriber $recipient,
		Part $content,
		\DateTimeImmutable $sentAt
	) {
		$this->recipient = $recipient;
		$this->content = $content;
		$this->sentAt = $sentAt;
	}

	public function recipient(): Access\Subscriber {
		return $this->recipient;
	}

	public function content(): Part {
		return $this->content;
	}

	public function sentAt(): \DateTimeImmutable {
		return $this->sentAt;
	}
}