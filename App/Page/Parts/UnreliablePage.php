<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Remembrall\Model\Subscribing;
use Texy;

final class UnreliablePage extends Page {
	protected function parts(): Subscribing\Parts {
		return new Subscribing\FormattedParts(
			new Subscribing\UnreliableParts(
				new Subscribing\CollectiveParts($this->database),
				$this->database
			),
			new Texy\Texy(),
			new Dindent\Indenter()
		);
	}
}