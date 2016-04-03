<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Librette\Application\PresenterFactory\DefaultPresenterClassFormatter;
use Librette\Application\PresenterFactory\IPresenterClassFormatter;
use Librette\Application\PresenterFactory\PresenterFactory;
use Nette;
use Nette\Application\IPresenterFactory;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @author David Matějka
 * @testCase
 */
class ExtensionTest extends Tester\TestCase
{

	/** @var Nette\Configurator */
	protected $configurator;


	public function setUp()
	{
		$this->configurator = new Nette\Configurator();
		$this->configurator->setTempDirectory(TEMP_DIR);
	}


	public function testBasic()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/basic.neon');
		$this->configurator->setDebugMode(TRUE);
		$container = $this->configurator->createContainer();

		/** @var PresenterFactory $presenterFactory */
		Assert::type(PresenterFactory::class,
			$presenterFactory = $container->getByType(IPresenterFactory::class));

		/** @var DefaultPresenterClassFormatter $formatter */
		$formatter = $container->getByType(IPresenterClassFormatter::class);

		Assert::same([
			'*' => [['', '*Module\\', '*Presenter']],
			'Nette' => [['NetteModule\\', '*\\', '*Presenter']]
		], $formatter->getMapping());
	}


	public function testOriginalMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/originalMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var DefaultPresenterClassFormatter $formatter */
		$formatter = $container->getByType(IPresenterClassFormatter::class);

		Assert::same([
			'*' => [['App\\', '*Module\\', 'Presenters\\*Presenter']],
			'Nette' => [['NetteModule\\', '*\\', '*Presenter']]
		], $formatter->getMapping());
	}


	public function testNewMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/newMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var DefaultPresenterClassFormatter $formatter */
		$formatter = $container->getByType(IPresenterClassFormatter::class);

		Assert::same([
			'*' => [['App\\', '*Module\\', 'Presenters\\*Presenter'], ['', '*Module\\', '*Presenter']],
			'Nette' => [['NetteModule\\', '*\\', '*Presenter']]
		], $formatter->getMapping());
	}


	public function testBothMappingFail()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/bothMapping.neon');

		Assert::exception(function () {
			$this->configurator->createContainer();
		}, \LogicException::class, 'You cannot use both %a%');
	}


	public function testMappingProvider()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/mappingProvider.neon');
		$container = $this->configurator->createContainer();
		/** @var DefaultPresenterClassFormatter $formatter */
		$formatter = $container->getByType(IPresenterClassFormatter::class);

		Assert::same([
			'*' => [['', '*Module\\', '*Presenter']],
			'Nette' => [['NetteModule\\', '*\\', '*Presenter']],
			'Foo' => [['Foo\\', '*Module\\', '*Presenter']],
			'Bar' => [['', '*Module\\', '*Presenter'], ['Bar\\', '*\\', '*']]
		], $formatter->getMapping());
	}

}


class MyExtension extends Nette\DI\CompilerExtension implements Librette\Application\PresenterFactory\DI\IPresenterMappingProvider
{


	public function getPresenterMappings(): array
	{
		return [
			'Foo' => 'Foo\\*Module\\*Presenter',
			'Bar' => ['*Presenter', 'Bar\\*\\*',],
		];
	}

}


(new ExtensionTest())->run();
