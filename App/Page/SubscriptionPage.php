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
		echo (new Output\XsltTemplate(
			self::TEMPLATES . '/Subscription/default.xsl',
			new Output\RemoteXml(self::TEMPLATES . '/Subscription/default.xml')
		))->render(['baseUrl' => $this->baseUrl->reference()]);
	}

	public function actionSubscribe() {
		try {
			$url = new Uri\NormalizedUrl(
				new Uri\ProtocolBasedUrl(
					new Uri\ReachableUrl(
						new Uri\ValidUrl($this->request->post['url'])
					),
					['http', 'https', '']
				)
			);
			$page = (new Subscribing\WebPages($this->database))->add(
				$url,
				new Subscribing\LoggedPage(
					new Subscribing\CachedPage(
						$url,
						new Subscribing\HtmlWebPage(
							new Http\BasicRequest('GET', $url)
						),
						$this->database
					),
					$this->logger
				)
			);
			(new Storage\PostgresTransaction($this->database))->start(
				function() use ($page, $url) {
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts($this->database),
						$this->logger
					))->add(
						new Subscribing\CachedPart(
							new Subscribing\HtmlPart(
								new Subscribing\MatchingExpression(
									new Subscribing\XPathExpression(
										$page,
										$this->request->post['expression']
									)
								),
								$page
							),
							new Storages\MemoryStorage()
						),
						$url,
						$this->request->post['expression']
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
						$this->request->post['expression'],
						new Time\FutureInterval(
							new Time\LimitedInterval(
								new Time\TimeInterval(
									new \DateTimeImmutable(),
									new \DateInterval(
										sprintf(
											'PT%dM',
											$this->request->post['interval']
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
			$this->redirect('Parts:default');
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}