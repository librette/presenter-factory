<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette;
use Nette\Application\Request;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class PresenterObjectFactoryTestCase extends Tester\TestCase
{

	public function setUp()
	{
	}


	public function testPresenterInDic()
	{
		$container = new SystemContainer();

		$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory($container, 99);

		$object = $presenterObjectFactory->createPresenter($class = 'LibretteTests\Application\PresenterFactory\PresenterMock');
		Assert::type($class, $object);
		Assert::same(99, $object->invalidLinkMode);
	}


	public function testPresenterNotInDic()
	{
		$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory($dic = new SystemContainer(), 1);

		$object = $presenterObjectFactory->createPresenter($class = 'LibretteTests\Application\PresenterFactory\BarPresenterMock');
		Assert::type($class, $object);
		Assert::type('LibretteTests\Application\PresenterFactory\PresenterMock', $object->fooPresenter);
	}
}


class SystemContainer extends Nette\DI\Container
{

	protected $meta = [
		'types' => [
			'librettetests\application\presenterfactory\presentermock' => ['fooPresenter'],
		]
	];


	public function createServiceFooPresenter()
	{
		return new PresenterMock();
	}
}


class PresenterMock extends Nette\Application\UI\Presenter
{

	function run(Request $request)
	{
	}

}


class BarPresenterMock implements Nette\Application\IPresenter
{

	/** @var PresenterMock @inject */
	public $fooPresenter;


	function run(Request $request)
	{
	}
}


\run(new PresenterObjectFactoryTestCase());