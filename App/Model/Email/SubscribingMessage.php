<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Klapuch\Storage;
use Nette\Application\UI;
use Remembrall\Model\{
	Access, Subscribing
};

final class SubscribingMessage implements Message {
	private $part;
	private $templateFactory;
	private $database;

	public function __construct(
		Subscribing\Part $part,
		UI\ITemplateFactory $templateFactory,
		Storage\Database $database
	) {
		$this->part = $part;
		$this->templateFactory = $templateFactory;
		$this->database = $database;
	}

	public function sender(): string {
		return 'Remembrall <remembrall@remembrall.org>';
	}

	public function recipients(): Access\Subscribers {
		$visualPart = $this->part->print();
		return new Access\OutdatedSubscribers(
			new Access\FakeSubscribers(),
			$visualPart['url'],
			(string)$visualPart['expression'],
			$this->database
		);
	}

	public function subject(): string {
		$visualPart = $this->part->print();
		return sprintf(
			'Changes occurred on "%s" page with "%s" expression',
			$visualPart['url'],
			(string)$visualPart['expression']
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