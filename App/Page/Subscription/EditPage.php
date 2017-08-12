<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class EditPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		return new Application\HtmlTemplate(
			new Response\WebAuthentication(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\SafeResponse(
							new Response\FormResponse(
								new Subscription\EditForm(
									new Subscribing\OwnedSubscription(
										new Subscribing\StoredSubscription(
											$parameters['id'],
											$this->database
										),
										$parameters['id'],
										$this->user,
										$this->database
									),
									$this->url,
									$this->csrf,
									new Form\Backup($_SESSION, $_POST)
								)
							),
							new Uri\RelativeUrl($this->url, 'subscriptions'),
							$_SESSION
						),
						new Response\FlashResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/edit.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/edit.xsl'
		);
	}
}