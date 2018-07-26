<?php

declare (strict_types = 1);


namespace App\Console;


use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use Nette\Utils\Random;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


final class WebloaderCommand extends BaseCommand
{
	/** @var string  */
	private $wwwDir;

	/** @var array|mixed[] */
	private $config = [];

	/**
	 * WebloaderCommand constructor.
	 * @param string $wwwDir
	 * @param array|mixed[] $config
	 * @param null|string $name
	 */
	public function __construct(string $wwwDir, array $config, ?string $name = NULL)
	{
		parent::__construct($name);
		$this->wwwDir = $wwwDir . '/../www';
		$this->config = $config;
	}

	protected function configure() : void
	{
		parent::configure();
		$this->setName('www:minify');
	}

	protected function execute(InputInterface $input, OutputInterface $output) : void
	{
		$appVersion = Random::generate(8, '1-9');
		$cssMinifier = new CSS();
		foreach ($this->config['css'] as $css) {
			$cssMinifier->add($this->wwwDir . '/' . $css);
		}

		$cssName = sprintf('page-%s-css.css', $appVersion);
		$cssMinifier->minify(sprintf('%s/temp/%s', $this->wwwDir, $cssName));

		$jsMinifier = new JS();
		foreach ($this->config['js'] as $js) {
			$jsMinifier->add($this->wwwDir . '/' . $js);
		}

		$jsName = sprintf('page-%s-js.js', $appVersion);
		$jsMinifier->minify(sprintf('%s/temp/%s', $this->wwwDir, $jsName));

		$versionJson = [
			'js' => $jsName,
			'css' => $cssName
		];

		file_put_contents(sprintf('%s/temp/%s', $this->wwwDir, 'app-versions.json'), json_encode($versionJson));
	}
}