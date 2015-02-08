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

	/** @var array[] of module => splited mask */
	private $mapping = [];

	/** @var array */
	private $cache = [];

	/** @var IPresenterObjectFactory */
	private $presenterObjectFactory;


	/**
	 * @param IPresenterObjectFactory
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
	 * @param  string presenter name
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
	 * @param array
	 * @return self
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			foreach (is_array($mask) ? $mask : [$mask] as $currentMask) {
				$this->addMapping($module, $currentMask);
			}
		}

		return $this;
	}


	/**
	 * @param string
	 * @param string|array
	 * @return $this
	 * @throws \Nette\InvalidStateException
	 */
	public function addMapping($module, $mask)
	{
		if (preg_match('#^\\\\?(([\w\\\\]+\\\\)?[\w]+)\z#', $mask, $m)) { //direct presenter mapping
			$this->mapping[$module][] = $m[1];
		} elseif (preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*?\w*)\z#', $mask, $m)) {
			$this->mapping[$module][] = [$m[1], $m[2] ?: '*Module\\', $m[3]];
		} else {
			throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
		}

		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 *
	 * @param  string
	 * @return array
	 */
	public function formatPresenterClasses($presenter)
	{
		$parts = explode(':', $presenter);
		$lastPos = 0;
		$possibleModules = ['*'];
		while ($pos = strpos($presenter, ':', $lastPos)) {
			$possibleModules[] = substr($presenter, 0, $pos);
			$lastPos = $pos + 1;
		}
		$possibleModules[] = ':' . $presenter;

		$classes = [];
		foreach ($possibleModules as $module) {
			if (!isset($this->mapping[$module])) {
				continue;
			}
			$moduleOffset = $module == '*' ? 0 : (substr_count($module, ':') + 1);
			foreach ($this->mapping[$module] as $mapping) {
				if (is_string($mapping)) {
					$classes[] = $mapping;
					continue;
				}
				$mappingParts = array_slice($parts, $moduleOffset, -1);
				$class = $mapping[0];
				while ($part = array_shift($mappingParts)) {
					$class .= str_replace('*', $part, $mapping[1]);
				}
				$class .= str_replace('*', end($parts), $mapping[2]);

				$classes[] = $class;
			}
		}

		return array_reverse($classes);
	}


	/**
	 * @param array
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
