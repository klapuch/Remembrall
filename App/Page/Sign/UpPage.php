<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Storage;
use Nette\Mail;
use Remembrall\Form\Sign;
use Remembrall\Model;
use Remembrall\Page;
use Remembrall\Response;

final class UpPage extends Page\Layout {
	private const ROLE = 'member';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = __DIR__ . '/../../Messages/Sign/Up/content.xsl',
		CONSTRAINT = __DIR__ . '/../../Messages/Sign/Up/constraint.xsd';

	public function response(array $parameters): Application\Response {
		return new Response\AuthenticatedResponse(
			new Response\ComposedResponse(
				new Response\CombinedResponse(
					new Response\FormResponse(
						new Sign\UpForm(
							$this->url,
							$this->csrf,
							new Form\Backup($_SESSION, $_POST)
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
				new Sign\InForm($this->url, $this->csrf, new Form\Backup($_SESSION, $_POST)),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					(new Storage\Transaction($this->database))->start(
						function() use ($credentials) {
							(new Model\Access\ParticipatedUsers(
								new Access\UniqueUsers(
									$this->database,
									new Encryption\AES256CBC(
										$this->configuration['KEYS']['password']
									)
								),
								$this->database
							))->register(
								$credentials['email'],
								$credentials['password'],
								self::ROLE
							);
							$code = (new Access\SecureVerificationCodes(
								$this->database
							))->generate($credentials['email']);
							(new Mail\SendmailMailer())->send(
								(new Mail\Message())
									->setFrom(self::SENDER)
									->addTo($credentials['email'])
									->setSubject(self::SUBJECT)
									->setHtmlBody(
										(new Output\XsltTemplate(
											self::CONTENT,
											$code->print(
												new Output\ValidXml(
													new Output\Xml([], 'up'),
													self::CONSTRAINT
												)
											)
										))->render(['base_url' => $this->url->reference()])
									)
							);
						}
					);
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