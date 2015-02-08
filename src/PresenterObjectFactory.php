<?php
namespace Librette\Application\PresenterFactory;

use Nette;
use Nette\Application;
use Nette\Object;

/**
 * @author David Matejka
 */
class PresenterObjectFactory extends Object implements IPresenterObjectFactory
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var bool */
	private $alwaysCallInjects = FALSE;

	/** @var IInvalidLinkModeStrategy */
	protected $invalidLinkModeStrategy;


	/**
	 * @param string $container
	 */
	public function __construct(Nette\DI\Container $container, IInvalidLinkModeStrategy $invalidLinkModeStrategy)
	{
		$this->container = $container;
		$this->invalidLinkModeStrategy = $invalidLinkModeStrategy;
	}


	/**
	 * @return boolean
	 */
	public function isAlwaysCallInjects()
	{
		return $this->alwaysCallInjects;
	}


	/**
	 * @param boolean
	 */
	public function setAlwaysCallInjects($alwaysCallInjects)
	{
		$this->alwaysCallInjects = $alwaysCallInjects;
	}


	/**
	 * Creates new presenter instance.
	 *
	 * @param  string presenter class name
	 * @return Application\IPresenter
	 */
	public function createPresenter($class)
	{
		$callInjects = $this->alwaysCallInjects;
		if (count($services = $this->container->findByType($class)) === 1) {
			$presenter = $this->container->createService($services[0]);
		} else {
			$presenter = $this->container->createInstance($class);
			$callInjects = TRUE;
		}
		if (!$presenter instanceof Application\IPresenter) {
			throw new UnexpectedValueException("Unable to create create presenter, returned value is not Nette\\Application\\IPresenter type.");
		}
		if ($callInjects) {
			$this->container->callInjects($presenter);
		}

		if ($presenter instanceof Application\UI\Presenter && $presenter->invalidLinkMode === NULL) {
			$presenter->invalidLinkMode = $this->invalidLinkModeStrategy->getInvalidLinkMode($presenter);
		}

		return $presenter;
	}

}