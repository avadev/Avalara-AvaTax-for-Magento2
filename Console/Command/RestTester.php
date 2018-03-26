<?php

namespace ClassyLlama\AvaTax\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use Symfony\Component\Console\Input\InputOption;

class RestTester extends \Symfony\Component\Console\Command\Command
{
    public function __construct(
        AppState $appState,
        $name = null
    ) {
        $this->appState = $appState;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('tax:rest-test')
            ->setDescription('Test Avalara API')
            ->setDefinition($this->getInputList());
        parent::configure();
    }

    public function getInputList()
    {
        return [
            new InputOption(
                'test',
                't',
                InputOption::VALUE_OPTIONAL,
                'Test'
            ),
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');

        $value = $input->getOption('test');

        $output->writeln($value);

        $output->writeln("\nTest");
    }
}