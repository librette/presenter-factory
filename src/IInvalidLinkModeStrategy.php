<?php
namespace Librette\Application\PresenterFactory;

use Nette\Application\UI\Presenter;

/**
 * @author David Matejka
 */
interface IInvalidLinkModeStrategy
{

	/**
	 * @param Presenter $presenter
	 * @return int one of Presenter::INVALID_LINK_* constants
	 */
	public function getInvalidLinkMode(Presenter $presenter);
}