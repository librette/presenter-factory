<?php declare(strict_types = 1);

namespace Librette\Application\PresenterFactory\DI;

use Librette\Application\PresenterFactory\DefaultPresenterClassFormatter;
use Librette\Application\PresenterFactory\IPresenterClassFormatter;
use Librette\Application\PresenterFactory\PresenterFactory;
use Nette;

/**
 * @author David Matějka
 */
class PresenterFactoryExtension extends Nette\DI\CompilerExtension
{

	protected $defaults = [
		'mapping' => [
			'*' => '*Module\\*Presenter',
			'Nette' => 'NetteModule\\*\\*Presenter',
		],
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$mappings = $this->getMappings($this->getMappingConfig());
		$builder->addDefinition($this->prefix('presenterClassFormatter'))
			->setClass(IPresenterClassFormatter::class)
			->setFactory(DefaultPresenterClassFormatter::class)
			->addSetup('setMapping', [$mappings]);

		$def = $builder->getDefinition('application.presenterFactory');
		$factory = $def->getFactory();
		$args = $factory->arguments;
		$args[] = $this->prefix('@presenterClassFormatter');
		$def->setFactory(PresenterFactory::class, $args);

	}


	protected function getMappings($mappings): array
	{
		$result = [];
		$this->addMappings($result, $mappings);

		/** @var IPresenterMappingProvider $ext */
		foreach ($this->compiler->getExtensions(IPresenterMappingProvider::class) as $ext) {
			$this->addMappings($result, $ext->getPresenterMappings());
		}

		return $result;
	}


	protected function addMappings(array &$current, array $mappings)
	{
		if (empty($mappings)) {
			return;
		}
		foreach ($mappings as $key => $mapping) {
			if (!is_array($mapping)) {
				$current[$key][] = $mapping;
			} else {
				$current[$key] = array_merge(isset($current[$key]) ? $current[$key] : [], $mapping);
			}
		}
	}


	protected function getMappingConfig(): array
	{
		$globalConfig = $this->compiler->getConfig();
		if (isset($globalConfig['application']['mapping'], $globalConfig[$this->name]['mapping'])) {
			throw new \LogicException("You cannot use both nette.application.mapping and {$this->name}.mapping config section, choose one.");
		}
		$userConfig = isset($globalConfig[$this->name]['mapping']) ? $globalConfig[$this->name]['mapping'] :
			(isset($globalConfig['application']['mapping']) ? $globalConfig['application']['mapping'] : []);
		$config = Nette\DI\Config\Helpers::merge($userConfig, $this->defaults['mapping']);

		return $config;
	}

}
