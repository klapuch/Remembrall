<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Internal;
use Klapuch\Uri;
use Remembrall\Form\Sign;
use Remembrall\Page;
use Remembrall\Response;

final class InPage extends Page\Layout {
	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Sign\InForm(
							$this->url,
							$this->csrf,
							new Form\Backup($_SESSION, $_POST)
						)
					),
					new Response\PermissionResponse(),
					new Response\IdentifiedResponse($this->user),
					new Response\FlashResponse()
				),
				__DIR__ . '/templates/in.xml',
				__DIR__ . '/../templates/layout.xml'
			),
			$this->user,
			$this->url
		);
	}

	public function submitIn(array $credentials): Application\Response {
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
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['success' => 'You have been logged in'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'sign/in')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}