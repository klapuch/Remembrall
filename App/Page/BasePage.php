<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Klapuch\{
	Uri, Encryption, FlashMessage, Csrf, Form, Log, Output, Access, Markup
};

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
	/** @var \Klapuch\Form\Storage */
	protected $storage;

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
		$this->storage = new Form\Storage($_SESSION, $_POST);
	}

	public function startup(): void {
		$this->user = new Access\FakeUser(0, ['role' => 'guest']);
		if(isset($_SESSION['id'])) {
			$this->user = new Access\CachedUser(
				new Access\RegisteredUser($_SESSION['id'], $this->database)
			);
		}
	}

	abstract public function render(array $parameters): Output\Format;

	/**
	 * XML for layout
	 * @param array $parameters
	 * @return array
	 */
	final public function template(array $parameters): array {
		$properties = $this->user->properties();
		return [
			new \SimpleXMLElement(
				sprintf(
					'<subscriber id="%d" %s/>',
					$this->user->id(),
					(new Markup\HtmlAttributes(
						...array_map(
							function(string $attribute, string $value) {
								return new Markup\HtmlAttribute($attribute, $value);
							},
							array_keys($properties), $properties
						)
					))->pairs()
				)
			),
			new \SimpleXMLElement(
				sprintf('<baseUrl>%s</baseUrl>', $this->url->reference())
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
			new \SimpleXMLElement($this->render($parameters)->serialization()),
		];
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
		header(sprintf('Location: %s', $this->url->reference() . $url));
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
}