<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory\DI;

/**
 * @author David Matějka
 */
interface IPresenterMappingProvider
{

	/**
	 * returns array of mappings. possible formats:
	 * array of module => mapping pairs
	 * array of module => mapping[]
	 *
	 * @return array
	 */
	public function getPresenterMappings();

}
