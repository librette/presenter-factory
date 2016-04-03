<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory;

use Nette\SmartObject;


class DefaultPresenterClassFormatter implements IPresenterClassFormatter
{
	use SmartObject;

	/** @var array */
	private $mapping = [];


	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			foreach (is_array($mask) ? $mask : [$mask] as $currentMask) {
				$this->addMapping($module, $currentMask);
			}
		}
	}


	public function getMapping(): array
	{
		return $this->mapping;
	}


	public function addMapping(string $module, string $mask)
	{
		if (preg_match('#^\\\\?(([\w\\\\]+\\\\)?[\w]+)\z#', $mask, $m)) { //direct presenter mapping
			$this->mapping[$module][] = $m[1];
		} elseif (preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*?\w*)\z#', $mask, $m)) {
			$this->mapping[$module][] = [$m[1], $m[2] ?: '*Module\\', $m[3]];
		} else {
			throw new \LogicException("Invalid mapping mask '$mask'.");
		}
	}


	public function formatPresenterClasses(string $name): \Iterator
	{
		$parts = explode(':', $name);
		$lastPos = 0;
		$possibleModules = ['*'];
		while ($pos = strpos($name, ':', $lastPos)) {
			$possibleModules[] = substr($name, 0, $pos);
			$lastPos = $pos + 1;
		}
		$possibleModules[] = ':' . $name;

		$classes = [];
		foreach ($possibleModules as $module) {
			if (!isset($this->mapping[$module])) {
				continue;
			}
			$moduleOffset = $module === '*' ? 0 : (substr_count($module, ':') + 1);
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

		return new \ArrayIterator(array_reverse($classes));
	}

}
