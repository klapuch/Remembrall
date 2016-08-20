<?php
namespace Remembrall\Page;

use Nette;
use Nette\Http\IResponse;
use Nette\Security;

abstract class BasePage extends Nette\Application\UI\Presenter {
    use \Nextras\Application\UI\SecuredLinksPresenterTrait;

    const TEMPLATES = __DIR__ . '/templates';

    /** @inject @var \Klapuch\Storage\Database */
    public $database;

    /** @inject @var \Tracy\ILogger */
    public $logger;

    /** @inject @var \Remembrall\Model\Access\Subscriber */
    public $subscriber;

    public function startup() {
        parent::startup();
        $this->user->login(new Security\Identity(1));
    }

    protected function createTemplate() {
        // Do not call createTemplate
    }

    public function afterRender() {
        if($this->isAjax() && $this->hasFlashSession())
            $this->redrawControl('flashes');
    }
}
