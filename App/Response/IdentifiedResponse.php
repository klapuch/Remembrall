<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Markup;
use Klapuch\Output;

final class IdentifiedResponse implements Application\Response {
	private $user;

	public function __construct(Access\User $user) {
		$this->user = $user;
	}
	public function body(): Output\Format {
		$properties = $this->user->properties();
		$dom = new \DOMDocument();
		$dom->loadXML(
			sprintf(
				'<user id="%d" %s/>',
				$this->user->id(),
				(new Markup\ConcatenatedAttribute(
					...array_map(
						function(string $attribute, string $value): Markup\Attribute {
								return new Markup\SafeAttribute(
									$attribute,
									$value
								);
						},
						array_keys($properties),
						$properties
					)
				))->pair()
			)
		);
		return new Output\DomFormat($dom, 'xml');
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/xml; charset=utf-8;',
		];
	}
}