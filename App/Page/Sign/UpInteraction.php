<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Form\Sign;
use Remembrall\Model;
use Remembrall\Page;
use Remembrall\Response;

final class UpInteraction extends Page\Layout {
	private const ROLE = 'member';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = __DIR__ . '/../../Messages/Sign/Up/content.xsl',
		CONSTRAINT = __DIR__ . '/../../Messages/Sign/Up/constraint.xsd';

	public function template(array $credentials): Output\Template {
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
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\InformativeResponse(
						new Response\RedirectResponse(
							new Response\EmptyResponse(),
							new Uri\RelativeUrl($this->url, 'sign/in')
						),
						['warning' => 'Confirm your registration in the email'],
						$_SESSION
					),
					['success' => 'You have been signed up'],
					$_SESSION
				)
			);
		} catch (\Throwable $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'sign/up')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}