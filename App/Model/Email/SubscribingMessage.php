<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Dibi;
use Nette\Application\UI;
use Remembrall\Model\{
	Access, Subscribing
};

final class SubscribingMessage implements Message {
	private $part;
	private $templateFactory;
	private $url;
	private $expression;
	private $database;

	public function __construct(
		Subscribing\Part $part,
		string $url,
		string $expression,
		UI\ITemplateFactory $templateFactory,
		Dibi\Connection $database
	) {
		$this->part = $part;
		$this->templateFactory = $templateFactory;
		$this->url = $url;
		$this->expression = $expression;
		$this->database = $database;
	}

	public function sender(): string {
		return 'Remembrall <remembrall@remembrall.org>';
	}

	public function recipients(): Access\Subscribers {
		return new Access\OutdatedSubscribers(
			new Access\FakeSubscribers(),
			$this->url,
			$this->expression,
			$this->database
		);
	}

	public function subject(): string {
		return sprintf(
			'Changes occurred on "%s" page with "%s" expression',
			$this->url,
			$this->expression
		);
	}

	public function content(): string {
		$template = $this->templateFactory->createTemplate();
		$template->setFile(
			__DIR__ . '/../../Page/templates/Email/subscribing.latte'
		);
		$template->part = $this->part;
		$template->url = $this->url;
		$template->expression = $this->expression;
		return (string)$template;
	}
}