<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Http, Output, Storage, Time, Uri
};
use Nette\Caching\Storages;
use Remembrall\Model\Subscribing;

final class SubscriptionPage extends BasePage {
	public function renderDefault() {
		$xml = new \DOMDocument();
		$xml->load(self::TEMPLATES . '/Subscription/default.xml');
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Subscription/default.xsl',
			new Output\MergedXml($xml, ...$this->layout())
		))->render();
	}

	public function actionSubscribe(array $subscription) {
		try {
			$url = new Uri\NormalizedUrl(
				new Uri\ProtocolBasedUrl(
					new Uri\ReachableUrl(
						new Uri\ValidUrl($subscription['url'])
					),
					['http', 'https', '']
				)
			);
			(new Storage\PostgresTransaction($this->database))->start(
				function() use ($url, $subscription) {
					$page = (new Subscribing\WebPages($this->database))->add(
						$url,
						new Subscribing\LoggedPage(
							new Subscribing\FrugalPage(
								$url,
								new Subscribing\HtmlWebPage(
									new Http\BasicRequest('GET', $url)
								),
								$this->database
							),
							$this->logger
						)
					);
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts($this->database),
						$this->logger
					))->add(
						new Subscribing\CachedPart(
							new Subscribing\HtmlPart(
								new Subscribing\MatchingExpression(
									new Subscribing\XPathExpression(
										$page,
										$subscription['expression']
									)
								),
								$page
							),
							new Storages\MemoryStorage()
						),
						$url,
						$subscription['expression']
					);
					(new Subscribing\LoggedSubscriptions(
						new Subscribing\LimitedSubscriptions(
							new Subscribing\OwnedSubscriptions(
								$this->subscriber,
								$this->database
							),
							$this->subscriber,
							$this->database
						),
						$this->logger
					))->subscribe(
						$url,
						$subscription['expression'],
						new Time\FutureInterval(
							new Time\LimitedInterval(
								new Time\TimeInterval(
									new \DateTimeImmutable(),
									new \DateInterval(
										sprintf(
											'PT%dM',
											$subscription['interval']
										)
									)
								),
								[
									new Time\TimeInterval(
										new \DateTimeImmutable(),
										new \DateInterval('PT30M')
									),
									new Time\TimeInterval(
										new \DateTimeImmutable(),
										new \DateInterval('PT9000M')
									),
								]
							)
						)
					);
				}
			);
			header('Location: ' . $this->url->reference() . 'parts');
			exit;
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}