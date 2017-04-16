<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Nette\Mail;
use Remembrall\Form\Password;
use Remembrall\Page;
use Remembrall\Response;

final class RemindPage extends Page\Layout {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall forgotten password',
		CONTENT = __DIR__ . '/../../Messages/Password/Remind/content.xsl';

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

	public function submitRemind(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Password\RemindForm($this->url, $this->csrf, new Form\Backup($_SESSION, $_POST)),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					(new Access\LimitedForgottenPasswords(
						new Access\SecureForgottenPasswords($this->database),
						$this->database
					))->remind($credentials['email']);
					(new Access\EmailedForgottenPasswords(
						$this->database,
						new Mail\SendmailMailer(),
						(new Mail\Message())->setFrom(self::SENDER)->setSubject(self::SUBJECT),
						new Output\XsltTemplate(
							self::CONTENT,
							new Output\Xml(
								['base_url' => $this->url->reference()],
								'remind'
							)
						)
					))->remind($credentials['email']);
				}
			))->validate();
			$this->flashMessage('Password reset has been sent to your email', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->redirect('password/remind');
		}
	}
}