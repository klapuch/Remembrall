<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Klapuch\{
	Storage, Output
};
use Remembrall\Model\{
	Access, Subscribing
};

final class SubscribingMessage implements Message {
	private $part;
	private $database;

	public function __construct(
		Subscribing\Part $part,
		Storage\Database $database
	) {
		$this->part = $part;
		$this->database = $database;
	}

	public function sender(): string {
		return 'Remembrall <remembrall@remembrall.org>';
	}

	public function recipients(): Access\Subscribers {
		$part = $this->part->print(new Output\Xml([], 'part'));
		return new Access\OutdatedSubscribers(
			new Access\FakeSubscribers(),
			current($part->valueOf('url')),
			current($part->valueOf('expression')),
			$this->database
		);
	}

	public function subject(): string {
		$part = $this->part->print(new Output\Xml([], 'part'));
		return sprintf(
			'Changes occurred on "%s" page with "%s" expression',
			current($part->valueOf('url')),
			current($part->valueOf('expression'))
		);
	}

	public function content(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../Page/templates/Email/subscribing.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xslt->setParameter('', 'content', $this->part->content());
		return $xslt->transformToXml(
			new \SimpleXMLElement(
				(string)$this->part->print(new Output\Xml([], 'part'))
			)
		);
	}
}