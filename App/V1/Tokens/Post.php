<?php
declare(strict_types = 1);
namespace Remembrall\V1\Tokens;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Internal;
use Klapuch\Output;
use Remembrall\Page;
use Remembrall\Response;

final class Post extends Page\Api {
	public function template(array $parameters): Output\Template {
		try {
			$credentials = new \SimpleXMLElement(
				(new Output\ValidXml(
					(new Application\PlainRequest())->body(),
					__DIR__ . '/schema/constraint.xsd'
				))->serialization()
			);
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
				$_SESSION,
				new class implements Internal\Extension {
					public function improve(): void {
					}
				}
			))->enter([$credentials->email, $credentials->password]);
			return new Application\RawTemplate(
				new Response\XmlResponse(
					new Response\PlainResponse(
						new Output\Xml(['@id' => session_id()], 'token')
					),
					201
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\XmlError($ex));
		}
	}
}