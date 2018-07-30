<?php

declare (strict_types = 1);


namespace App\Console;


use App\Model\PersonModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


final class ProcessNewPersonsCommand extends BaseCommand
{
	/** @var PersonModel */
	private $personModel;

	public function __construct(PersonModel $personModel, ?string $name = NULL)
	{
		parent::__construct($name);
		$this->personModel = $personModel;
	}

	protected function configure() : void
	{
		parent::configure();
		$this->setName('process:persons');
	}

	protected function execute(InputInterface $input, OutputInterface $output) : void
	{
		$output->writeln('Checking for new persons invoices');
		$this->logger->log('PROCESS PERSONS COMMAND', 'RUNNING');

		$processedPersons = $this->personModel->processNewPersonsInvoices();

		$output->writeln(sprintf('Checking for new persons invoices finished, processed persons: %s', $processedPersons));
		$this->logger->log('PROCESS PERSONS COMMAND', 'FINISHED', ['count' => $processedPersons]);
	}
}