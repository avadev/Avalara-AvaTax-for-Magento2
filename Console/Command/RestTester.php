<?php

namespace ClassyLlama\AvaTax\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use Symfony\Component\Console\Input\InputOption;
use Avalara\AvaTaxClient;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RestTester extends \Symfony\Component\Console\Command\Command
{
    protected $appState;

    protected $scopeConfig;

    public function __construct(
        AppState $appState,
        ScopeConfigInterface $scopeConfig,
        $name = null
    ) {
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;

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
                'account',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Account ID'
            ),
            new InputOption(
                'license',
                'l',
                InputOption::VALUE_OPTIONAL,
                'License Key'
            ),
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');

        $accountId = $input->getOption('account');
        $licenseKey = $input->getOption('license');

        $client = new AvaTaxClient('testApp', '1.0', 'localhost', 'sandbox');
        $client->withSecurity($accountId, $licenseKey);

        $result = $client->ping();

        $output->writeln("\n" . json_encode($result));

        $tb = new \Avalara\TransactionBuilder($client, "CLASSYINC", \Avalara\DocumentType::C_SALESORDER, 'My Customer');
        $result = $tb->withAddress('SingleLocation', '2920 Zoo Dr', NULL, NULL, 'San Diego', 'CA', '92101', 'US')
            ->withLine(100.0, 1, null, 'P0000000')
            ->create();

        $output->writeln("\n" . json_encode($result));
    }
}