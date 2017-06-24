<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Http;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;

final class DefaultPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Subscription\PreviewForm(
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

	public function submitDefault(array $part): Application\Response {
		try {
			(new Form\HarnessedForm(
				new Subscription\PreviewForm(
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($part): void {
					$url = new Uri\CachedUri(
						new Uri\NormalizedUrl(
							new Uri\ReachableUrl(
								new Uri\SchemeForcedUrl(
									new Uri\ValidUrl($part['url']),
									['http', 'https']
								)
							)
						)
					);
					$page = new Web\FrugalPage(
						$url,
						new Web\HtmlWebPage(new Http\BasicRequest('GET', $url)),
						$this->database
					);
					(new Web\HarnessedParts(
						new Web\TemporaryParts($this->redis),
						new Misc\LoggingCallback($this->logs)
					))->add(
						new Web\HtmlPart(
							new Web\MatchingExpression(
								new Web\SuitableExpression(
									$part['language'],
									$page,
									$part['expression']
								)
							),
							$page
						),
						$url,
						$part['expression'],
						$part['language']
					);
					$_SESSION['part'] = $part;
				}
			))->validate();
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'subscription/preview')
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