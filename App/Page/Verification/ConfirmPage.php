<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Page;
use Remembrall\Response;

final class ConfirmPage extends Page\Layout {
	public function template(array $parameters): Output\Template {
		try {
			(new Access\ExistingVerificationCode(
				new Access\ThrowawayVerificationCode(
					$parameters['code'],
					$this->database
				),
				$parameters['code'],
				$this->database
			))->use();
			(new Access\SessionEntrance(
				new Access\WelcomingEntrance($this->database),
				$_SESSION,
				new Internal\CookieExtension($this->configuration['PROPRIETARY_SESSIONS'])
			))->enter([$parameters['code']]);
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\InformativeResponse(
						new Response\RedirectResponse(
							new Response\EmptyResponse(),
							new Uri\RelativeUrl($this->url, 'subscriptions')
						),
						['success' => 'Your code has been confirmed'],
						$_SESSION
					),
					['success' => 'You have been logged in'],
					$_SESSION
				)
			);
		} catch (\UnexpectedValueException | \LogicException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/in')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}