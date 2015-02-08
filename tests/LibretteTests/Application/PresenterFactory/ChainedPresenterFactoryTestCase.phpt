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
class ChainedPresenterFactoryTestCase extends Tester\TestCase
{

	public function setUp()
	{
	}


	public function testChain()
	{
		$chainedPresenterFactory = new Librette\Application\PresenterFactory\ChainedPresenterFactory(new PresenterObjectFactoryMock());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		$chainedPresenterFactory->addPresenterFactory(new FooPresenterFactory());
		$name = 'Foo';
		Assert::same('FooPresenter', $chainedPresenterFactory->getPresenterClass($name));
	}


	public function testChainFail()
	{
		$chainedPresenterFactory = new Librette\Application\PresenterFactory\ChainedPresenterFactory(new PresenterObjectFactoryMock());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		$chainedPresenterFactory->addPresenterFactory(new FailingPresenterFactory());
		Assert::exception(function () use ($chainedPresenterFactory) {
			$name = 'Foo';
			$chainedPresenterFactory->getPresenterClass($name);
		}, 'Nette\Application\InvalidPresenterException');
	}
}


class PresenterObjectFactoryMock implements Librette\Application\PresenterFactory\IPresenterObjectFactory
{

	public function createPresenter($class)
	{
	}

}


class FailingPresenterFactory implements Nette\Application\IPresenterFactory
{

	function getPresenterClass(& $name)
	{
		throw new Nette\Application\InvalidPresenterException("Unable to create presenter '$name'");
	}


	function createPresenter($name)
	{
	}
}


class FooPresenterFactory implements Nette\Application\IPresenterFactory
{

	function getPresenterClass(& $name)
	{
		return $name . 'Presenter';
	}


	function createPresenter($name)
	{
	}

}


\run(new ChainedPresenterFactoryTestCase());