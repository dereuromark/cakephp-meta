<?php

namespace Meta\View\Helper;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\View;
use Exception;
use RuntimeException;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class MetaHelper extends Helper {

	/**
	 * Included helpers.
	 *
	 * @var array
	 */
	protected array $helpers = ['Html', 'Url'];

	/**
	 * Default config.
	 *
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'multiLanguage' => true, // Disable to only display the localized tag to the current language
	];

	/**
	 * Meta headers for the response
	 *
	 * @var array<string, mixed>
	 */
	protected array $meta = [
		'title' => null,
		'charset' => null,
		'icon' => null,
		'canonical' => null, // Set to true for auto-detect
		'language' => null, // Set to true for auto-detect
		'robots' => ['index' => false, 'follow' => false, 'archive' => false],
		'description' => null,
	];

	/**
	 * Class Constructor
	 *
	 * Merges defaults with
	 * - Configure::read(Meta)
	 * - Helper options
	 * - viewVars _meta
	 * in that order (the latter trumps)
	 *
	 * @param \Cake\View\View $View
	 * @param array<string, mixed> $options
	 */
	public function __construct(View $View, array $options = []) {
		parent::__construct($View, $options);

		$configureMeta = (array)Configure::read('Meta');
		if (Configure::read('Meta.robots') && is_array(Configure::read('Meta.robots'))) {
			$configureMeta['robots'] = Hash::merge($this->meta['robots'], Configure::read('Meta.robots'));
		}
		$this->meta = $configureMeta + $this->meta;

		if (!empty($options['robots']) && is_array($options['robots'])) {
			$options['robots'] = Hash::merge($this->meta['robots'], $options['robots']);
		}

		unset($options['className']);
		$this->meta = $options + $this->meta;

		$viewVarsMeta = (array)$this->getView()->get('_meta');
		if ($viewVarsMeta) {
			if (!empty($viewVarsMeta['robots']) && is_array($viewVarsMeta['robots'])) {
				$viewVarsMeta['robots'] = Hash::merge($this->meta['robots'], $viewVarsMeta['robots']);
			}
			$this->meta = $viewVarsMeta + $this->meta;
		}

		if ($this->meta['charset'] === null) {
			// By default include this
			$this->meta['charset'] = true;
		}

		if ($this->meta['icon'] === null) {
			// By default include this
			$this->meta['icon'] = true;
		}

		if ($this->meta['title'] === null) {
			$controller = $this->getView()->getRequest()->getParam('controller');
			$action = $this->getView()->getRequest()->getParam('action');
			if ($controller && $action) {
				$controllerName = Inflector::humanize(Inflector::underscore($controller));
				$actionName = Inflector::humanize(Inflector::underscore($action));
				$this->meta['title'] = __($controllerName) . ' - ' . __($actionName);
			}
		}
	}

	/**
	 * Guesses language from system defaults.
	 *
	 * Autoformats de_DE to de-DE.
	 *
	 * @return string|null
	 */
	protected function _guessLanguage(): ?string {
		$locale = ini_get('intl.default_locale');
		if (!$locale) {
			return null;
		}

		if (strpos($locale, '_') !== false) {
			$locale = str_replace('_', '-', $locale);
		}

		return $locale;
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setTitle(string $value): void {
		$this->meta['title'] = $value;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		$value = $this->meta['title'];
		if ($value === false) {
			return '';
		}

		return $this->Html->tag('title', $value);
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setCharset(string $value): void {
		$this->meta['charset'] = $value;
	}

	/**
	 * @return string
	 */
	public function getCharset(): string {
		$value = $this->meta['charset'];
		if ($value === false) {
			return '';
		}
		if ($value === true) {
			$value = null;
		}

		return $this->Html->charset($value);
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setIcon(string $value): void {
		$this->meta['icon'] = $value;
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		$value = $this->meta['icon'];
		if ($value === false) {
			return '';
		}
		if ($value === true) {
			$value = null;
		}

		return (string)$this->Html->meta('icon', $value);
	}

	/**
	 * @param string $url
	 * @param int $size
	 * @param array<string, mixed> $options
	 * @return void
	 */
	public function setSizesIcon(string $url, int $size, array $options = []): void {
		$options += [
			'size' => $size,
			'prefix' => null,
		];
		$this->meta['sizesIcon'][$url] = $options;
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public function getSizesIcon(string $url): string {
		/** @var array<string, mixed> $value */
		$value = $this->meta['sizesIcon'][$url];

		$options = [
			'rel' => $value['prefix'] . 'icon',
			'sizes' => $value['size'] . 'x' . $value['size'],
		] + $value;
		$array = [
			'url' => $url,
			'attrs' => $this->Html->templater()->formatAttributes($options, ['prefix', 'size']),
		];

		return $this->Html->templater()->format('metalink', $array);
	}

	/**
	 * @return string
	 */
	public function getSizesIcons(): string {
		/** @var array<string, array<string, mixed>> $sizesIcons */
		$sizesIcons = $this->meta['sizesIcon'] ?? [];

		$icons = [];
		foreach ($sizesIcons as $url => $options) {
			$icons[] = $this->getSizesIcon($url);
		}

		return implode(PHP_EOL, $icons);
	}

	/**
	 * @param string|null $value
	 * @return void
	 */
	public function setLanguage(string|null $value): void {
		if ($value === null) {
			$value = true;
		}

		$this->meta['language'] = $value;
	}

	/**
	 * @return string
	 */
	public function getLanguage(): string {
		$value = $this->meta['language'];
		if (!$value) {
			return '';
		}

		if ($value === true) {
			$value = $this->_guessLanguage();
		}

		$array = [
			'http-equiv' => 'language',
			'content' => $value,
		];

		return (string)$this->Html->meta($array);
	}

	/**
	 * @param array<string>|string|false $value
	 * @return void
	 */
	public function setRobots(array|string|false $value): void {
		if (is_array($value)) {
			$defaults = $this->meta['robots'];
			$value += $defaults;
		}
		$this->meta['robots'] = $value;
	}

	/**
	 * @return string
	 */
	public function getRobots(): string {
		$robots = $this->meta['robots'];
		if ($robots === false) {
			return '';
		}

		if (is_array($robots)) {
			foreach ($robots as $robot => $use) {
				$robots[$robot] = $use ? $robot : 'no' . $robot;
			}
			$robots = implode(',', $robots);
		}

		return (string)$this->Html->meta('robots', $robots);
	}

	/**
	 * @param string $value
	 * @param string|null $lang
	 * @return void
	 */
	public function setDescription(string $value, ?string $lang = null): void {
		if ($lang && $this->meta['language'] && $lang !== $this->meta['language'] && !$this->getConfig('multiLanguage')) {
			throw new RuntimeException('Not configured as multi-language');
		}

		if ($lang === null) {
			$lang = $this->meta['language'] ?: '*';
		}

		$this->meta['description'][$lang] = $value;
	}

	/**
	 * @param string|null $lang
	 * @return string
	 */
	public function getDescription(?string $lang = null): string {
		if (!is_array($this->meta['description'])) {
			if ($lang === null) {
				$lang = $this->meta['language'] ?: '*';
			}
			$this->meta['description'] = [$lang => $this->meta['description']];
		}

		if ($lang === null) {
			/** @var array<string, string> $description */
			$description = $this->meta['description'];

			$res = [];
			foreach ($description as $lang => $content) {
				if ($lang === '*') {
					$lang = null;
					if (count($this->meta['description']) > 1) {
						continue;
					}
				}
				$array = [
					'name' => 'description',
					'content' => $description,
					'lang' => $lang,
				];

				$res[] = (string)$this->Html->meta($array);
			}

			return implode(PHP_EOL, $res);
		}

		$description = $this->meta['description'][$lang] ?? false;

		if ($description === false) {
			return '';
		}

		$array = [
			'name' => 'description',
			'content' => $description,
			'lang' => $lang !== '*' ? $lang : null,
		];

		return (string)$this->Html->meta($array);
	}

	/**
	 * @param array<string>|string $value
	 * @param string|null $lang
	 *
	 * @return void
	 */
	public function setKeywords(array|string $value, ?string $lang = null): void {
		if ($lang && $this->meta['language'] && $lang !== $this->meta['language'] && !$this->getConfig('multiLanguage')) {
			throw new RuntimeException('Not configured as multi-language');
		}

		if ($lang === null) {
			$lang = $this->meta['language'] ?: '*';
		}

		$this->meta['keywords'][$lang] = $value;
	}

	/**
	 * @param string|null $lang
	 *
	 * @return string
	 */
	public function getKeywords(?string $lang = null): string {
		if ($lang === null) {
			/** @var array<string, mixed>|string $keywords */
			$keywords = $this->meta['keywords'];
			if (!is_array($keywords)) {
				return $this->keywords($keywords, $lang);
			}

			$res = [];
			foreach ($keywords as $lang => $keyword) {
				if ($lang === '*') {
					$lang = null;
					if (count($this->meta['keywords']) > 1) {
						continue;
					}
				}

				$res[] = $this->keywords($keyword, $lang);
			}

			return implode('', $res);
		}

		$keywords = $this->meta['keywords'][$lang] ?? false;

		return $this->keywords($keywords, $lang);
	}

	/**
	 * @param mixed $keywords
	 * @param string|null $lang
	 *
	 * @return string
	 */
	protected function keywords(mixed $keywords, ?string $lang): string {
		if ($keywords === false) {
			return '';
		}

		if (is_array($keywords)) {
			$keywords = implode(',', $keywords);
		}

		$array = [
			'name' => 'keywords',
			'content' => $keywords,
			'lang' => $lang !== '*' ? $lang : null,
		];

		return (string)$this->Html->meta($array);
	}

	/**
	 * @param string|null $name
	 * @param string|null $value
	 * @throws \Exception
	 * @return string
	 */
	public function custom($name = null, $value = null): string {
		if ($value !== null) {
			if ($name === null) {
				throw new Exception('Name must be provided');
			}

			$this->meta['custom'][$name] = $value;
		}

		if ($name === null) {
			$res = [];
			foreach ($this->meta['custom'] as $name => $content) {
				$res[] = $this->custom($name, $content);
			}

			return implode('', $res);
		}

		if (!isset($this->meta['custom'][$name]) || $this->meta['custom'][$name] === false) {
			return '';
		}
		$value = $this->meta['custom'][$name];

		$array = [
			'name' => $name,
			'content' => $value,
		];

		return (string)$this->Html->meta($array);
	}

	/**
	 * @param array|string|bool $value
	 * @return void
	 */
	public function setCanonical(array|string|bool $value): void {
		$this->meta['canonical'] = $value;
	}

	/**
	 * @param bool $full
	 *
	 * @return string
	 */
	public function getCanonical(bool $full = false): string {
		$url = $this->meta['canonical'];
		if ($url === false) {
			return '';
		}

		$options = [
			'fullBase' => $full,
		];

		if ($url === true) {
			$url = $this->getView()->getRequest()->getAttribute('here');
		} elseif (is_array($url)) {
			$url = $this->Url->build($url, $options);
		} elseif (!preg_match('/^(https:\/\/|http:\/\/)/', $url)) {
			$url = $this->Url->build($url, $options);
		}

		$array = [
			'url' => $url,
			'rel' => 'canonical',
		];

		return $this->Html->templater()->format('css', $array);
	}

	/**
	 * @param string $type
	 * @param string|false $value
	 * @return void
	 */
	public function setHttpEquiv(string $type, string|false $value): void {
		$this->meta['http-equiv'][$type] = $value;
	}

	/**
	 * @param string|null $type
	 * @return string
	 */
	public function getHttpEquiv(?string $type = null): string {
		if ($type === null) {
			$res = [];
			foreach ($this->meta['http-equiv'] as $type => $content) {
				$res[] = $this->httpEquiv($type, $content);
			}

			return implode('', $res);
		}

		if (!isset($this->meta['http-equiv'][$type]) || $this->meta['http-equiv'][$type] === false) {
			return '';
		}
		$value = $this->meta['http-equiv'][$type];

		return $this->httpEquiv($type, $value);
	}

	/**
	 * @param string $type
	 * @param string|false $value
	 * @return string
	 */
	protected function httpEquiv(string $type, string|false $value): string {
		if ($value === false) {
			return '';
		}

		$array = [
			'http-equiv' => $type,
			'content' => $value,
		];

		return (string)$this->Html->meta($array);
	}

	/**
	 * Outputs a meta header or series of meta headers
	 *
	 * Covered are:
	 * - charset
	 * - title
	 * - canonical
	 * - robots
	 * - language
	 * - keywords
	 * -
	 *
	 * Options:
	 * - skip
	 * - implode
	 *
	 * @param string|null $header Specific meta header to output
	 * @param array<string, mixed> $options
	 * @return string
	 */
	public function out(?string $header = null, array $options = []): string {
		$defaults = [
			'implode' => Configure::read('debug') ? PHP_EOL : '',
			'skip' => [],
		];
		$options += $defaults;

		if (!is_array($options['skip'])) {
			$options['skip'] = (array)$options['skip'];
		}

		if ($header) {
			if (!isset($this->meta[$header]) || $this->meta[$header] === false) {
				return '';
			}

			if ($header === 'charset') {
				return $this->getCharset();
			}

			if ($header === 'icon') {
				return $this->getIcon();
			}

			if ($header === 'title') {
				return $this->getTitle();
			}

			if ($header === 'canonical') {
				return $this->getCanonical();
			}

			if ($header === 'robots') {
				return $this->getRobots();
			}

			if ($header === 'language') {
				return $this->getLanguage();
			}

			if ($header === 'keywords') {
				return $this->getKeywords();
			}

			if ($header === 'description') {
				return $this->getDescription();
			}

			if ($header === 'custom') {
				return $this->custom();
			}

			$meta = ['name' => $header, 'content' => $this->meta[$header]];
			$pos = strpos($header, ':');
			if ($pos !== false) {
				$meta['name'] = substr($header, $pos + 1);
				$meta['property'] = $header;
			}

			return (string)$this->Html->meta($meta);
		}

		$results = [];

		foreach ($this->meta as $header => $value) {
			if (in_array($header, $options['skip'])) {
				continue;
			}
			$out = $this->out($header, $options);
			if ($out === '') {
				continue;
			}
			$results[] = $out;
		}

		return implode($options['implode'], $results);
	}

}
