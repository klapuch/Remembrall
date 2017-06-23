<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Gajus\Dindent;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Time;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;
use Remembrall\Model\Subscribing;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Klapuch\Form;
use Texy;

final class PreviewPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(), // TODO!!!!!!!!!!!!!!
					new Response\PlainResponse(
						(new Web\FormattedPart(
							new Web\TemporaryPart(
								$this->redis,
								new Uri\NormalizedUrl(
									new Uri\SchemeForcedUrl(
										new Uri\ValidUrl(
											$_SESSION['part']['url']
										),
										['http', 'https']
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
	}

	public function submitPreview(array $subscription): Application\Response {
		try {
			(new Form\HarnessedForm(
				new Subscription\PreviewForm(
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($subscription): void {
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
						new Uri\NormalizedUrl(
							new Uri\SchemeForcedUrl(
								new Uri\ValidUrl($_SESSION['part']['url']),
								['http', 'https']
							)
						),
						$_SESSION['part']['expression'],
						$_SESSION['part']['language'],
						new Time\TimeInterval(
							new \DateTimeImmutable(),
							new \DateInterval(
								sprintf('PT%dM', $subscription['interval'])
							)
						)
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