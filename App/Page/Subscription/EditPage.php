<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class EditPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
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
		);
	}

	public function submitEdit(array $subscription, array $parameters): Application\Response {
		try {
			$id = $parameters['id'];
			(new Form\HarnessedForm(
				new Subscription\EditForm(
					new Subscribing\FakeSubscription(),
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($subscription, $id): void {
					(new Subscribing\StoredSubscription(
						$id,
						$this->database
					))->edit(
						new Time\TimeInterval(
							new \DateTimeImmutable(),
							new \DateInterval(
								sprintf('PT%dM', $subscription['interval'])
							)
						)
					);
				}
			))->validate();
			$this->flashMessage('Subscription has been edited', 'success');
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'subscriptions')
			);
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, $this->url->path())
			);
		}
	}
}