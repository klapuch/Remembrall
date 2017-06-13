<?php
declare(strict_types = 1);
namespace Remembrall\Page\Participants;

use Klapuch\Application;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Form\Participants;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class InvitePage extends Page\Layout {
	private const SCHEMA = __DIR__ . '/../Invitation/templates/constraint.xsd';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Invitation to subscription',
		CONTENT = __DIR__ . '/../../Messages/Participants/Invite/content.xsl';

	public function response(array $parameters): Application\Response {
		return new Response\RedirectResponse(
			new Response\EmptyResponse(),
			new Uri\RelativeUrl($this->url, 'error')
		);
	}

	public function submitInvite(array $participant): Application\Response {
		try {
			(new Form\HarnessedForm(
				new Participants\InviteForm(
					new Subscribing\FakeSubscription(),
					$this->url,
					$this->csrf,
					new Form\Backup($_SESSION, $participant)
				),
				new Form\Backup($_SESSION, $participant),
				function() use ($participant): void {
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
				}
			))->validate();
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['success' => 'Participant has been asked'],
				$_SESSION
			);
		} catch (\Throwable $ex) {
			return new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['danger' => $ex->getMessage()],
				$_SESSION
			);
		}
	}
}