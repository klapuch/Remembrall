<?php
namespace Remembrall\Component;

use Nette;

abstract class BaseControl extends Nette\Application\UI\Control {
	use \Nextras\Application\UI\SecuredLinksControlTrait;
}