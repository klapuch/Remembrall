<?php
declare(strict_types = 1);
namespace Remembrall\Page\Participants;

use Klapuch\Application;
use Klapuch\Output;
use Nette\Mail;
use Remembrall\Model\Subscribing\OwnedParticipants;
use Remembrall\Page;

final class InvitePage extends Page\Layout {
	private const SCHEMA = __DIR__ . '/../Invitation/templates/constraint.xsd';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Remembrall registration verification code',
		CONTENT = __DIR__ . '/../../Messages/Participants/Invite/content.xsl';

	public function response(array $parameters): Application\Response {
		$this->redirect('error');
	}

	public function submitInvite(array $participant): void {
		try {
			$this->protect();
			$invitation = (new OwnedParticipants(
				$this->user,
				$this->database
			))->invite($participant['subscription'], $participant['email']);
			(new Mail\SendmailMailer())->send(
				(new Mail\Message())->setFrom(self::SENDER)
					->addTo($participant['email'])
					->setSubject(self::SUBJECT)
					->setHtmlBody(
						(new Output\XsltTemplate(
							self::CONTENT,
							new Output\ValidXml(
								$invitation->print(
									new Output\Xml(
										['base_url' => $this->url->reference()],
										'invitation'
									)
								),
								self::SCHEMA
							)
						))->render()
					)
			);
			$this->flashMessage('Invitation has been sent', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->redirect('subscription');
		}
	}
}