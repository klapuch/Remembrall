<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Authorization;
use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Markup;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

abstract class BasePage extends Application\Page {
	/** @var \Klapuch\Access\User */
	protected $user;
	/** @var \PDO */
	protected $database;
	/** @var \Klapuch\Form\Backup */
	protected $backup;

	public function __construct(
		Uri\Uri $url,
		Log\Logs $logs,
		Ini\Ini $configuration
	) {
		parent::__construct($url, $logs, $configuration);
		$this->database = new Storage\SafePDO(
			$this->configuration['DATABASE']['dsn'],
			$this->configuration['DATABASE']['user'],
			$this->configuration['DATABASE']['password']
		);
		$this->backup = new Form\Backup($_SESSION, $_POST);
	}

	public function startup(): void {
		$this->user = new Access\FakeUser(0, ['role' => 'guest']);
		if (isset($_SESSION['id'])) {
			$this->user = new Access\CachedUser(
				new Access\RegisteredUser($_SESSION['id'], $this->database)
			);
		}
		$role = new Authorization\HttpRole(
			new Authorization\RolePermissions(
				$this->user->properties()['role'],
				new Authorization\XmlPermissions(
					__DIR__ . '/../Configuration/permission.xml'
				)
			)
		);
		if (!$role->allowed($this->url->path())) {
			$this->flashMessage('You don\'t have a permission to request the page', 'danger');
			$this->redirect($this->url->path() === 'sign/in' ? '' : 'sign/in');
		}
	}

	/**
	 * XML for layout
	 * @param array $parameters
	 * @return array
	 */
	final public function template(array $parameters): array {
		$properties = $this->user->properties();
		$layout = new \DOMDocument();
		$layout->load(__DIR__ . '/templates/layout.xml');
		$permission = new \DOMDocument();
		$permission->load(__DIR__ . '/../Configuration/permission.xml');
		return array_merge(
			simplexml_import_dom($layout)->xpath('child::*'),
			[simplexml_import_dom($permission)],
			[
				new \SimpleXMLElement(
					sprintf(
						'<user id="%d" %s/>',
						$this->user->id(),
						(new Markup\ConcatenatedAttribute(
							...array_map(
								function(string $attribute, string $value) {
									return new Markup\SafeAttribute($attribute, $value);
								},
								array_keys($properties),
								$properties
							)
						))->pair()
					)
				),
				new \SimpleXMLElement(
					(new Output\Xml([], 'request'))
					->with('get', $_GET)
					->serialization()
				),
				new \SimpleXMLElement(
					(new Output\WrappedXml(
						'flashMessages',
						(new UI\FlashMessages(
							new UI\PersistentFlashMessage($_SESSION)
						))->print(new Output\Xml([], 'flashMessage'))
					))->serialization()
				),
				new \SimpleXMLElement(
					sprintf(
						'<csrf><link>%s</link><input>%s</input></csrf>',
						(new Csrf\CsrfLink($this->csrf))->protection(),
						(new Csrf\CsrfInput($this->csrf))->protection()
					)
				),
			],
			(new \SimpleXMLElement(
				(new Output\WrappedXml(
					'page',
					$this->render($parameters)
				))->serialization()
			))->xpath('child::*')
		);
	}
}