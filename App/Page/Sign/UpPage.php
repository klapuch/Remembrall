<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form\Backup;
use Klapuch\Output;
use Klapuch\Storage;
use Nette\Mail;
use Remembrall\Form;
use Remembrall\Form\Sign;
use Remembrall\Page;
use Remembrall\Response;

final class UpPage extends Page\Layout {
	private const ROLE = 'member';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = __DIR__ . '/../../Messages/Sign/Up/content.xsl';

	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Sign\UpForm(
							$this->url,
							$this->csrf,
							new Backup($_SESSION, $_POST)
						)
					),
					new Response\FlashResponse(),
					new Response\PermissionResponse(),
					new Response\IdentifiedResponse($this->user)
				),
				__DIR__ . '/templates/up.xml',
				__DIR__ . '/../templates/layout.xml'
			),
			$this->user,
			$this->url
		);
	}

	public function submitUp(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Sign\InForm($this->url, $this->csrf, new Backup($_SESSION, $_POST)),
				new Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					(new Storage\Transaction($this->database))->start(
						function() use ($credentials) {
							(new Access\UniqueUsers(
								$this->database,
								new Encryption\AES256CBC(
									$this->configuration['KEYS']['password']
								)
							))->register(
								$credentials['email'],
								$credentials['password'],
								self::ROLE
							);
							(new Access\SecureVerificationCodes(
								$this->database
							))->generate($credentials['email']);
						}
					);
					(new Access\ReserveVerificationCodes(
						$this->database,
						new Mail\SendmailMailer(),
						(new Mail\Message())->setFrom(self::SENDER)->setSubject(self::SUBJECT),
						new Output\XsltTemplate(
							self::CONTENT,
							new Output\Xml(
								['base_url' => $this->url->reference()],
								'up'
							)
						)
					))->generate($credentials['email']);
				}
			))->validate();
			$this->flashMessage('You have been signed up', 'success');
			$this->flashMessage('Confirm your registration in the email', 'warning');
			$this->redirect('sign/in');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/up');
		}
	}
}