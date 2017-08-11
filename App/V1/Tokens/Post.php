<?php
declare(strict_types = 1);
namespace Remembrall\V1\Tokens;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Internal;
use Klapuch\Output;
use Remembrall\Page\Layout;
use Remembrall\Response;

final class Post extends Layout {
	public function template(array $parameters): Output\Template {
		try {
			$credentials = new \SimpleXMLElement(file_get_contents('php://input'));
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
				new Internal\CookieExtension($this->configuration['PROPRIETARY_SESSIONS'])
			))->enter([$credentials->email, $credentials->password]);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\XmlError($ex));
		}
	}
}