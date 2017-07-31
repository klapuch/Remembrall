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

final class RemindInteraction extends Page\Layout {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall forgotten password',
		CONTENT = __DIR__ . '/../../Messages/Password/Remind/content.xsl',
		CONSTRAINT = __DIR__ . '/../../Messages/Password/Remind/constraint.xsd';

	public function response(array $credentials): Output\Template {
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
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/in')
					),
					['danger' => 'Password reset has been sent to your email'],
					$_SESSION
				)
			);
		} catch (\UnexpectedValueException | \OverflowException $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'password/remind')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}