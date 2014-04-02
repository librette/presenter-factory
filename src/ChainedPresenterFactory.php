<?php
namespace Librette\Application\PresenterFactory;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenterFactory;
use Nette\Object;

/**
 * @author David Matejka
 */
class ChainedPresenterFactory extends Object implements IPresenterFactory
{

	/** @var \Librette\Application\PresenterFactory\IPresenterObjectFactory */
	protected $presenterObjectFactory;

	/** @var IPresenterFactory[] */
	protected $presenterFactories = array();


	/**
	 * @param IPresenterObjectFactory $presenterObjectFactory
	 */
	public function __construct(IPresenterObjectFactory $presenterObjectFactory)
	{
		$this->presenterObjectFactory = $presenterObjectFactory;
	}


	/**
	 * @param IPresenterFactory $presenterFactory
	 */
	public function addPresenterFactory(IPresenterFactory $presenterFactory)
	{
		$this->presenterFactories[] = $presenterFactory;
	}


	function getPresenterClass(& $name)
	{
		$exceptionMessages = array();
		$lastException = NULL;
		foreach ($this->presenterFactories as $factory) {
			try {
				return $factory->getPresenterClass($name);
			} catch (InvalidPresenterException $lastException) {
			}
		}
		$exceptionMessage = "Cannot load presenter '$name'.' All " . count($exceptionMessages) . ' presenter factories have failed: ' . implode(';', $exceptionMessages);
		throw new InvalidPresenterException($exceptionMessage, 0, $lastException);
	}


	function createPresenter($name)
	{
		return $this->presenterObjectFactory->createPresenter($this->getPresenterClass($name));
	}

}
