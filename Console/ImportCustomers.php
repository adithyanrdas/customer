<?php

namespace Adithyan\Customer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Adithyan\Customer\Model\Customer;

class ImportCustomers extends Command
{
    /**
     * csv file console parameter name
     */
    const CSV_PARAM_NAME = 'sample-csv';
    /**
     * json file console parameter name
     */
    const JSON_PARAM_NAME = 'sample-json';

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * Constructor function
     *
     * @param Customer $customer
     * @param string|null $name
     */
    public function __construct(
        Customer $customer,
        string $name = null
    ) {
        $this->customer = $customer;
        parent::__construct($name);
    }

    /**
     * Console command configurations
     *
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::CSV_PARAM_NAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV file name'
            ),
            new InputOption(
                self::JSON_PARAM_NAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'JSON file name'
            )
        ];
        $this->setName('customer:import');
        $this->setDescription('Import customer data')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * Read csv file and Import customers
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputData = $this->validateInput($input, $output);
        if (empty($inputData)) {
            $output->writeln("Empty input file name parameter. check customer:import --help");
            return $this;
        } elseif ($inputData['type'] == 'csv')
        {
            $this->customer->ImportCustomersFromCsv($inputData['file_name']);
        } else {
            $this->customer->ImportCustomersFromJson($inputData['file_name']);
        }
        
        return $this;
    }

    /**
     * Validate command input parameters
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    public function validateInput($input, $output)
    {
        $inputData = [];
        if ($csvFileName = $input->getOption(self::CSV_PARAM_NAME)) {
            $inputData['type'] = 'csv';
            $inputData['file_name'] = $csvFileName;
        } else if ($jsonFileName = $input->getOption(self::JSON_PARAM_NAME)) {
            $inputData['type'] = 'json';
            $inputData['file_name'] = $jsonFileName;
        }

        return $inputData;
    }
}
