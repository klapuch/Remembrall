<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\{
	Http, Output, Storage, Time, Uri
};
use Remembrall\Control;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DefaultPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->appendChild($dom->createElement('forms'));
		return new Output\MergedXml(
			$dom,
			new \SimpleXMLElement(
				sprintf(
					'<form name="subscribing">%s</form>',
					(new Control\SubscribingForm(
						$this->url,
						$this->csrf,
						$this->storage
					))->render()
				)
			)
		);
	}

	public function submitDefault(array $subscription): void {
		try {
			(new Control\SubscribingForm(
				$this->url,
				$this->csrf,
				$this->storage
			))->validate();
			$url = new Uri\NormalizedUrl(
				new Uri\ReachableUrl(
					new Uri\SchemeForcedUrl(
						new Uri\ValidUrl($subscription['url']),
						['http', 'https']
					)
				)
			);
			(new Storage\Transaction($this->database))->start(
				function() use ($url, $subscription): void {
					$page = (new Subscribing\LoggedPages(
						new Subscribing\UniquePages($this->database),
						$this->logs
					))->add(
						$url,
						new Subscribing\FrugalPage(
							$url,
							new Subscribing\HtmlWebPage(
								new Http\BasicRequest('GET', $url)
							),
							$this->database
						)
					);
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts($this->database),
						$this->logs
					))->add(
						new Subscribing\HtmlPart(
							new Subscribing\MatchingExpression(
								new Subscribing\XPathExpression(
									$page,
									$subscription['expression']
								)
							),
							$page
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
						$this->logs
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
			$this->flashMessage('Subscription has been added', 'success');
			$this->redirect('subscriptions');
		} catch(\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('subscription');
		}
	}
}