<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Librette\Application\PresenterFactory\DefaultPresenterClassFormatter;
use Librette\Application\PresenterFactory\PresenterFactory;
use Nette;
use Nette\Application\Request;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class GetPresenterClassTest extends Tester\TestCase
{

	/** @var PresenterFactory */
	protected $presenterFactory;


	public function setUp()
	{
		$classFormatter = new DefaultPresenterClassFormatter();
		$this->presenterFactory = $presenterFactory = new PresenterFactory(function () {}, $classFormatter);
		$classFormatter->addMapping('Foo', 'LibretteTests\\Application\\PresenterFactory\\*Presenter');
		$classFormatter->addMapping('Foo', 'App\\*Presenter');
	}


	public function testGetPresenterClass()
	{
		$presenterName = 'Foo:Bar';
		$class = $this->presenterFactory->getPresenterClass($presenterName);
		Assert::same('LibretteTests\\Application\\PresenterFactory\\BarPresenter', $class);
	}


	public function testInvalidPresenterName()
	{
		Assert::exception(function () {
			$presenterName = 'xxx-yyy';
			$this->presenterFactory->getPresenterClass($presenterName);
		}, 'Nette\Application\InvalidPresenterException', "Presenter name must be alphanumeric string, 'xxx-yyy' is invalid.");
	}


	public function testNonExistingClass()
	{
		Assert::exception(function () {
			$name = 'Foo:Lorem';
			$this->presenterFactory->getPresenterClass($name);
		}, 'Nette\Application\InvalidPresenterException', "Cannot load presenter 'Foo:Lorem', none of following classes were found: App\\LoremPresenter, LibretteTests\\Application\\PresenterFactory\\LoremPresenter");
	}


	public function testNoMapping()
	{
		Assert::exception(function () {
			$name = 'Bar:Foo';
			$this->presenterFactory->getPresenterClass($name);
		}, 'Nette\Application\InvalidPresenterException', "Cannot load presenter 'Bar:Foo', no applicable mapping found.");
	}
}

class BarPresenter implements Nette\Application\IPresenter
{

	function run(Request $request)
	{
	}

}


(new GetPresenterClassTest())->run();
