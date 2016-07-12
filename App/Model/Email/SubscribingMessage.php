<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Dibi;
use Nette\Application\UI;
use Nette\Mail;
use Remembrall\Model\{
	Access, Subscribing
};

final class SubscribingMessage implements Message {
	private $templateFactory;
	private $part;
	private $database;

	public function __construct(
		Subscribing\Part $part,
		UI\ITemplateFactory $templateFactory,
		Dibi\Connection $database
	) {
		$this->templateFactory = $templateFactory;
		$this->part = $part;
		$this->database = $database;
	}

	public function sender(): string {
		return 'Remembrall <remembrall@remembrall.org>';
	}

	public function recipients(): Access\Subscribers {
		return new Access\PartSharedSubscribers(
			new Access\FakeSubscribers(),
			$this->part,
			$this->database
		);
	}

	public function subject(): string {
		return sprintf(
			'Changes occurred on "%s" page with "%s" expression',
			$this->part->source()->url(),
			(string)$this->part->expression()
		);
	}

	public function content(): string {
		$template = $this->templateFactory->createTemplate();
		$template->setFile(
			__DIR__ . '/../../Page/templates/Email/subscribing.latte'
		);
		$template->part = $this->part;
		return (string)$template;
	}
}