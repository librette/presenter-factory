<?php
namespace Librette\Application\PresenterFactory;

use Nette\Application\UI\Presenter;
use Nette\Object;

/**
 * @author David Matejka
 * @method int getMode()
 * @method setMode(int)
 */
class StaticInvalidLinkModeStrategy extends Object implements IInvalidLinkModeStrategy
{

	/** @var int */
	protected $mode;


	/**
	 * @param int $mode
	 */
	public function __construct($mode)
	{
		$this->mode = $mode;
	}


	public function getInvalidLinkMode(Presenter $presenter)
	{
		return $this->mode;
	}
}