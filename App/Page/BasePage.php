<?php
namespace Remembrall\Page;

use Nette;
use Nette\Http\IResponse;
use Nette\Security;

abstract class BasePage extends Nette\Application\UI\Presenter {
	use \Nextras\Application\UI\SecuredLinksPresenterTrait;

	public function checkRequirements($element) {
		if($this->signal === null) {
			$resource = $this->name;
			$action = $this->action;
		} elseif($this->signal && empty($this->signal[0])) {
			$resource = $this->name;
			$action = $this->signal[1] . '!';
		} elseif($this->signal && $this->signal[0]) {
			$resource = preg_replace('~-[0-9]+$~', '', $this->signal[0]);
			$action = $this->signal[1] . '!';
		}
		if(!$this->user->isAllowed($resource, $action)) {
			if($this->user->loggedIn) {
				$this->error(
					'You do not have a permission to see this page',
					IResponse::S403_FORBIDDEN
				);
			}
			$this->flashMessage('You need to log in first', 'danger');
			$this->redirect(
				'Login:',
				['backlink' => $this->storeRequest()]
			);
		}
	}

	public function startup() {
		if(!$this->user->loggedIn) {
			if($this->user->logoutReason === Security\IUserStorage::INACTIVITY) {
				$this->flashMessage(
					'You were logged out because of inactivity',
					'danger'
				);
				$this->redirect(
					'Login:',
					['backlink' => $this->storeRequest()]
				);
			}
		}
		parent::startup();
	}

	public function afterRender() {
		if($this->isAjax() && $this->hasFlashSession())
			$this->redrawControl('flashes');
	}
}
