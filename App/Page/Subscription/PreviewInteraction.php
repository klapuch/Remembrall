<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Http;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;

final class PreviewInteraction extends Page\Layout {
	public function response(array $subscription): Output\Template {
		try {
			(new Form\HarnessedForm(
				new Subscription\NewForm(
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($subscription): void {
					(new Storage\Transaction($this->database))->start(
						function() use ($subscription): void {
							$url = new Uri\CachedUri(
								new Uri\NormalizedUrl(
									new Uri\SchemeForcedUrl(
										new Uri\ValidUrl($_SESSION['part']['url']),
										['http', 'https']
									)
								)
							);
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
								new Web\SafeParts(
									new Web\CollectiveParts($this->database),
									$this->database
								),
								new Misc\LoggingCallback($this->logs)
							))->add(
								new Web\HtmlPart(
									new Web\MatchingExpression(
										new Web\SuitableExpression(
											$_SESSION['part']['language'],
											$page,
											$_SESSION['part']['expression']
										)
									),
									$page
								),
								$url,
								$_SESSION['part']['expression'],
								$_SESSION['part']['language']
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
								$_SESSION['part']['expression'],
								$_SESSION['part']['language'],
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
			return new Application\HtmlTemplate(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, $this->url->path())
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}