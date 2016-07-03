<?php
declare(strict_types = 1);
namespace Remembrall\Component;

use Nette;

abstract class SecureControl extends Nette\Application\UI\Control {
	use \Nextras\Application\UI\SecuredLinksControlTrait;
}