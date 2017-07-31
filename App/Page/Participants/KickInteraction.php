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

final class KickInteraction extends Page\Layout {
	private const SCHEMA = __DIR__ . '/../Invitation/templates/constraint.xsd';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Kick from subscription',
		CONTENT = __DIR__ . '/../../Messages/Participants/Kick/content.xsl';

	public function response(array $participant): Output\Template {
		(new Form\HarnessedForm(
			new Participants\KickForm(
				new Subscribing\FakeParticipant(),
				$this->url,
				$this->csrf,
				new Form\Backup($_SESSION, $participant)
			),
			new Form\Backup($_SESSION, $participant),
			function() use ($participant): void {
				$kick = (new Subscribing\MemorialInvitation(
					(int) $participant['subscription'],
					$participant['email'],
					$this->database
				))->print(new Output\Xml([], 'invitation'));
				(new Subscribing\NonViolentParticipants(
					$this->user,
					$this->database
				))->kick(
					(int) $participant['subscription'],
					$participant['email']
				);
				(new Mail\SendmailMailer())->send(
					(new Mail\Message())
						->setFrom(self::SENDER)
						->addTo($participant['email'])
						->setSubject(self::SUBJECT)
						->setHtmlBody(
							(new Output\XsltTemplate(
								self::CONTENT,
								new Output\ValidXml($kick, self::SCHEMA)
							))->render()
						)
				);
			}
		))->validate();
		return new Application\HtmlTemplate(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl($this->url, 'subscriptions')
				),
				['success' => 'Participant has been kicked'],
				$_SESSION
			)
		);
	}
}