<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory;

interface IPresenterClassFormatter
{

	public function formatPresenterClasses(string $name): \Iterator;

}
