<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Http;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Form;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Web;
use Remembrall\Page;

final class DefaultPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Subscription\NewForm(
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitDefault(array $subscription): void {
		try {
			(new Form\HarnessedForm(
				new Subscription\NewForm(
					$this->url,
					$this->csrf,
					$this->backup
				),
				$this->backup,
				function() use ($subscription): void {
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
							$page = (new Web\HarnessedPages(
								new Web\UniquePages($this->database),
								new Misc\LoggingCallback($this->logs)
							))->add(
								$url,
								new Web\FrugalPage(
									$url,
									new Web\HtmlWebPage(
										new Http\BasicRequest('GET', $url)
									),
									$this->database
								)
							);
							(new Web\HarnessedParts(
								new Web\CollectiveParts($this->database),
								new Misc\LoggingCallback($this->logs)
							))->add(
								new Web\HtmlPart(
									new Web\MatchingExpression(
										new Web\XPathExpression(
											$page,
											$subscription['expression']
										)
									),
									$page
								),
								$url,
								$subscription['expression']
							);
							(new Subscribing\HarnessedSubscriptions(
								new Subscribing\LimitedSubscriptions(
									new Subscribing\OwnedSubscriptions(
										$this->user,
										$this->database
									),
									$this->user,
									$this->database
								),
								new Misc\LoggingCallback($this->logs)
							))->subscribe(
								$url,
								$subscription['expression'],
								new Time\TimeInterval(
									new \DateTimeImmutable(),
									new \DateInterval(
										sprintf(
											'PT%dM',
											$subscription['interval']
										)
									)
								)
							);
						}
					);
				}
			))->validate();
			$this->flashMessage('Subscription has been added', 'success');
			$this->redirect('subscriptions');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('subscription');
		}
	}
}