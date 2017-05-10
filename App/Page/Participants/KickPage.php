<?php
declare(strict_types = 1);
namespace Remembrall\Page\Participants;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;

final class KickPage extends Page\Layout {
	private const SCHEMA = __DIR__ . '/../Invitation/templates/constraint.xsd';
	private const SENDER = 'Remembrall <remembrall@remembrall.org>',
		SUBJECT = 'Kick from subscription',
		CONTENT = __DIR__ . '/../../Messages/Participants/Kick/content.xsl';

	public function response(array $parameters): Application\Response {
		return new Response\RedirectResponse(
			new Response\EmptyResponse(),
			new Uri\RelativeUrl($this->url, 'error')
		);
	}

	public function submitKick(array $participant): Application\Response {
		try {
			$this->protect();
			$kick = (new Subscribing\MemorialInvitation(
				(int) $participant['subscription'],
				$participant['email'],
				$this->database
			))->print(new Output\Xml([], 'invitation'));
			(new Subscribing\NonViolentParticipants(
				$this->user,
				$this->database
			))->kick((int) $participant['subscription'], $participant['email']);
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
			$this->flashMessage('Participant has been kicked', 'success');
		} catch (\Throwable $ex) {
			$this->flashMessage($ex->getMessage(), 'danger');
		} finally {
			return new Response\RedirectResponse(
				new Response\EmptyResponse(),
				new Uri\RelativeUrl($this->url, 'susbcriptions')
			);
		}
	}
}