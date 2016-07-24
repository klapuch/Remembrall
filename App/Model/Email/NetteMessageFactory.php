<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Nette\Mail;

/**
 * Factory responsible for converting Mail\Message to Message class
 */
final class NetteMessageFactory {
	private $message;

	public function __construct(Message $message) {
		$this->message = $message;
	}

	/**
	 * Create the instance of Mail\Message
	 * @return Mail\Message
	 */
	public function create(): Mail\Message {
		$message = new Mail\Message();
		$message->setFrom($this->message->sender())
			->setSubject($this->message->subject())
			->setBody($this->message->content());
		foreach($this->message->recipients()->iterate() as $subscriber)
			$message->addBcc($subscriber->email());
		return $message;
	}
}