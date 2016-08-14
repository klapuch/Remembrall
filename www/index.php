<?php
declare(strict_types = 1);
header('X-Frame-Option: DENY');
header('X-Content-Type-Options: nosniff');
header('X-Powered-By: ');
header('X-XSS-Protection: 1; mode=block;');
date_default_timezone_set('Europe/Prague');
mb_internal_encoding('UTF-8');
session_start(
	[
		'cookie_secure' => 0,
		'cookie_httponly' => 1,
		'use_strict_mode' => 1,
	]
);
require __DIR__ . '/../vendor/autoload.php';
use Klapuch\{
	Output, Storage, Ini
};
use Nette\Caching\Storages;
use Remembrall\Model\{
	Access, Subscribing
};

define('TEMPLATES', __DIR__ . '/../App/Page/templates');
define('INI', __DIR__ . '/../App/Configuration/.config.ini');
$config = (new Ini\Valid(INI, new Ini\Typed(INI)))->read();
$database = new Storage\PDODatabase(
	$config['DATABASE']['dsn'],
	$config['DATABASE']['user'],
	$config['DATABASE']['password']
);
$logger = new Tracy\Logger(__DIR__ . '/../Log');
$subscriber = new Access\PostgresSubscriber(1, $database);
$subscriptions = new Subscribing\OwnedSubscriptions($subscriber, $database);
$url = $_SERVER['REQUEST_URI'];
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if($url === '/Remembrall/www/parts') {
		$xmlData = array_reduce(
			$subscriptions->iterate(),
			function($subscriptions, Subscribing\Subscription $subscription) {
				$subscriptions .= $subscription->print(
					new Output\Xml('subscription')
				);
				return $subscriptions;
			}
		);
		$xsl = new \DOMDocument();
		$xsl->load(TEMPLATES . '/Parts/default.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		echo $xslt->transformToXml(
			new \SimpleXMLElement(
				sprintf(
					'<%1$s>%2$s</%1$s>',
					'subscriptions',
					$xmlData
				)
			)
		);
	} elseif($url === '/Remembrall/www/subscription') {
		$xsl = new \DOMDocument();
		$xsl->load(TEMPLATES . '/Subscription/default.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->load(TEMPLATES . '/Subscription/form.xml');
		echo $xslt->transformToXml($xml);
	}
} elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
	if($url === '/Remembrall/www/subscription') {
		try {
			$page = new Subscribing\LoggedPage(
				new Subscribing\CachedPage(
					$_POST['url'],
					new Subscribing\HtmlWebPage(
						$_POST['url'],
						new GuzzleHttp\Client(['http_errors' => false])
					),
					new Subscribing\WebPages($database),
					$database
				),
				$logger
			);
			(new Storage\PostgresTransaction($database))->start(
				function() use ($page, $subscriber, $database, $logger) {
					(new Subscribing\LoggedParts(
						new Subscribing\CollectiveParts(
							$database
						),
						$logger
					))->add(
						new Subscribing\CachedPart(
							new Subscribing\HtmlPart(
								new Subscribing\ValidXPathExpression(
									new Subscribing\XPathExpression(
										$page,
										$_POST['expression']
									)
								),
								$page
							),
							new Storages\MemoryStorage()
						),
						$_POST['url'],
						$_POST['expression']
					);
					(new Subscribing\LoggedSubscriptions(
						new Subscribing\LimitedSubscriptions(
							$database,
							$subscriber,
							new Subscribing\OwnedSubscriptions(
								$subscriber,
								$database
							)
						),
						$logger
					))->subscribe(
						$_POST['url'],
						$_POST['expression'],
						new Subscribing\FutureInterval(
							new Subscribing\DateTimeInterval(
								new \DateTimeImmutable(),
								new \DateInterval(
									sprintf('PT%dM', max(0, $_POST['interval']))
								)
							)
						)
					);
				}
			);
			header('Location: /parts');
			exit;
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}