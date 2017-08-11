<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Gajus\Dindent;
use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Web;
use Remembrall\Page;
use Remembrall\Response;
use Texy;

final class PreviewPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		if (isset($_SESSION['part'], $_SESSION['part']['url'], $_SESSION['part']['expression'], $_SESSION['part']['language'])) {
			try {
				return new Application\HtmlTemplate(
					new Response\AuthenticatedResponse(
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
					),
					__DIR__ . '/templates/preview.xsl'
				);
			} catch (\UnexpectedValueException $ex) {
				return new Application\HtmlTemplate(
					new Response\InformativeResponse(
						new Response\RedirectResponse(
							new Response\EmptyResponse(),
							new Uri\RelativeUrl($this->url, 'subscription')
						),
						['danger' => $ex->getMessage()],
						$_SESSION
					)
				);
			}
		}
		return new Application\HtmlTemplate(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscription')
				),
				['danger' => 'Missing referenced part'],
				$_SESSION
			)
		);
	}
}