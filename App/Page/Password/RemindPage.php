<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class RemindPage extends Page\Layout {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall forgotten password',
		CONTENT = __DIR__ . '/../../Messages/Password/Remind/content.xsl',
		CONSTRAINT = __DIR__ . '/../../Messages/Password/Remind/constraint.xsd';

	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Password\RemindForm(
							$this->url,
							$this->csrf,
							new Form\Backup($_SESSION, $_POST)
						)
					),
					new Response\FlashResponse(),
					new Response\PermissionResponse(),
					new Response\IdentifiedResponse($this->user)
				),
				__DIR__ . '/templates/remind.xml',
				__DIR__ . '/../templates/layout.xml'
			),
			$this->user,
			$this->url
		);
	}

	public function submitRemind(array $credentials): Application\Response {
		try {
			(new Form\HarnessedForm(
				new Password\RemindForm($this->url, $this->csrf, new Form\Backup($_SESSION, $_POST)),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					$password = (new Access\LimitedForgottenPasswords(
						new Access\SecureForgottenPasswords($this->database),
						$this->database
					))->remind($credentials['email']);
					(new Mail\SendmailMailer())->send(
						(new Mail\Message())
							->setFrom(self::SENDER)
							->addTo($credentials['email'])
							->setSubject(self::SUBJECT)
							->setHtmlBody(
								(new Output\XsltTemplate(
									self::CONTENT,
									$password->print(
										new Output\ValidXml(
											new Output\Xml([], 'remind'),
											self::CONSTRAINT
										)
									)
								))->render(['base_url' => $this->url->reference()])
							)
					);
				}
			))->validate();
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'password/remind')
				),
				['danger' => 'Password reset has been sent to your email'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'password/remind')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}