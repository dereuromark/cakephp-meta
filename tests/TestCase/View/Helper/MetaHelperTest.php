<?php

namespace Meta\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Meta\View\Helper\MetaHelper;
use RuntimeException;

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
			->withParam('action', 'actionName')
			->withEnv('HTTP_HOST', 'localhost');

		$this->View = new View($request);
		$this->Meta = new MetaHelper($this->View);

		$builder = Router::createRouteBuilder('/');
		$builder->setRouteClass(DashedRoute::class);
		$builder->connect('/:controller/:action/*');
		$builder->fallbacks(DashedRoute::class);
		$builder->plugin('Meta', function (RouteBuilder $routes): void {
			$routes->fallbacks(DashedRoute::class);
		});
	}

	/**
	 * @return void
	 */
	public function testMetaLanguage() {
		$result = $this->Meta->getLanguage();
		$expected = '';
		$this->assertEquals($expected, $result);

		$this->Meta->setLanguage(null);
		$result = $this->Meta->getLanguage();
		$expected = '<meta http-equiv="language" content="de-DE">';
		$this->assertEquals($expected, $result);

		$this->Meta->setLanguage('deu');
		$result = $this->Meta->getLanguage();
		$expected = '<meta http-equiv="language" content="deu">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaLanguageConfiguration() {
		ini_set('intl.default_locale', 'en_US');

		$this->Meta = new MetaHelper($this->View, ['language' => true]);

		$result = $this->Meta->getLanguage();
		$expected = '<meta http-equiv="language" content="en-US">';
		$this->assertEquals($expected, $result);

		$this->Meta->setLanguage('en');
		$result = $this->Meta->getLanguage();
		$expected = '<meta http-equiv="language" content="en">';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->getLanguage();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaRobots() {
		$result = $this->Meta->getRobots();
		$this->assertEquals('<meta name="robots" content="noindex,nofollow,noarchive">', $result);

		$this->Meta->setRobots(['index' => true]);
		$result = $this->Meta->getRobots();
		$this->assertEquals('<meta name="robots" content="index,nofollow,noarchive">', $result);

		$this->Meta->setRobots('noindex,nofollow,archive');
		$result = $this->Meta->getRobots();
		$this->assertEquals('<meta name="robots" content="noindex,nofollow,archive">', $result);

		$this->Meta->setRobots(false);
		$result = $this->Meta->getRobots();
		$this->assertEquals('', $result);
	}

	/**
	 * @return void
	 */
	public function testMetaRobotsConfiguration() {
		Configure::write('Meta', ['robots' => ['index' => true]]);
		$options = ['robots' => ['follow' => true]];
		$this->Meta = new MetaHelper($this->View, $options);

		$result = $this->Meta->getRobots();
		$this->assertEquals('<meta name="robots" content="index,follow,noarchive">', $result);

		$this->Meta->setRobots(['index' => false]);
		$result = $this->Meta->getRobots();
		$this->assertEquals('<meta name="robots" content="noindex,follow,noarchive">', $result);
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
		$result = $this->Meta->getDescription();
		$expected = '';
		$this->assertEquals($expected, $result);

		$this->Meta->setDescription('descr');
		$result = $this->Meta->getDescription();
		$expected = '<meta name="description" content="descr">';
		$this->assertEquals($expected, $result);

		$this->Meta->setDescription('foo', 'deu');

		$result = $this->Meta->getDescription();
		$expected = '<meta name="description" content="foo" lang="deu">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaDescriptionString() {
		$this->View->set('_meta', ['description' => 'Foo Bar']);
		$this->Meta = new MetaHelper($this->View);

		$result = $this->Meta->getDescription();
		$expected = '<meta name="description" content="Foo Bar">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * MetaHelperTest::testMetaKeywords()
	 *
	 * @return void
	 */
	public function testMetaKeywords() {
		$this->Meta->setKeywords('mystring');
		$result = $this->Meta->getKeywords();
		$expected = '<meta name="keywords" content="mystring">';
		$this->assertEquals($expected, $result);

		$this->Meta->setKeywords(['foo', 'bar']);
		$result = $this->Meta->getKeywords();
		$expected = '<meta name="keywords" content="foo,bar">';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->getKeywords();
		$this->assertEquals($expected, $result);

		// Locale keywords trump global ones
		$this->Meta->setKeywords(['fooD', 'barD'], 'deu');
		$result = $this->Meta->getKeywords('deu');
		$expected = '<meta name="keywords" content="fooD,barD" lang="deu">';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->getKeywords();
		$this->assertEquals($expected, $result);

		// But you can force-get them
		$result = $this->Meta->getKeywords('*');
		$expected = '<meta name="keywords" content="foo,bar">';
		$this->assertEquals($expected, $result);

		$this->Meta->setKeywords(['fooE', 'barE'], 'eng');
		$result = $this->Meta->getKeywords('eng');
		$expected = '<meta name="keywords" content="fooE,barE" lang="eng">';
		$this->assertEquals($expected, $result);

		// Having multiple locale keywords combines them
		$result = $this->Meta->getKeywords();
		$expected = '<meta name="keywords" content="fooD,barD" lang="deu"><meta name="keywords" content="fooE,barE" lang="eng">';
		$this->assertEquals($expected, $result);

		// Retrieve a specific one
		$result = $this->Meta->getKeywords('eng');
		$expected = '<meta name="keywords" content="fooE,barE" lang="eng">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaKeywordsString() {
		$this->View->set('_meta', ['keywords' => 'Foo,Bar']);
		$this->Meta = new MetaHelper($this->View);

		$result = $this->Meta->getKeywords();
		$expected = '<meta name="keywords" content="Foo,Bar">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function _testMetaRss() {
		$result = $this->Meta->metaRss('/some/url', 'some title');
		$expected = '<link rel="alternate" type="application/rss+xml" title="some title" href="/some/url">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testSizesIcon() {
		$this->Meta->setSizesIcon('/favicon-16x16.png', 16);
		$expected1 = '<link href="/favicon-16x16.png" rel="icon" sizes="16x16">';

		$this->Meta->setSizesIcon('/favicon-32x32.png', 32, ['type' => 'image/png']);
		$expected2 = '<link href="/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png">';

		$this->Meta->setSizesIcon('/apple-touch-icon-57x57.png', 57, ['prefix' => 'apple-touch-']);
		$expected3 = '<link href="/apple-touch-icon-57x57.png" rel="apple-touch-icon" sizes="57x57">';

		$result = $this->Meta->getSizesIcons();
		$this->assertEquals($expected1 . PHP_EOL . $expected2 . PHP_EOL . $expected3, $result);
	}

	/**
	 * MetaHelperTest::testMetaEquiv()
	 *
	 * @return void
	 */
	public function testMetaHttpEquiv() {
		$this->Meta->setHttpEquiv('expires', '0');
		$result = $this->Meta->getHttpEquiv();
		$expected = '<meta http-equiv="expires" content="0">';
		$this->assertEquals($expected, $result);

		$this->Meta->setHttpEquiv('foo', 'bar');
		$result = $this->Meta->getHttpEquiv();
		$expected = '<meta http-equiv="expires" content="0"><meta http-equiv="foo" content="bar">';
		$this->assertEquals($expected, $result);

		$result = $this->Meta->getHttpEquiv();
		$expected = '<meta http-equiv="expires" content="0"><meta http-equiv="foo" content="bar">';
		$this->assertEquals($expected, $result);

		$this->Meta->setHttpEquiv('expires', false);
		$result = $this->Meta->getHttpEquiv();
		$expected = '<meta http-equiv="foo" content="bar">';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMetaCanonical() {
		$this->Meta->setCanonical('/some/url/param1');
		$is = $this->Meta->getCanonical();
		$this->assertEquals('<link rel="canonical" href="' . $this->Meta->Url->build('/some/url/param1') . '">', $is);

		$this->Meta->setCanonical(['plugin' => 'Meta', 'controller' => 'Foo', 'action' => 'bar']);
		$is = $this->Meta->getCanonical();
		$this->assertEquals('<link rel="canonical" href="' . $this->Meta->Url->build(['plugin' => 'Meta', 'controller' => 'Foo', 'action' => 'bar']) . '">', $is);
	}

	/**
	 * @return void
	 */
	public function _testMetaAlternate() {
		$is = $this->Meta->metaAlternate('/some/url/param1', 'de-de', true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url/param1', ['fullBase' => true]) . '" rel="alternate" hreflang="de-de">', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], 'de', true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de">', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], ['de', 'de-ch'], true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de">' . PHP_EOL . '<link href="' . $this->Meta->Url->build('/some/url', true) . '" rel="alternate" hreflang="de-ch">', trim($is));

		$is = $this->Meta->metaAlternate(['controller' => 'some', 'action' => 'url'], ['de' => ['ch', 'at'], 'en' => ['gb', 'us']], true);
		$this->assertEquals('<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de-ch">' . PHP_EOL
			. '<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="de-at">' . PHP_EOL
			. '<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="en-gb">' . PHP_EOL
			. '<link href="' . $this->Meta->Url->build('/some/url', ['fullBase' => true]) . '" rel="alternate" hreflang="en-us">', trim($is));
	}

	/**
	 * @return void
	 */
	public function testOut() {
		$result = $this->Meta->out();

		// Icon output varies by CakePHP version (newer versions dropped shortcut icon)
		$iconPattern = '<link href="/favicon.ico" type="image/x-icon" rel="icon">(<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon">)?';
		$pattern = '#^<title>Controller Name - Action Name</title>\n<meta charset="utf-8">\n' . $iconPattern . '\n<meta name="robots" content="noindex,nofollow,noarchive">$#';
		$this->assertMatchesRegularExpression($pattern, $result);

		$this->Meta->setCharset('utf-8');
		$this->Meta->setTitle('Foo');
		$this->Meta->setCanonical(true);
		$this->Meta->setLanguage('de');
		$this->Meta->setKeywords('foo bar');
		$this->Meta->setKeywords('foo bar EN', 'en');
		$this->Meta->setDescription('A sentence');
		$this->Meta->setHttpEquiv('expires', '0');
		$this->Meta->setRobots(['index' => true]);
		$this->Meta->custom('viewport', 'width=device-width, initial-scale=1');
		$this->Meta->custom('x', 'y');

		$result = $this->Meta->out(null, ['implode' => PHP_EOL]);

		// Icon output varies by CakePHP version (newer versions dropped shortcut icon)
		$pattern = '#^<title>Foo</title>
<meta charset="utf-8">
<link href="/favicon.ico" type="image/x-icon" rel="icon">(<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon">)?
<link rel="canonical" href="/">
<meta http-equiv="language" content="de">
<meta name="robots" content="index,nofollow,noarchive">
<meta name="description" content="A sentence" lang="de">
<meta name="keywords" content="foo bar" lang="de"><meta name="keywords" content="foo bar EN" lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="x" content="y">
<meta name="http-equiv" content="0">$#';
		$this->assertMatchesRegularExpression($pattern, $result);
	}

	/**
	 * @return void
	 */
	public function testOutMultiLanguageFalse() {
		$this->Meta->setConfig('multiLanguage', false);

		$this->Meta->setLanguage('de');

		$this->expectException(RuntimeException::class);

		$this->Meta->setKeywords('foo bar');
		$this->Meta->setKeywords('foo bar EN', 'en');

		$this->Meta->setDescription('A sentence', 'de');
		$this->Meta->setDescription('A sentence EN', 'en');
	}

	/**
	 * @return void
	 */
	public function testSetBreadcrumbs(): void {
		$this->Meta->setBreadcrumbs([
			['name' => 'Home', 'url' => '/'],
			['name' => 'Blog', 'url' => '/blog'],
			['name' => 'My Post'],
		]);

		$result = $this->Meta->getBreadcrumbs();
		$this->assertNotNull($result);
		$this->assertStringContainsString('"@context":', $result);
		$this->assertStringContainsString('https://schema.org', $result);
		$this->assertStringContainsString('"@type":', $result);
		$this->assertStringContainsString('BreadcrumbList', $result);
		$this->assertStringContainsString('"name":', $result);
		$this->assertStringContainsString('Home', $result);
		$this->assertStringContainsString('"position":', $result);
		$this->assertStringContainsString('<script type="application/ld+json">', $result);
	}

	/**
	 * @return void
	 */
	public function testSetBreadcrumbsEmpty(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Breadcrumbs require at least one item.');
		$this->Meta->setBreadcrumbs([]);
	}

	/**
	 * @return void
	 */
	public function testSetBreadcrumbsMissingName(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Breadcrumb item 1 requires a 'name' string.");
		$this->Meta->setBreadcrumbs([
			['name' => 'Home', 'url' => '/'],
			['url' => '/blog'],
		]);
	}

	/**
	 * @return void
	 */
	public function testSetBreadcrumbsUrlArray(): void {
		$this->Meta->setBreadcrumbs([
			['name' => 'Home', 'url' => ['controller' => 'Pages', 'action' => 'home']],
			['name' => 'Current'],
		]);

		$result = $this->Meta->getBreadcrumbs();
		$this->assertNotNull($result);
		$this->assertStringContainsString('"item":', $result);
		$this->assertStringContainsString('http', $result);
	}

	/**
	 * @return void
	 */
	public function testGetBreadcrumbsNull(): void {
		$result = $this->Meta->getBreadcrumbs();
		$this->assertNull($result);
	}

	/**
	 * @return void
	 */
	public function testSetArticle(): void {
		$this->Meta->setArticle([
			'headline' => 'How to Use JSON-LD',
			'author' => 'John Doe',
			'datePublished' => '2026-03-19',
			'dateModified' => '2026-03-19',
			'image' => 'https://example.com/image.jpg',
			'description' => 'A guide to structured data',
		]);

		$result = $this->Meta->getArticle();
		$this->assertNotNull($result);
		$this->assertStringContainsString('"@type":', $result);
		$this->assertStringContainsString('Article', $result);
		$this->assertStringContainsString('"headline":', $result);
		$this->assertStringContainsString('How to Use JSON-LD', $result);
		$this->assertStringContainsString('Person', $result);
		$this->assertStringContainsString('John Doe', $result);
		$this->assertStringContainsString('"datePublished":', $result);
	}

	/**
	 * @return void
	 */
	public function testSetArticleMinimal(): void {
		$this->Meta->setArticle([
			'headline' => 'Simple Post',
		]);

		$result = $this->Meta->getArticle();
		$this->assertNotNull($result);
		$this->assertStringContainsString('Simple Post', $result);
		$this->assertStringNotContainsString('author', $result);
	}

	/**
	 * @return void
	 */
	public function testSetArticleMissingHeadline(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Article requires a 'headline' string.");
		$this->Meta->setArticle([
			'author' => 'John Doe',
		]);
	}

	/**
	 * @return void
	 */
	public function testGetArticleNull(): void {
		$result = $this->Meta->getArticle();
		$this->assertNull($result);
	}

	/**
	 * @return void
	 */
	public function testSetOrganization(): void {
		$this->Meta->setOrganization([
			'name' => 'Acme Inc',
			'url' => 'https://acme.com',
			'logo' => 'https://acme.com/logo.png',
			'sameAs' => [
				'https://twitter.com/acme',
				'https://facebook.com/acme',
			],
		]);

		$result = $this->Meta->getOrganization();
		$this->assertNotNull($result);
		$this->assertStringContainsString('"@type":', $result);
		$this->assertStringContainsString('Organization', $result);
		$this->assertStringContainsString('"name":', $result);
		$this->assertStringContainsString('Acme Inc', $result);
		$this->assertStringContainsString('https://acme.com', $result);
		$this->assertStringContainsString('"sameAs":', $result);
	}

	/**
	 * @return void
	 */
	public function testSetOrganizationMissingName(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Organization requires a 'name' string.");
		$this->Meta->setOrganization([
			'url' => 'https://acme.com',
		]);
	}

	/**
	 * @return void
	 */
	public function testSetOrganizationFromConfig(): void {
		Configure::write('Meta.organization', [
			'name' => 'Global Corp',
			'url' => 'https://global.com',
		]);
		$this->Meta = new MetaHelper($this->View);

		$this->Meta->setOrganization([]);

		$result = $this->Meta->getOrganization();
		$this->assertNotNull($result);
		$this->assertStringContainsString('Global Corp', $result);
	}

	/**
	 * @return void
	 */
	public function testSetOrganizationConfigMerge(): void {
		Configure::write('Meta.organization', [
			'name' => 'Global Corp',
			'url' => 'https://global.com',
			'logo' => 'https://global.com/logo.png',
		]);
		$this->Meta = new MetaHelper($this->View);

		$this->Meta->setOrganization([
			'name' => 'Local Division',
		]);

		$result = $this->Meta->getOrganization();
		$this->assertNotNull($result);
		$this->assertStringContainsString('Local Division', $result);
		$this->assertStringContainsString('https://global.com', $result);
		$this->assertStringContainsString('logo.png', $result);
	}

	/**
	 * @return void
	 */
	public function testGetOrganizationNull(): void {
		$result = $this->Meta->getOrganization();
		$this->assertNull($result);
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
