<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Remembrall\Model\Access;

final class FakeMessage implements Message {
	private $recipients;
	private $sender;
	private $subject;
	private $content;

	public function __construct(
		Access\Subscribers $recipients = null,
		string $sender = null,
		string $subject = null,
		string $content = null
	) {
		$this->recipients = $recipients;
		$this->sender = $sender;
		$this->subject = $subject;
		$this->content = $content;
	}

	public function sender(): string {
		return $this->sender;
	}

	public function recipients(): Access\Subscribers {
		return $this->recipients;
	}

	public function subject(): string {
		return $this->subject;
	}

	public function content(): string {
		return $this->content;
	}
}