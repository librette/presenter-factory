<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Librette\Application\PresenterFactory\DefaultPresenterClassFormatter;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class DefaultPresenterClassFormatterTest extends Tester\TestCase
{

	/** @var  DefaultPresenterClassFormatter */
	protected $formatter;


	public function setUp()
	{
		$this->formatter = new DefaultPresenterClassFormatter();
	}



	public function testAddMapping()
	{
		$this->formatter->addMapping('App', 'Bar\\*Module\\*Presenter');
		$this->formatter->addMapping('App', 'Foo\\*Module\\*Presenter');

		Assert::same(['Foo\\LoremModule\\IpsumPresenter', 'Bar\\LoremModule\\IpsumPresenter',],
			iterator_to_array($this->formatter->formatPresenterClasses('App:Lorem:Ipsum'), FALSE)
		);
	}


	/**
	 * @dataProvider getTestData
	 */
	public function testMapping(array $expected, array $mapping, string $presenterName)
	{
		$this->formatter->setMapping($mapping);
		$classes = $this->formatter->formatPresenterClasses($presenterName);
		Assert::same($expected, iterator_to_array($classes, FALSE));
	}


	public function getTestData()
	{
		yield [
			['Foo\\LoremModule\\BarPresenter',],
			['Foo' => 'Foo\\*Module\\BarPresenter',],
			'Foo:Lorem:Ipsum',
		];

		yield [
			['FooModule\\BarPresenter',],
			[':Foo:Bar' => 'FooModule\\BarPresenter',],
			'Foo:Bar',
		];

		yield [
			['NS3\\FooModule\\BarPresenter', 'NS2\\FooModule\\BarPresenter', 'NS1\\FooModule\\BarPresenter'],
			[
				'App' => [
					'NS1\\*Module\\*Presenter',
					'NS2\\*Module\\*Presenter',
					'NS3\\*Module\\*Presenter',
				],
			],
			'App:Foo:Bar',
		];

		yield [
			['AppFoo\\BarModule\\LoremPresenter', 'App\\FooModule\\BarModule\\LoremPresenter'],
			[
				'App' => 'App\\*Module\\*Presenter',
				'App:Foo' => 'AppFoo\\*Module\\*Presenter',
			],
			'App:Foo:Bar:Lorem',
		];

		yield [
			['App\\BarModule\\FooPresenter'],
			[
				'App' => 'App\\*Module\\*Presenter',
				'App:Foo' => 'AppFoo\\*Module\\*Presenter',
			],
			'App:Bar:Foo',
		];

		$mapping1 = [
			'*' => '*Module\\*Presenter',
			'Foo2' => 'App2\*Presenter',
			'Foo3' => 'My\App\*Presenter',
		];
		yield [
			['Foo2Presenter'],
			$mapping1,
			'Foo2',
		];

		yield [
			['App2\BarPresenter', 'Foo2Module\BarPresenter'],
			$mapping1,
			'Foo2:Bar',
		];

		yield [
			['App2\BarModule\BazPresenter', 'Foo2Module\BarModule\BazPresenter'],
			$mapping1,
			'Foo2:Bar:Baz',
		];

		yield [
			['My\App\BarPresenter', 'Foo3Module\BarPresenter'],
			$mapping1,
			'Foo3:Bar',
		];

		yield [
			['My\App\BarModule\BazPresenter', 'Foo3Module\BarModule\BazPresenter'],
			$mapping1,
			'Foo3:Bar:Baz',
		];

	}

}


(new DefaultPresenterClassFormatterTest())->run();
