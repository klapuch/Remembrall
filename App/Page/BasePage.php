<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\Access;
use Klapuch\Authorization;
use Klapuch\Csrf;
use Klapuch\Encryption;
use Klapuch\FlashMessage;
use Klapuch\Form;
use Klapuch\Log;
use Klapuch\Markup;
use Klapuch\Output;
use Klapuch\Uri;

abstract class BasePage {
	/** @var \Klapuch\Uri\Uri */
	protected $url;
	/** @var \Klapuch\Access\User */
	protected $user;
	/** @var \PDO */
	protected $database;
	/** @var \Klapuch\Log\Logs */
	protected $logs;
	/** @var \Klapuch\Encryption\Cipher */
	protected $cipher;
	/** @var \Klapuch\Csrf\Csrf */
	protected $csrf;
	/** @var \Klapuch\Form\Backup */
	protected $backup;

	public function __construct(
		Uri\Uri $url,
		\PDO $database,
		Log\Logs $logs,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->logs = $logs;
		$this->url = $url;
		$this->cipher = $cipher;
		$this->csrf = new Csrf\StoredCsrf($_SESSION, $_POST, $_GET);
		$this->backup = new Form\Backup($_SESSION, $_POST);
	}

	public function startup(): void {
		$this->user = new Access\FakeUser(0, ['role' => 'guest']);
		if(isset($_SESSION['id'])) {
			$this->user = new Access\CachedUser(
				new Access\RegisteredUser($_SESSION['id'], $this->database)
			);
		}
		$role = new Authorization\HttpRole(
			new Authorization\RolePermissions(
				$this->user->properties()['role'],
				new Authorization\XmlPermissions(
					__DIR__ . '/components/permission.xml'
				)
			)
		);
		if(!$role->allowed($this->url->path())) {
			$this->flashMessage('You don\'t have a permission to request the page', 'danger');
			$this->redirect('sign/in');
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
		$permission->load(__DIR__ . '/components/permission.xml');
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
								array_keys($properties), $properties
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
					(new FlashMessage\XmlMessage($_SESSION))->print()
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
					'page', $this->render($parameters)
				))->serialization()
			))->xpath('child::*')
		);
	}

	/**
	 * Flash message to the page
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	final protected function flashMessage(string $content, string $type): void {
		(new FlashMessage\XmlMessage($_SESSION))->flash($content, $type);
	}

	/**
	 * Redirect relatively to the given url
	 * @param string $url
	 * @return void
	 */
	final protected function redirect(string $url): void {
		header(sprintf('Location: %s/%s', $this->url->reference(), $url));
		exit;
	}

	/**
	 * Protect against CSRF
	 * @throws \Exception
	 */
	final protected function protect(): void {
		if($this->csrf->abused())
			throw new \Exception('Timeout');
	}

	/**
	 * Log the exception
	 * @param \Throwable $ex
	 * @return void
	 */
	final protected function log(\Throwable $ex): void {
		$this->logs->put(
			new Log\PrettyLog(
				$ex,
				new Log\PrettySeverity(
					new Log\JustifiedSeverity(Log\Severity::ERROR)
				)
			)
		);
	}

	abstract public function render(array $parameters): Output\Format;
}