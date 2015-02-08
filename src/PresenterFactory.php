<?php
namespace Librette\Application\PresenterFactory;

use Nette;
use Nette\Application;


/**
 * Default presenter loader.
 *
 * @author David Grudl
 * @author David Matějka
 *
 * @method array getMapping()
 */
class PresenterFactory extends Nette\Object implements Application\IPresenterFactory
{

	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var array[] of module => splited mask */
	private $mapping = array();

	/** @var array */
	private $cache = array();

	/** @var \Librette\Application\PresenterFactory\IPresenterObjectFactory */
	private $presenterObjectFactory;


	/**
	 * @param IPresenterObjectFactory $presenterObjectFactory
	 */
	public function __construct(IPresenterObjectFactory $presenterObjectFactory)
	{
		$this->presenterObjectFactory = $presenterObjectFactory;
	}


	public function createPresenter($name)
	{
		return $this->presenterObjectFactory->createPresenter($this->getPresenterClass($name));
	}


	/**
	 * Generates and checks presenter class name.
	 *
	 * @param  string $name presenter name
	 * @return string  class name
	 * @throws Application\InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new Application\InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$classes = $this->formatPresenterClasses($name);
		if (!$classes) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', no applicable mapping found.");
		}
		$class = $this->findValidClass($classes);
		if (!$class) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', none of following classes were found: " . implode(', ', $classes));
		}

		$reflection = new Nette\Reflection\ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		return $this->cache[$name] = $class;
	}


	/**
	 * Sets mapping as pairs [module => mask]
	 *
	 * @param array $mapping
	 * @return self
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			foreach (is_array($mask) ? $mask : array($mask) as $currentMask) {
				$this->addMapping($module, $currentMask);
			}
		}

		return $this;
	}


	/**
	 * @param string $module
	 * @param string|array $mask
	 * @return $this
	 * @throws \Nette\InvalidStateException
	 */
	public function addMapping($module, $mask)
	{
		if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
			throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
		}
		$this->mapping[$module][] = array($m[1], $m[2] ? : '*Module\\', $m[3]);

		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 *
	 * @param  string $presenter
	 * @return array
	 */
	public function formatPresenterClasses($presenter)
	{
		$parts = explode(':', $presenter);
		$lastPos = 0;
		$possibleModules = array('*');
		while ($pos = strpos($presenter, ':', $lastPos)) {
			$possibleModules[] = substr($presenter, 0, $pos);
			$lastPos = $pos + 1;
		}
		$classes = array();
		foreach ($possibleModules as $module) {
			if (!isset($this->mapping[$module])) {
				continue;
			}
			$moduleOffset = $module == '*' ? 0 : (substr_count($module, ':') + 1);
			foreach ($this->mapping[$module] as $mapping) {
				$mappingParts = array_slice($parts, $moduleOffset);
				$class = $mapping[0];
				while ($part = array_shift($mappingParts)) {
					$class .= str_replace('*', $part, $mapping[count($mappingParts) ? 1 : 2]);
				}

				$classes[] = $class;
			}
		}

		return array_reverse($classes);
	}


	/**
	 * @param array $classes
	 * @return string|null
	 */
	protected function findValidClass($classes)
	{
		foreach ($classes as $class) {
			if (class_exists($class)) {
				return $class;
			}
		}

		return NULL;
	}
}
