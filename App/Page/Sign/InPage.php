<?php
declare(strict_types = 1);
namespace Remembrall\Page\Sign;

use Klapuch\Access;
use Klapuch\Encryption;
use Klapuch\Output;
use Remembrall\Form;
use Remembrall\Form\Sign;
use Remembrall\Page;

final class InPage extends Page\Layout {
	public function render(array $parameters): Output\Format {
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<forms>%s</forms>',
				(new Sign\InForm(
					$this->url,
					$this->csrf,
					$this->backup
				))->render()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function submitIn(array $credentials): void {
		try {
			(new Form\HarnessedForm(
				new Sign\InForm($this->url, $this->csrf, $this->backup),
				$this->backup,
				function() use ($credentials): void {
					(new Access\SessionEntrance(
						new Access\VerifiedEntrance(
							$this->database,
							new Access\SecureEntrance(
								$this->database,
								new Encryption\AES256CBC(
									$this->configuration['KEYS']['password']
								)
							)
						),
						$_SESSION
					))->enter([$credentials['email'], $credentials['password']]);
				}
			))->validate();
			$this->flashMessage('You have been logged in', 'success');
			$this->redirect('subscriptions');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
			$this->redirect('sign/in');
		}
	}
}