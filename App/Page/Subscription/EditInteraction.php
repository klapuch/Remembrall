<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscription;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class EditInteraction extends Page\Layout {
	public function response(array $subscription): Output\Template {
		try {
			(new Form\HarnessedForm(
				new Subscription\EditForm(
					new Subscribing\FakeSubscription(),
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($subscription): void {
					(new Subscribing\OwnedSubscription(
						new Subscribing\StoredSubscription(
							(int) $subscription['id'],
							$this->database
						),
						(int) $subscription['id'],
						$this->user,
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
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'subscriptions')
					),
					['success' => 'Subscription has been edited'],
					$_SESSION
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