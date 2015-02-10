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

	/** @var int */
	protected $invalidLinkMode;


	/**
	 * @param Nette\DI\Container
	 * @param int
	 */
	public function __construct(Nette\DI\Container $container, $invalidLinkMode)
	{
		$this->container = $container;
		$this->invalidLinkMode = $invalidLinkMode;
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
		$services = array_keys($this->container->findByTag('nette.presenter'), $class);
		if (count($services) > 1) {
			throw new Application\InvalidPresenterException("Multiple services of type $class found: " . implode(', ', $services) . '.');
		} elseif (count($services)) {
			$presenter = $this->container->createService($services[0]);
			$callInjects = FALSE;
		} elseif (count($services = $this->container->findByType($class)) === 1) {
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
			$presenter->invalidLinkMode = $this->invalidLinkMode;
		}

		return $presenter;
	}

}