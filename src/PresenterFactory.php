<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory;

use Nette;
use Nette\Application;


/**
 * Default presenter loader.
 *
 * @author David Grudl
 * @author David Matějka
 */
class PresenterFactory implements Application\IPresenterFactory
{

	/** @var array */
	private $cache = [];

	/** @var callable */
	private $factory;

	/** @var IPresenterClassFormatter[] */
	private $formatters = [];

	/** @var DefaultPresenterClassFormatter */
	private $defaultFormatter;


	/**
	 * @param  callable  function (string $class): IPresenter
	 */
	public function __construct(callable $factory, DefaultPresenterClassFormatter $defaultFormatter)
	{
		$this->factory = $factory;
		$this->formatters[] = $defaultFormatter;
		$this->defaultFormatter = $defaultFormatter;
	}


	public function setMapping(array $mapping)
	{
		$this->defaultFormatter->setMapping($mapping);
	}


	public function addClassFormatter(IPresenterClassFormatter $classFormatter, bool $prefix = FALSE)
	{
		if ($prefix) {
			array_unshift($this->formatters, $classFormatter);
		} else {
			$this->formatters[] = $classFormatter;
		}
	}


	public function createPresenter(string $name): Application\IPresenter
	{
		return call_user_func($this->factory, $this->getPresenterClass($name));
	}


	public function getPresenterClass(string &$name): string
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new Application\InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$classes = $this->formatClasses($name);

		$class = $this->findValidClass($classes, $name);

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		return $this->cache[$name] = $class;
	}


	protected function findValidClass(\Iterator $classes, string $name): string
	{
		$classesArray = [];
		$anyClass = FALSE;
		foreach ($classes as $class) {
			$anyClass = TRUE;
			$classesArray[] = $class;
			if (class_exists($class)) {
				return $class;
			}
		}
		if ($anyClass) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', none of following classes were found: " . implode(', ',
					$classesArray));
		} else {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', no applicable mapping found.");
		}
	}


	private function formatClasses(string $name): \Generator
	{
		foreach ($this->formatters as $formatter) {
			foreach ($formatter->formatPresenterClasses($name) as $class) {
				yield $class;
			}
		}
	}

}
