<?php
namespace LibretteTests\Application\PresenterFactory;

use Librette;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class MyExtension extends Nette\DI\CompilerExtension implements Librette\Application\PresenterFactory\DI\IPresenterMappingProvider
{


	public function getPresenterMappings()
	{
		return array('Foo' => 'Foo\\*Module\\*Presenter', 'Bar' => array('*Presenter', 'Bar\\*\\*'));
	}

}


/**
 * @author David Matějka
 */
class ExtensionTestCase extends Tester\TestCase
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
		$this->configurator->addConfig(__DIR__ . '/config/basic.neon');
		$this->configurator->setDebugMode(TRUE);
		$container = $this->configurator->createContainer();
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		Assert::type('Librette\Application\PresenterFactory\PresenterFactory', $presenterFactory = $container->getByType('Nette\Application\IPresenterFactory'));
		$invalidLinkModeStrategy = $container->getByType('Librette\Application\PresenterFactory\StaticInvalidLinkModeStrategy');
		Assert::same(Nette\Application\UI\Presenter::INVALID_LINK_WARNING, $invalidLinkModeStrategy->mode);

		Assert::same(['*'     => [['', '*Module\\', '*Presenter']],
					  'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());
	}


	public function testProduction()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/basic.neon');
		$this->configurator->setDebugMode(FALSE);
		$container = $this->configurator->createContainer();
		$invalidLinkModeStrategy = $container->getByType('Librette\Application\PresenterFactory\StaticInvalidLinkModeStrategy');
		Assert::same(Nette\Application\UI\Presenter::INVALID_LINK_SILENT, $invalidLinkModeStrategy->mode);
	}


	public function testOriginalMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/originalMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['App\\', '*Module\\', 'Presenters\\*Presenter']],
					  'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());
	}


	public function testNewMapping()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/newMapping.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['App\\', '*Module\\', 'Presenters\\*Presenter'], ['', '*Module\\', '*Presenter']],
					  'Nette' => [['NetteModule\\', '*\\', '*Presenter']]], $presenterFactory->getMapping());
	}


	public function testBothMappingFail()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/bothMapping.neon');

		Assert::exception(function () {
			$this->configurator->createContainer();
		}, '\Librette\Application\PresenterFactory\InvalidStateException');
	}


	public function testMappingProvider()
	{
		$this->configurator->addParameters(['container' => ['class' => 'SystemContainer_' . __FUNCTION__]]);
		$this->configurator->addConfig(__DIR__ . '/config/mappingProvider.neon');
		$container = $this->configurator->createContainer();
		/** @var Librette\Application\PresenterFactory\PresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		Assert::same(['*'     => [['', '*Module\\', '*Presenter']],
					  'Nette' => [['NetteModule\\', '*\\', '*Presenter']],
					  'Foo'   => [['Foo\\', '*Module\\', '*Presenter']],
					  'Bar'   => [['', '*Module\\', '*Presenter'], ['Bar\\', '*\\', '*']]], $presenterFactory->getMapping());
	}

}


\run(new ExtensionTestCase());