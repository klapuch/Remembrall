<?php
declare(strict_types = 1);
namespace Remembrall\Page\Password;

use Klapuch\Access;
use Klapuch\Output;
use Nette\Mail;
use Remembrall\Form;
use Remembrall\Form\Password;
use Remembrall\Page;

final class RemindPage extends Page\Layout {
	private const TEMPLATES = __DIR__ . '/../../Messages/Password/Remind';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall forgotten password',
		CONTENT = self::TEMPLATES . '/content.xsl';

	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Password\RemindForm(
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitRemind(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Password\RemindForm($this->url, $this->csrf, $this->backup),
				$this->backup,
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