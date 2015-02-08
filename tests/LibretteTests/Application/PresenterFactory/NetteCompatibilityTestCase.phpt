<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette\Application\PresenterFactory\IPresenterObjectFactory;
use Librette\Application\PresenterFactory\PresenterFactory;
use Nette;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';


class PresenterObjectFactoryMock implements IPresenterObjectFactory
{

	public function createPresenter($class)
	{
	}

}


/**
 * @author David Grudl
 * @author David Matejka
 * @testCase
 */
class NetteCompatibilityTestCase extends TestCase
{

	/** @var PresenterFactory */
	protected $presenterFactory;


	public function setUp()
	{
		$this->presenterFactory = new PresenterFactory(new PresenterObjectFactoryMock());
		$this->presenterFactory->addMapping('*', '*Module\\*Presenter');
	}


	public function testFormatPresenterClasses()
	{
		$this->presenterFactory->setMapping(array(
			'Foo2' => 'App2\*Presenter',
			'Foo3' => 'My\App\*Presenter',
		));


		$this->assertFormatClasses('Foo2Presenter', 'Foo2');
		$this->assertFormatClasses('App2\BarPresenter', 'Foo2:Bar');
		$this->assertFormatClasses('App2\BarModule\BazPresenter', 'Foo2:Bar:Baz');
		$this->assertFormatClasses('My\App\BarPresenter', 'Foo3:Bar');
		$this->assertFormatClasses('My\App\BarModule\BazPresenter', 'Foo3:Bar:Baz');

	}


	public function testFormatPresenterClassModule()
	{
		$this->presenterFactory->setMapping(array(
			'Foo2' => 'App2\*\*Presenter',
			'Foo3' => 'My\App\*Mod\*Presenter',
		));

		$this->assertFormatClasses('FooPresenter', 'Foo');
		$this->assertFormatClasses('FooModule\BarPresenter', 'Foo:Bar');
		$this->assertFormatClasses('FooModule\BarModule\BazPresenter', 'Foo:Bar:Baz');
		$this->assertFormatClasses('Foo2Presenter', 'Foo2');
		$this->assertFormatClasses('App2\BarPresenter', 'Foo2:Bar');
		$this->assertFormatClasses('App2\Bar\BazPresenter', 'Foo2:Bar:Baz');
		$this->assertFormatClasses('My\App\BarPresenter', 'Foo3:Bar');
		$this->assertFormatClasses('My\App\BarMod\BazPresenter', 'Foo3:Bar:Baz');
		$this->assertFormatClasses('NetteModule\FooPresenter', 'Nette:Foo');
	}



	private function assertFormatClasses($expectedClass, $presenterName)
	{
		$classes = $this->presenterFactory->formatPresenterClasses($presenterName);
		Assert::same($expectedClass, reset($classes));
	}
}


run(new NetteCompatibilityTestCase());
