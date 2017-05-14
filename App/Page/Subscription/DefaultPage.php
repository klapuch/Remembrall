<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Http;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;

final class DefaultPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Subscription\NewForm(
							$this->url,
							$this->csrf,
							new Form\Backup($_SESSION, $_POST)
						)
					),
					new Response\FlashResponse(),
					new Response\PermissionResponse(),
					new Response\IdentifiedResponse($this->user)
				),
				__DIR__ . '/templates/default.xml',
				__DIR__ . '/../templates/layout.xml'
			),
			$this->user,
			$this->url
		);
	}

	public function submitDefault(array $subscription): Application\Response {
		try {
			(new Form\HarnessedForm(
				new Subscription\NewForm(
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
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
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['success' => 'Subscription has been added'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}