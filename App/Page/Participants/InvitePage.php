<?php
declare(strict_types = 1);
namespace Remembrall\Page\Participants;

use Klapuch\Application;
use Klapuch\Output;
use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class InvitePage extends Page\Layout {
	private const SCHEMA = __DIR__ . '/../Invitation/templates/constraint.xsd';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Invitation to subscription',
		CONTENT = __DIR__ . '/../../Messages/Participants/Invite/content.xsl';

	public function response(array $parameters): Application\Response {
		$this->redirect('error');
	}

	public function submitInvite(array $participant): void {
		try {
			$this->protect();
			$invitation = (new Subscribing\GuestParticipants(
				new Subscribing\NonViolentParticipants(
					$this->user,
					$this->database
				),
				$this->database
			))->invite((int) $participant['subscription'], $participant['email']);
			(new Mail\SendmailMailer())->send(
				(new Mail\Message())
					->setFrom(self::SENDER)
					->addTo($participant['email'])
					->setSubject(self::SUBJECT)
					->setHtmlBody(
						(new Output\XsltTemplate(
							self::CONTENT,
							new Output\ValidXml(
								$invitation->print(new Output\Xml([], 'invitation')),
								self::SCHEMA
							)
						))->render(['base_url' => $this->url->reference()])
					)
			);
			$this->flashMessage('Participant has been asked', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			$this->redirect('subscriptions');
		}
	}
}