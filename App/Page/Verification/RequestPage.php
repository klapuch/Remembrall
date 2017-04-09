<?php
declare(strict_types = 1);
namespace Remembrall\Page\Verification;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Control\Verification;
use Remembrall\Page;

final class RequestPage extends Page\Layout {
	private const TEMPLATES = __DIR__ . '/../../Messages/Verification/Request';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = self::TEMPLATES . '/content.xsl';

	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Verification\RequestForm(
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitRequest(array $credentials): void {
		try {
			(new Verification\RequestForm(
				$this->url,
				$this->csrf,
				$this->backup
			))->submit(function() use ($credentials) {
				(new Access\ReserveVerificationCodes(
					$this->database,
					new Mail\SendmailMailer(),
					(new Mail\Message())->setFrom(self::SENDER)->setSubject(self::SUBJECT),
					new Output\XsltTemplate(
						self::CONTENT,
						new Output\Xml(
							[
								'base_url' => (new Uri\SchemeFoistedUrl(
									new Uri\FakeUri($_SERVER['SERVER_NAME']),
									$_SERVER['REQUEST_SCHEME']
								))->reference(),
							],
							'request'
						)
					)
				))->generate($credentials['email']);
			});
			$this->flashMessage('Verification code has been resent', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->redirect('verification/request');
		}
	}
}