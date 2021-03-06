<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Form\Verification;
use Remembrall\Page;
use Remembrall\Response;

final class RequestInteraction extends Page\Layout {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = __DIR__ . '/../../Messages/Verification/Request/content.xsl',
		CONSTRAINT = __DIR__ . '/../../Messages/Verification/Request/constraint.xsd';

	public function template(array $credentials): Output\Template {
		try {
			(new Form\HarnessedForm(
				new Verification\RequestForm(
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $_POST)
				),
				new Form\Backup($_SESSION, $_POST),
				function() use ($credentials): void {
					$verification = (new Access\ReserveVerificationCodes(
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
									$verification->print(
										new Output\ValidXml(
											new Output\Xml([], 'request'),
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
					['success' => 'Verification code has been resent'],
					$_SESSION
				)
			);
		} catch (\Throwable $ex) {
			return new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl($this->url, 'verification/request')
					),
					['danger' => $ex->getMessage()],
					$_SESSION
				)
			);
		}
	}
}