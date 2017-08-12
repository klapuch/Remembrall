<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Http;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;

final class DefaultInteraction extends Page\Layout {
	public function template(array $part): Output\Template {
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
			return new Application\HtmlTemplate(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscription/preview')
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