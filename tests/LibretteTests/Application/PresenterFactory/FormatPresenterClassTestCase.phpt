<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';



/**
 * @author David Matějka
 * @testCase
 */
class FormatPresenterClassTestCase extends Tester\TestCase
{

	/** @var Librette\Application\PresenterFactory\PresenterFactory */
	protected $presenterFactory;


	public function setUp()
	{
		$this->presenterFactory = new Librette\Application\PresenterFactory\PresenterFactory(new PresenterObjectFactoryMock());
	}


	public function testSubmodule()
	{
		$this->presenterFactory->setMapping([
			'App'     => 'App\\*Module\\*Presenter',
			'App:Foo' => 'AppFoo\\*Module\\*Presenter',
		]);

		Assert::same(['AppFoo\\BarModule\\LoremPresenter', 'App\\FooModule\\BarModule\\LoremPresenter'],
			$this->presenterFactory->formatPresenterClasses('App:Foo:Bar:Lorem')
		);
		Assert::same(['App\\BarModule\\FooPresenter'], $this->presenterFactory->formatPresenterClasses('App:Bar:Foo'));
	}


	public function testMultipleMappingForModule()
	{
		$this->presenterFactory->setMapping([
			'App' => ['NS1\\*Module\\*Presenter', 'NS2\\*Module\\*Presenter'],
		]);
		$this->presenterFactory->addMapping('App', 'NS3\\*Module\\*Presenter');

		Assert::same(['NS3\\FooModule\\BarPresenter', 'NS2\\FooModule\\BarPresenter', 'NS1\\FooModule\\BarPresenter'],
			$this->presenterFactory->formatPresenterClasses('App:Foo:Bar')
		);
	}


	public function testDirectPresenterMapping()
	{
		$this->presenterFactory->setMapping([
			':Foo:Bar' => 'FooModule\\BarPresenter',
		]);
		Assert::same(['FooModule\\BarPresenter'], $this->presenterFactory->formatPresenterClasses('Foo:Bar'));
	}


	public function testPresenterNameOverwrite()
	{
		$this->presenterFactory->setMapping([
			'Foo' => 'Foo\\*Module\\BarPresenter',
		]);
		Assert::same(['Foo\\LoremModule\\BarPresenter'], $this->presenterFactory->formatPresenterClasses('Foo:Lorem:Ipsum'));
	}

}


class PresenterObjectFactoryMock implements Librette\Application\PresenterFactory\IPresenterObjectFactory
{

	public function createPresenter($class)
	{
	}

}


\run(new FormatPresenterClassTestCase());