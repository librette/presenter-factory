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


	public function testUnformatPresenterClassWithModule()
	{
		$factory = $this->presenterFactory;
		$factory->setMapping(array(
			'Foo2' => 'App2\*\*Presenter',
			'Foo3' => 'My\App\*Mod\*Presenter',
		));

		Assert::same('Foo', $factory->unformatPresenterClass('FooPresenter'));
		Assert::same('Foo:Bar', $factory->unformatPresenterClass('FooModule\BarPresenter'));
		Assert::same('Foo:Bar:Baz', $factory->unformatPresenterClass('FooModule\BarModule\BazPresenter'));

		Assert::same('Foo2', $factory->unformatPresenterClass('Foo2Presenter'));
		Assert::same('Foo2:Bar', $factory->unformatPresenterClass('App2\BarPresenter'));
		Assert::same('Foo2:Bar:Baz', $factory->unformatPresenterClass('App2\Bar\BazPresenter'));

		Assert::same('Foo3:Bar', $factory->unformatPresenterClass('My\App\BarPresenter'));
		Assert::same('Foo3:Bar:Baz', $factory->unformatPresenterClass('My\App\BarMod\BazPresenter'));

		Assert::null($factory->unformatPresenterClass('Foo'));
		Assert::null($factory->unformatPresenterClass('FooMod\BarPresenter'));
	}


	public function testUnformatPresenterClassWithoutModule()
	{
		$factory = $this->presenterFactory;
		$factory->setMapping(array(
			'Foo2' => 'App2\*Presenter',
			'Foo3' => 'My\App\*Presenter',
		));

		Assert::same('Foo2', $factory->unformatPresenterClass('Foo2Presenter'));
		Assert::same('Foo2:Bar', $factory->unformatPresenterClass('App2\BarPresenter'));
		Assert::same('Foo2:Bar:Baz', $factory->unformatPresenterClass('App2\BarModule\BazPresenter'));

		Assert::same('Foo3:Bar', $factory->unformatPresenterClass('My\App\BarPresenter'));
		Assert::same('Foo3:Bar:Baz', $factory->unformatPresenterClass('My\App\BarModule\BazPresenter'));

		Assert::null($factory->unformatPresenterClass('App2\Bar\BazPresenter'));
		Assert::null($factory->unformatPresenterClass('My\App\BarMod\BazPresenter'));
	}


	private function assertFormatClasses($expectedClass, $presenterName)
	{
		$classes = $this->presenterFactory->formatPresenterClasses($presenterName);
		Assert::same($expectedClass, reset($classes));
	}
}


run(new NetteCompatibilityTestCase());
