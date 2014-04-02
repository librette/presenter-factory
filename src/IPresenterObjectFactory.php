<?php
namespace Librette\Application\PresenterFactory;

use Nette;


/**
 * @author David Matejka
 */
interface IPresenterObjectFactory
{

	/**
	 * Creates new presenter instance.
	 *
	 * @param  string $class presenter class name
	 * @return Nette\Application\IPresenter
	 */
	public function createPresenter($class);
}