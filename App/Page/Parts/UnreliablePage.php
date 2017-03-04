<?php
declare(strict_types = 1);
namespace Remembrall\Page\Parts;

use Gajus\Dindent;
use Remembrall\Model\Web;
use Texy;

final class UnreliablePage extends Page {
	protected function parts(): Web\Parts {
		return new Web\FormattedParts(
			new Web\UnreliableParts(
				new Web\CollectiveParts($this->database),
				$this->database
			),
			new Texy\Texy(),
			new Dindent\Indenter()
		);
	}
}