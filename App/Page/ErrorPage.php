<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Application;
use Tracy\ILogger;

final class ErrorPage implements Application\IPresenter {
	use Nette\SmartObject;
	/** @var ILogger */
	private $logger;

	public function __construct(ILogger $logger) {
		$this->logger = $logger;
	}

	public function run(Application\Request $request) {
		$exception = $request->getParameter('exception');
		if($exception instanceof Application\BadRequestException) {
			list($module, , $sep) = Application\Helpers::splitName(
				$request->getPresenterName()
			);
			return new Responses\ForwardResponse(
				$request->setPresenterName($module . $sep . 'Error4xx')
			);
		}
		$this->logger->log($exception, ILogger::EXCEPTION);
		return new Responses\CallbackResponse(
			function() {
				require __DIR__ . '/templates/Error/500.phtml';
			}
		);
	}
}