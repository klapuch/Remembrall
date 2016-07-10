<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Nette\Mail;
use Remembrall\Model\{
	Access, Subscribing
};

//TODO
final class SubscribingEmail extends SecureControl {
	/**
	 * @secured
	 */
	public function handleSend(
		Access\Subscriber $subscriber,
		Subscribing\Part $part
	) {
		$template = parent::createTemplate();
		$template->setFile(__DIR__ . '/subscribing.latte');
		$template->part = $part;
		(new Mail\SendmailMailer())->send(
			(new Mail\Message())
				->setFrom('remembrall@remembrall.cz', 'Remembrall')
				->addTo($subscriber->email())
				->setSubject(
					sprintf(
						'Changes on "%s" with "%s" expression',
						$part->source()->url(),
						(string)$part->expression()
					)
				)
				->setHtmlBody((string)$template)
		);
	}
}
