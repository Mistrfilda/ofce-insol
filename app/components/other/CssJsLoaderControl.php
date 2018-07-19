<?php

declare(strict_types = 1);


namespace App\Components\Other;


use App\Lib\AppException;
use Nette\Http\Session;


class CssJsLoaderControl extends BaseControl
{
	/** @var string */
	private $wwwDir;

	/** @var \Nette\Http\SessionSection */
	private $sessionSection;

	public function __construct(string $wwwDir, Session $session)
	{
		parent::__construct();
		$this->wwwDir = $wwwDir;
		$this->sessionSection = $session->getSection('cssJsFiles');
	}

	public function render() : void
	{
		$jsonFile = @file_get_contents($this->wwwDir . '/temp/app-versions.json');

		if ($jsonFile === FALSE) {
			throw new AppException(AppException::WEBLOADER_ERROR, 'Can\'t load versions file');
		}

		$versions = [];
		if ($this->sessionSection->css === NULL) {
			$versions = json_decode($jsonFile, TRUE);
			$this->sessionSection->css = $versions['css'];
			$this->sessionSection->js = $versions['js'];
			$this->sessionSection->setExpiration('10 minutes');
		} else {
			$versions['css'] = $this->sessionSection->css;
			$versions['js'] = $this->sessionSection->js;
		}

		$this->getTemplate()->cssFile = $versions['css'];
		$this->getTemplate()->jsFile = $versions['js'];
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}
}