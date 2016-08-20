<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Mail;
use Remembrall\Model\{
    Email, Subscribing
};
use GuzzleHttp;

final class CronPage extends BasePage {
    /** @var Mail\IMailer @inject */
    public $mailer;

    public function actionDefault() {
        $parts = new Subscribing\ChangedParts(
            new Subscribing\ExpiredParts(
                new Subscribing\CollectiveParts($this->database),
                $this->database,
                new GuzzleHttp\Client(['http_errors' => false])
            )
        );
        /** @var Subscribing\Part $part */
        foreach($parts->iterate() as $part) {
            $this->mailer->send(
                (new Email\NetteMessageFactory(
                    new Email\SubscribingMessage(
                        new Subscribing\TextPart($part),
                        $this->database
                    )
                ))->create()
            );
        }
        echo 'OK';
    }
}
