<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Caching\Storages;
use Remembrall\Model\Subscribing;
use Tracy;
use GuzzleHttp;

final class CronPage extends BasePage {
	public function actionDefault(string $code) {
		try {
			$pages = new Subscribing\ExpiredPages(
				new Subscribing\MySqlPages($this->database),
				$this->database,
				new Subscribing\FutureInterval(
					new Subscribing\DateTimeInterval(
						new \DateTimeImmutable(),
						new \DateInterval('PT10M')
					)
				)
			);
			foreach($pages->iterate() as $page) {
				$webPage = new Subscribing\CachedPage(
					new Subscribing\HtmlWebPage(
						new GuzzleHttp\Client(
							[
								'base_uri' => $page->url(),
								'allow_redirects' => true,
							]
						)
					),
					new Storages\MemoryStorage()
				);
				$parts = new Subscribing\ChangedParts(
					new Subscribing\ExpiredParts(
						new Subscribing\CollectiveParts(
							$this->database,
							$webPage
						),
						$webPage,
						$this->database
					)
				);
				foreach($parts->iterate() as $part) {
					$freshPart = new Subscribing\HtmlPart(
						$webPage,
						$part->expression(),
						$part->owner()
					);
					$parts->replace($part, $freshPart);
				}
			}
		} catch(\Exception $ex) {
			(new Tracy\Logger(__DIR__ . '/../../Log'))->log(
				$ex,
				Tracy\Logger::EXCEPTION
			);
		}
	}
}
