<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Gajus\Dindent;
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
use Texy;

final class PreviewPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		if (isset($_SESSION['part'], $_SESSION['part']['url'], $_SESSION['part']['expression'], $_SESSION['part']['language'])) {
			try {
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
							new Response\PlainResponse(
								(new Web\FormattedPart(
									new Web\TemporaryPart(
										$this->redis,
										new Uri\CachedUri(
											new Uri\NormalizedUrl(
												new Uri\SchemeForcedUrl(
													new Uri\ValidUrl(
														$_SESSION['part']['url']
													),
													['http', 'https']
												)
											)
										),
										$_SESSION['part']['expression'],
										$_SESSION['part']['language']
									),
									new Texy\Texy(),
									new Dindent\Indenter()
								))->print(new Output\Xml([], 'part'))
							),
							new Response\FlashResponse(),
							new Response\PermissionResponse(),
							new Response\IdentifiedResponse($this->user)
						),
						__DIR__ . '/templates/preview.xml',
						__DIR__ . '/../templates/layout.xml'
					),
					$this->user,
					$this->url
				);
			} catch (\UnexpectedValueException $ex) {
				return new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'subscription')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				);
			}
		}
		return new Response\InformativeResponse(
			new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'subscription')
			),
			['danger' => 'Missing referenced part'],
			$_SESSION
		);
	}

	public function submitPreview(array $subscription): Application\Response {
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
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'subscriptions')
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, $this->url->path())
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}