<?php
declare(strict_types = 1);
namespace Remembrall\Model\Misc;

use Klapuch\Output;

/**
 * Simplified printing to XML
 */
final class XmlPrintedObjects implements Output\Format {
	private $root;
	private $prints;

	public function __construct(string $root, array $prints) {
		$this->root = $root;
		$this->prints = $prints;
	}

	public function serialization(): string {
		return (new Output\WrappedXml(
			$this->root,
			...array_map(
				function($object): Output\Format {
					return $object->print(new Output\Xml([], key($this->prints)));
				},
				current($this->prints)
			)
		))->serialization();
	}

	public function with(string $tag, $content = null): Output\Format {
		throw new \Exception('Not implemented');
	}

	public function adjusted(string $tag, callable $adjustment): Output\Format {
		throw new \Exception('Not implemented');
	}
}