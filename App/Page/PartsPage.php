<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Output;
use Remembrall\Model\Subscribing;

final class PartsPage extends BasePage {
	public function renderDefault() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Parts/default.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Parts/default.xsl',
			new Output\MergedXml(
				$xml,
				new \SimpleXMLElement(
					(string)new Output\WrappedXml(
						'subscriptions',
						...(new Subscribing\OwnedSubscriptions(
							$this->subscriber,
							$this->database
						))->print(new Output\Xml([], 'subscription'))
					)
				)
			)
		))->render([
			'baseUrl' => $this->url->reference(),
		]);
	}

	public function renderDelete() {
		try {
			(new Subscribing\OwnedSubscription(
				new Subscribing\PostgresSubscription($_GET['id'], $this->database),
				$_GET['id'],
				$this->subscriber,
				$this->database
			))->cancel();
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}