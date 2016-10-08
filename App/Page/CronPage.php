<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Mail;
use Remembrall\Model\{
	Email
};

final class CronPage extends BasePage {
	/** @var Mail\IMailer @inject */
	public $mailer;

	public function actionDefault() {
		echo 'OK';
	}
}