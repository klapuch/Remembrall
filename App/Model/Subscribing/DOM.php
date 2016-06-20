<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class DOM extends \DOMDocument {
	public function __construct($version = '1.0', $encoding = 'UTF-8') {
		parent::__construct($version, $encoding);
	}

	public function loadHTML($source, $options = 0) {
		libxml_use_internal_errors(true);
		parent::loadHTML(
			mb_convert_encoding(
				$source,
				'HTML-ENTITIES',
				'UTF-8'
			),
			$options
		);
		libxml_use_internal_errors(false);
	}
}