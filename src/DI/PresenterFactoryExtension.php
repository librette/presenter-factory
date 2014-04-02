<?php
namespace Librette\Application\PresenterFactory\DI;

use Librette\Application\PresenterFactory\InvalidStateException;
use Nette;

/**
 * @author David Matějka
 */
class PresenterFactoryExtension extends Nette\DI\CompilerExtension
{

	protected $defaults = array(
		'mapping'         => array(
			'*'     => '*Module\\*Presenter',
			'Nette' => 'NetteModule\\*\\*Presenter',
		),
		'invalidLinkMode' => array(
			'debug'      => Nette\Application\UI\Presenter::INVALID_LINK_WARNING,
			'production' => Nette\Application\UI\Presenter::INVALID_LINK_SILENT,
		),
	);


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$mappings = $this->getMappings($this->getMappingConfig());
		$builder->removeDefinition('nette.presenterFactory');
		$presenterFactory = $builder->addDefinition($this->prefix('presenterFactory'))
									->setClass('Librette\Application\PresenterFactory\PresenterFactory');
		$presenterFactory->addSetup('setMapping', array($mappings));
		$builder->addDefinition('nette.presenterFactory')->setFactory($this->prefix('@presenterFactory'));

		$builder->addDefinition($this->prefix('presenterObjectFactory'))
				->setClass('Librette\Application\PresenterFactory\PresenterObjectFactory')
				->addSetup('setAlwaysCallInjects', array($this->shouldAlwaysCallInject()));


		$genericConfig = $this->getConfig($this->defaults);
		$mode = $builder->parameters['debugMode'] ? 'debug' : 'production';
		$builder->addDefinition($this->prefix('invalidLinkModeStrategy'))
				->setClass('\Librette\Application\PresenterFactory\StaticInvalidLinkModeStrategy')
				->setArguments(array($genericConfig['invalidLinkMode'][$mode]));

	}


	protected function getMappings($mappings)
	{
		$result = array();
		$this->addMappings($result, $mappings);

		foreach ($this->compiler->getExtensions('Librette\Application\PresenterFactory\DI\IPresenterMappingProvider') as $ext) {
			/** @var IPresenterMappingProvider $ext */
			$this->addMappings($result, $ext->getPresenterMappings());
		}

		return $result;
	}


	protected function addMappings(&$current, $mappings)
	{
		if (empty($mappings)) {
			return;
		}
		foreach ($mappings as $key => $mapping) {
			if (!is_array($mapping)) {
				$current[$key][] = $mapping;
			} else {
				$current[$key] = array_merge(isset($current[$key]) ? $current[$key] : array(), $mapping);
			}
		}
	}


	/**
	 * @return array
	 * @throws \Librette\Application\PresenterFactory\InvalidStateException
	 */
	protected function getMappingConfig()
	{
		$globalConfig = $this->compiler->getConfig();
		if (isset($globalConfig['nette']['application']['mapping']) && isset($globalConfig[$this->name]['mapping'])) {
			throw new InvalidStateException("You cannot use both nette.application.mapping and {$this->name}.mapping config section, choose one.");
		}
		$userConfig = isset($globalConfig[$this->name]['mapping']) ? $globalConfig[$this->name]['mapping'] :
			(isset($globalConfig['nette']['application']['mapping']) ? $globalConfig['nette']['application']['mapping'] : array());
		$config = Nette\DI\Config\Helpers::merge($userConfig, $this->defaults['mapping']);

		return $config;
	}


	private function shouldAlwaysCallInject()
	{
		$serviceDef = new Nette\DI\ServiceDefinition();

		return $serviceDef->inject == FALSE;
	}
}
