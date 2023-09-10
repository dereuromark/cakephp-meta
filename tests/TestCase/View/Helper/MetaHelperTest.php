<?php

namespace Meta\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Meta\View\Helper\MetaHelper;

/**
 * MetaHelper tests
 */
class MetaHelperTest extends TestCase {

	protected MetaHelper $Meta;

	protected View $View;

	/**
	 * @var string
	 */
	protected $defaultLocale;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ($this->defaultLocale === null) {
			$this->defaultLocale = ini_get('intl.default_locale');
		}

		ini_set('intl.default_locale', 'de_DE');
		Configure::delete('Meta');

		$request = (new ServerRequest())
			->withParam('controller', 'ControllerName')
			->withParam('action', 'actionName');

		$this->View = new View($request);
		$this->Meta = new MetaHelper($this->View);

		Router::plugin('Meta', function (RouteBuilder $routes): void {
			$routes->fallbacks(DashedRoute::class);
		});
	}

	/**
	 * @return void
	 */
	public function testMetaLanguage() {
		$result = $this->Meta->language();
		$expected = '';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language(true);
		$expected = '<meta http-equiv="language" content="de-DE"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language();
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language('deu');
		$expected = '<meta http-equiv="language" content="deu"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaLanguageConfiguration() {
		ini_set('intl.default_locale', 'en_US');

		$this->Meta = new MetaHelper($this->View, ['language' => true]);

		$result = $this->Meta->language();
		$expected = '<meta http-equiv="language" content="en-US"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language('en');
		$expected = '<meta http-equiv="language" content="en"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->language();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaRobots() {
		$result = $this->Meta->robots();
		$this->assertEquals('<meta name="robots" content="noindex,nofollow,noarchive"/>', $result);

		$result = $this->Meta->robots(['index' => true]);
		$this->assertEquals('<meta name="robots" content="index,nofollow,noarchive"/>', $result);

		$result = $this->Meta->robots('noindex,nofollow,archive');
		$this->assertEquals('<meta name="robots" content="noindex,nofollow,archive"/>', $result);

		$result = $this->Meta->robots(false);
		$this->assertEquals('', $result);
	}

	/**
	 * @return void
	 */
	public function testMetaRobotsConfiguration() {
		Configure::write('Meta', ['robots' => ['index' => true]]);
		$options = ['robots' => ['follow' => true]];
		$this->Meta = new MetaHelper($this->View, $options);

		$result = $this->Meta->robots();
		$this->assertEquals('<meta name="robots" content="index,follow,noarchive"/>', $result);

		$result = $this->Meta->robots(['index' => false]);
		$this->assertEquals('<meta name="robots" content="noindex,follow,noarchive"/>', $result);
	}

	/**
	 * @return void
	 */
	public function _testMetaName() {
		$result = $this->Meta->metaName('foo', [1, 2, 3]);
		$expected = '<meta name="foo" content="1, 2, 3" />';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaDescription() {
		$result = $this->Meta->description('descr');
		$expected = '<meta name="description" content="descr"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->description();
		$this->assertEquals($expected, $result);

		$result = $this->Meta->description('foo', 'deu');
		$expected = '<meta name="description" content="foo" lang="deu"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->description();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaDescriptionString() {
		$this->View->set('_meta', ['description' => 'Foo Bar']);
		$this->Meta = new MetaHelper($this->View);

		$result = $this->Meta->description();
		$expected = '<meta name="description" content="Foo Bar"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * MetaHelperTest::testMetaKeywords()
	 *
	 * @return void
	 */
	public function testMetaKeywords() {
		$result = $this->Meta->keywords('mystring');
		$expected = '<meta name="keywords" content="mystring"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->keywords(['foo', 'bar']);
		$expected = '<meta name="keywords" content="foo,bar"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->keywords();
		$this->assertEquals($expected, $result);

		// Locale keywords trump global ones
		$result = $this->Meta->keywords(['fooD', 'barD'], 'deu');
		$expected = '<meta name="keywords" content="fooD,barD" lang="deu"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->keywords();
		$this->assertEquals($expected, $result);

		// But you can force-get them
		$result = $this->Meta->keywords(null, '*');
		$expected = '<meta name="keywords" content="foo,bar"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->keywords(['fooE', 'barE'], 'eng');
		$expected = '<meta name="keywords" content="fooE,barE" lang="eng"/>';
		$this->assertEquals($expected, $result);

		// Having multiple locale keywords combines them
		$result = $this->Meta->keywords();
		$expected = '<meta name="keywords" content="fooD,barD" lang="deu"/><meta name="keywords" content="fooE,barE" lang="eng"/>';
		$this->assertEquals($expected, $result);

		// Retrieve a specific one
		$result = $this->Meta->keywords(null, 'eng');
		$expected = '<meta name="keywords" content="fooE,barE" lang="eng"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaKeywordsString() {
		$this->View->set('_meta', ['keywords' => 'Foo,Bar']);
		$this->Meta = new MetaHelper($this->View);

		$result = $this->Meta->keywords();
		$expected = '<meta name="keywords" content="Foo,Bar"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function _testMetaRss() {
		$result = $this->Meta->metaRss('/some/url', 'some title');
		$expected = '<link rel="alternate" type="application/rss+xml" title="some title" href="/some/url"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testSizesIcon() {
		$result = $this->Meta->sizesIcon('/favicon-32x32.png', 32);
		$expected = '<link href="/favicon-32x32.png" rel="icon" sizes="32x32"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->sizesIcon('/favicon-32x32.png', 32, ['type' => 'image/png']);
		$expected = '<link href="/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->sizesIcon('/apple-touch-icon-57x57.png', 57, ['prefix' => 'apple-touch-']);
		$expected = '<link href="/apple-touch-icon-57x57.png" rel="apple-touch-icon" sizes="57x57"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * MetaHelperTest::testMetaEquiv()
	 *
	 * @return void
	 */
	public function testMetaHttpEquiv() {
		$result = $this->Meta->httpEquiv('expires', '0');
		$expected = '<meta http-equiv="expires" content="0"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->httpEquiv('foo', 'bar');
		$expected = '<meta http-equiv="foo" content="bar"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->httpEquiv('expires');
		$expected = '<meta http-equiv="expires" content="0"/>';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->httpEquiv();
		$expected = '<meta http-equiv="expires" content="0"/><meta http-equiv="foo" content="bar"/>';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaCanonical() {
		$is = $this->Meta->canonical('/some/url/param1');
		$this->assertEquals('<link rel="canonical" href="' . $this->Meta->Url->build('/some/url/param1', ['fullBase' => true]) . '"/>', $is);

		$is = $this->Meta->canonical(['plugin' => 'Meta', 'controller' => 'Foo', 'action' => 'bar'], true);
		$this->assertEquals('<link rel="canonical" href="' . $this->Meta->Url->build(['plugin' => 'Meta', 'controller' => 'Foo', 'action' => 'bar'], ['fullBase' => true]) . '"/>', $is);
	}

	/**
	 * @return void
	 */
	public function _testMetaAlternate() {
		$is = $this->Meta->metaAlternate('/some/url/param1', 'de-de', true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url/param1', ['fullBase' => true]) . '" rel="alternate" hreflang="de-de"/>', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], 'de', true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de"/>', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], ['de', 'de-ch'], true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de"/>' . PHP_EOL . '<link href="' . $this->Meta->Url->build('/some/url', true) . '" rel="alternate" hreflang="de-ch"/>', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], ['de' => ['ch', 'at'], 'en' => ['gb', 'us']], true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de-ch"/>' . PHP_EOL .
			'<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de-at"/>' . PHP_EOL .
			'<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="en-gb"/>' . PHP_EOL .
			'<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="en-us"/>', trim($is));
	}

	/**
	 * @return void
	 */
	public function testOut() {
		$result = $this->Meta->out();

		$expected = '<title>Controller Name - Action Name</title><meta charset="utf-8"/>';
		$expected .= '<link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>';
		$expected .= '<meta name="robots" content="noindex,nofollow,noarchive"/>';
		$this->assertTextEquals($expected, $result);

		$this->Meta->title('Foo');
		$this->Meta->canonical(true);
		$this->Meta->language('de');
		$this->Meta->keywords('foo bar');
		$this->Meta->keywords('foo bar EN', 'en');
		$this->Meta->description('A sentence');
		$this->Meta->httpEquiv('expires', '0');
		$this->Meta->robots(['index' => true]);
		$this->Meta->custom('viewport', 'width=device-width, initial-scale=1');
		$this->Meta->custom('x', 'y');

		$result = $this->Meta->out(null, ['implode' => PHP_EOL]);

		$expected = '<title>Foo</title>
<meta charset="utf-8"/>
<link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
<link rel="canonical" href="/"/>
<meta http-equiv="language" content="de"/>
<meta name="robots" content="index,nofollow,noarchive"/>
<meta name="description" content="A sentence" lang="de"/>
<meta name="keywords" content="foo bar" lang="de"/><meta name="keywords" content="foo bar EN" lang="en"/>
<meta name="http-equiv" content="0"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/><meta name="x" content="y"/>';
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testOutMultiLanguageFalse() {
		$this->Meta->setConfig('multiLanguage', false);

		$this->Meta->language('de');
		$this->Meta->keywords('foo bar');
		$this->Meta->keywords('foo bar EN', 'en');
		$this->Meta->description('A sentence', 'de');
		$this->Meta->description('A sentence EN', 'en');

		$result = $this->Meta->out(null, ['implode' => PHP_EOL]);

		$expected = '<title>Controller Name - Action Name</title>
<meta charset="utf-8"/>
<link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
<meta http-equiv="language" content="de"/>
<meta name="robots" content="noindex,nofollow,noarchive"/>
<meta name="description" content="A sentence" lang="de"/>
<meta name="keywords" content="foo bar" lang="de"/>';
		$this->assertTextEquals($expected, $result);

		$this->Meta->language('en');
		$this->Meta->keywords('foo bar');
		$this->Meta->keywords('foo bar EN', 'en');
		$this->Meta->description('A sentence', 'de');
		$this->Meta->description('A sentence EN', 'en');

		$result = $this->Meta->out(null, ['implode' => PHP_EOL]);
		$expected = '<title>Controller Name - Action Name</title>
<meta charset="utf-8"/>
<link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
<meta http-equiv="language" content="en"/>
<meta name="robots" content="noindex,nofollow,noarchive"/>
<meta name="description" content="A sentence EN" lang="en"/>
<meta name="keywords" content="foo bar EN" lang="en"/>';
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * TearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		unset($this->Meta);

		ini_set('intl.default_locale', $this->defaultLocale);
	}

}
