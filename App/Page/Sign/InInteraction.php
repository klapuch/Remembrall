<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Internal;
use Klapuch\Uri;
use Klapuch\Output;
use Remembrall\Form\Sign;
use Remembrall\Page;
use Remembrall\Response;

final class InInteraction extends Page\Layout {
	public function response(array $credentials): Output\Template {
		try {
			(new Form\HarnessedForm(
				new Sign\InForm($this->url, $this->csrf, new Form\Backup($_SESSION, $_POST)),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					(new Access\SessionEntrance(
						new Access\VerifiedEntrance(
							$this->database,
							new Access\SecureEntrance(
								$this->database,
								new Encryption\AES256CBC(
									$this->configuration['KEYS']['password']
								)
							)
						),
						$_SESSION,
						new Internal\CookieExtension($this->configuration['PROPRIETARY_SESSIONS'])
					))->enter([$credentials['email'], $credentials['password']]);
				}
			))->validate();
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'subscriptions')
					),
					['success' => 'You have been logged in'],
					$_SESSION
				)
			);
		} catch (\Throwable $ex) {
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