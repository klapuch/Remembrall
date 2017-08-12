<?php
declare(strict_types = 1);
namespace Remembrall\Page\Subscriptions;

use Gajus\Dindent;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Form;
use Klapuch\Output;
use Remembrall\Form\Participants;
use Remembrall\Form\Subscription;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page;
use Remembrall\Response;
use Texy;

final class DefaultPage extends Page\Layout {
	private const FIELDS = ['last_update', 'interval', 'expression', 'url', 'language'];

	public function template(array $parameters): Output\Template {
		$subscriptions = iterator_to_array(
			(new Subscribing\FormattedSubscriptions(
				new Subscribing\OwnedSubscriptions(
					$this->user,
					$this->database
				),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->all(
				new Dataset\CombinedSelection(
					new Dataset\SqlRestSort($_GET['sort'] ?? '', self::FIELDS)
				)
			)
		);
		$participants = iterator_to_array(
			(new Subscribing\NonViolentParticipants(
				$this->user,
				$this->database
			))->all()
		);
		return new Application\HtmlTemplate(
			new Response\WebAuthentication(
				new Response\ComposedResponse(
					new Response\CombinedResponse(
						new Response\FormResponse(
							new Subscription\DeleteForms(
								$subscriptions,
								$this->url,
								$this->csrf
							)
						),
						new Response\CombinedResponse(
							new Response\FormResponse(
								new Participants\InviteForms(
									$subscriptions,
									$this->url,
									$this->csrf,
									new Form\Backup($_SESSION, $_POST)
								)
							),
							new Response\FormResponse(
								new Participants\RetryForms(
									$participants,
									$this->url,
									$this->csrf
								)
							),
							new Response\FormResponse(
								new Participants\KickForms(
									$participants,
									$this->url,
									$this->csrf
								)
							),
							new Response\PlainResponse(
								new Output\ValidXml(
									new Misc\XmlPrintedObjects(
										'subscriptions',
										['subscription' => $subscriptions]
									),
									__DIR__ . '/templates/constraint.xsd'
								)
							),
							new Response\PlainResponse(
								new Output\ValidXml(
									new Misc\XmlPrintedObjects(
										'participants',
										['participant' => $participants]
									),
									__DIR__ . '/templates/constraint.xsd'
								)
							)
						),
						new Response\FlashResponse(),
						new Response\GetResponse(),
						new Response\PermissionResponse(),
						new Response\IdentifiedResponse($this->user)
					),
					__DIR__ . '/templates/default.xml',
					__DIR__ . '/../templates/layout.xml'
				),
				$this->user,
				$this->url
			),
			__DIR__ . '/templates/default.xsl'
		);
	}
}