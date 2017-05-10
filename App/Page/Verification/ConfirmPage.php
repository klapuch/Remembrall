<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Uri;
use Remembrall\Page;
use Remembrall\Response;

final class ConfirmPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		try {
			(new Access\ExistingVerificationCode(
				new Access\ThrowawayVerificationCode(
					$parameters['code'],
					$this->database
				),
				$parameters['code'],
				$this->database
			))->use();
			$this->flashMessage('Your code has been confirmed', 'success');
			(new Access\SessionEntrance(
				new Access\WelcomingEntrance($this->database),
				$_SESSION,
				new Internal\CookieExtension($this->configuration['PROPRIETARY_SESSIONS'])
			))->enter([$parameters['code']]);
			$this->flashMessage('You have been logged in', 'success');
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'subscriptions')
			);
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'sign/in')
			);
		}
	}
}