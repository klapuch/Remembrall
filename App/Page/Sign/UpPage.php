<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Encryption;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Control\Sign;
use Remembrall\Page;

final class UpPage extends Page\BasePage {
	private const ROLE = 'member';
	private const TEMPLATES = __DIR__ . '/../../Messages/Sign/Up';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = self::TEMPLATES . '/content.xsl';

	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Sign\UpForm(
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitUp(array $credentials): void {
		try {
			(new Sign\InForm(
				$this->url,
				$this->csrf,
				$this->backup
			))->submit(function() use ($credentials) {
				(new Storage\Transaction($this->database))->start(function() use ($credentials) {
					(new Access\UniqueUsers(
						$this->database,
						new Encryption\AES256CBC(
							$this->configuration['KEYS']['password']
						)
					))->register($credentials['email'], $credentials['password'], self::ROLE);
					(new Access\SecureVerificationCodes($this->database))->generate($credentials['email']);
				});
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
							'up'
						)
					)
				))->generate($credentials['email']);
			});
			$this->flashMessage('You have been signed up', 'success');
			$this->flashMessage('Confirm your registration in the email', 'warning');
			$this->redirect('sign/in');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/up');
		}
	}
}