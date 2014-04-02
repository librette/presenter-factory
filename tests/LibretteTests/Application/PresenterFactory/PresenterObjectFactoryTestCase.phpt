<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette\Application\Request;
use Nette;
use Tester\Assert;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


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


class SystemContainer extends Nette\DI\Container
{

	protected $meta = array(
		'types' => array(
			'librettetests\application\presenterfactory\presentermock' => array('fooPresenter'),
		)
	);


	public function createServiceFooPresenter()
	{
		return new PresenterMock();
	}
}


/**
 * @author David Matějka
 */
class PresenterObjectFactoryTestCase extends Tester\TestCase
{

	public function setUp()
	{
	}


	public function testPresenterInDic()
	{
		$container = new SystemContainer();

		$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory($container,
			new Librette\Application\PresenterFactory\StaticInvalidLinkModeStrategy(99));

		$object = $presenterObjectFactory->createPresenter($class = 'LibretteTests\Application\PresenterFactory\PresenterMock');
		Assert::type($class, $object);
		Assert::same(99, $object->invalidLinkMode);
	}


	public function testPresenterNotInDic()
	{
		$presenterObjectFactory = new Librette\Application\PresenterFactory\PresenterObjectFactory($dic = new SystemContainer(),
			new Librette\Application\PresenterFactory\StaticInvalidLinkModeStrategy(1));

		$object = $presenterObjectFactory->createPresenter($class = 'LibretteTests\Application\PresenterFactory\BarPresenterMock');
		Assert::type($class, $object);
		Assert::type('LibretteTests\Application\PresenterFactory\PresenterMock', $object->fooPresenter);
	}
}


\run(new PresenterObjectFactoryTestCase());