<?php

namespace Meta\View\Helper;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\View;
use Exception;

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
	protected $helpers = ['Html', 'Url'];

	/**
	 * Default config.
	 *
	 * @var array<string, mixed>
	 */
	protected $_defaultConfig = [
		'multiLanguage' => true, // Disable to only display the localized tag to the current language
	];

	/**
	 * Meta headers for the response
	 *
	 * @var array<string, mixed>
	 */
	protected $meta = [
		'title' => null,
		'charset' => null,
		'icon' => null,
		'canonical' => null, // Set to true for auto-detect
		'language' => null, // Set to true for auto-detect
		'robots' => ['index' => false, 'follow' => false, 'archive' => false],
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
	 * @param array $options
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
	protected function _guessLanguage() {
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
	 * @param string|null $value
	 * @return string
	 */
	public function title($value = null) {
		if ($value !== null) {
			$this->meta['title'] = $value;
		}

		$value = $this->meta['title'];
		if ($value === false) {
			return '';
		}

		return $this->Html->tag('title', $value);
	}

	/**
	 * @param string|null $value
	 * @return string
	 */
	public function charset($value = null) {
		if ($value !== null) {
			$this->meta['charset'] = $value;
		}

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
	 * @param string|null $value
	 * @return string
	 */
	public function icon($value = null) {
		if ($value !== null) {
			$this->meta['icon'] = $value;
		}

		$value = $this->meta['icon'];
		if ($value === false) {
			return '';
		}
		if ($value === true) {
			$value = null;
		}

		return $this->Html->meta('icon', $value);
	}

	/**
	 * @param string $url
	 * @param int $size
	 * @param array<string, mixed> $options
	 * @return string
	 */
	public function sizesIcon($url, $size, array $options = []) {
		if ($url !== null) {
			$options += [
				'size' => $size,
				'prefix' => null,
			];
			$this->meta['sizesIcon'][$url] = $options;
		}

		/** @var array<string, mixed>|bool $value */
		$value = $this->meta['sizesIcon'][$url];
		if ($value === false) {
			return '';
		}
		if ($value === true) {
			$value = null;
		}

		$options = [
			'rel' => $value['prefix'] . 'icon',
			'sizes' => $value['size'] . 'x' . $value['size'],
		] + $options;
		$array = [
			'url' => $url,
			'attrs' => $this->Html->templater()->formatAttributes($options, ['prefix', 'size']),
		];

		return $this->Html->templater()->format('metalink', $array);
	}

	/**
	 * Specify the target audience language of the page.
	 * Discouraged now. Instead use `lang` attribute for the html tag.
	 *
	 * @param string|null $value
	 * @return string
	 */
	public function language($value = null) {
		if ($value !== null) {
			$this->meta['language'] = $value;
		}

		$language = $this->meta['language'] === true ? $this->_guessLanguage() : $this->meta['language'];

		if (!$language) {
			return '';
		}

		$array = [
			'http-equiv' => 'language',
			'content' => $language,
		];

		return $this->Html->meta($array);
	}

	/**
	 * @param array|string|false|null $value
	 * @return string
	 */
	public function robots($value = null) {
		if ($value === false) {
			return '';
		}

		if ($value !== null) {
			$robots = $value;
			if (is_array($value)) {
				$defaults = $this->meta['robots'];
				$robots += $defaults;
			}
			$this->meta['robots'] = $robots;
		}

		$robots = $this->meta['robots'];

		if (is_array($robots)) {
			foreach ($robots as $robot => $use) {
				$robots[$robot] = $use ? $robot : 'no' . $robot;
			}
			$robots = implode(',', $robots);
		}

		return $this->Html->meta('robots', $robots);
	}

	/**
	 * @param string|null $description
	 * @param string|null $lang
	 * @return string
	 */
	public function description($description = null, $lang = null) {
		if ($description !== null) {
			if ($lang && $this->meta['language'] && $lang !== $this->meta['language'] && !$this->getConfig('multiLanguage')) {
				return '';
			}

			if ($lang === null) {
				$lang = $this->meta['language'] ?: '*';
			}

			$this->meta['description'][$lang] = $description;
		}
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
				$res[] = $this->description($content, $lang);
			}

			return implode('', $res);
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

		return $this->Html->meta($array);
	}

	/**
	 * @param array<string>|string|null $keywords
	 * @param string|null $lang
	 * @return string
	 */
	public function keywords($keywords = null, $lang = null) {
		if ($keywords !== null) {
			if ($lang && $this->meta['language'] && $lang !== $this->meta['language'] && !$this->getConfig('multiLanguage')) {
				return '';
			}

			if ($lang === null) {
				$lang = $this->meta['language'] ?: '*';
			}

			$keywords = (array)$keywords;
			$this->meta['keywords'][$lang] = $keywords;
		}
		if (!is_array($this->meta['keywords'])) {
			if ($lang === null) {
				$lang = $this->meta['language'] ?: '*';
			}
			$this->meta['keywords'] = [$lang => $this->meta['keywords']];
		}

		if ($lang === null) {
			$keywords = $this->meta['keywords'];

			$res = [];
			foreach ($keywords as $lang => $keywords) {
				if ($lang === '*') {
					$lang = null;
					if (count($this->meta['keywords']) > 1) {
						continue;
					}
				}
				$res[] = $this->keywords($keywords, $lang);
			}

			return implode('', $res);
		}

		$keywords = $this->meta['keywords'][$lang] ?? false;

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

		return $this->Html->meta($array);
	}

	/**
	 * @param string|null $name
	 * @param string|null $value
	 * @throws \Exception
	 * @return string
	 */
	public function custom($name = null, $value = null) {
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

		return $this->Html->meta($array);
	}

	/**
	 * Outputs a canonical tag to the page
	 *
	 * @param array|string|true|null $url Canonical URL override
	 * @param bool $full
	 *
	 * @return string
	 */
	public function canonical($url = null, $full = false) {
		if ($url !== null) {
			$this->meta['canonical'] = $url;
		}

		$url = $this->meta['canonical'];

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
	 * @param string|null $type
	 * @param string|null $value
	 * @throws \Exception
	 * @return string
	 */
	public function httpEquiv($type = null, $value = null) {
		if ($value !== null) {
			if ($type === null) {
				throw new Exception('Type must be provided');
			}

			$this->meta['http-equiv'][$type] = $value;
		}

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

		$array = [
			'http-equiv' => $type,
			'content' => $value,
		];

		return $this->Html->meta($array);
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
	 * @param array $options
	 * @return string
	 */
	public function out($header = null, $options = []) {
		$defaults = [
			'implode' => '',
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
				return $this->charset();
			}

			if ($header === 'icon') {
				return $this->icon();
			}

			if ($header === 'title') {
				return $this->title();
			}

			if ($header === 'canonical') {
				return $this->canonical();
			}

			if ($header === 'robots') {
				return $this->robots();
			}

			if ($header === 'language') {
				return $this->language();
			}

			if ($header === 'keywords') {
				return $this->keywords();
			}

			if ($header === 'description') {
				return $this->description();
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

			return $this->Html->meta($meta);
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
