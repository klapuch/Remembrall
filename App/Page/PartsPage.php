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
			'baseUrl' => $this->baseUrl->reference(),
		]);
	}

	public function handleDelete(int $id) {
		try {
			(new Subscribing\OwnedSubscription(
				new Subscribing\PostgresSubscription($id, $this->database),
				$id,
				$this->subscriber,
				$this->database
			))->cancel();
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}