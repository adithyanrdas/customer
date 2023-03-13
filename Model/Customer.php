<?php

namespace Adithyanrdas\Customer\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;

class Customer
{

    /**
     * @var File
     */
    protected $file;
    /**
     * @var Csv
     */
    protected $csv;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Construct function
     *
     * @param File $file
     * @param Csv $csv
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        File $file,
        Csv $csv,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
    ) {
        $this->file = $file;
        $this->csv = $csv;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Import customers from csv file
     *
     * @param string $fileName
     * @return void
     */
    public function ImportCustomersFromCsv($fileName)
    {
        $data = [];
        $customerData=[];
        $csvFile = $this->directoryList->getPath('var') . "/" . $fileName;
        try {
            if ($this->file->isExists($csvFile)) {
                $this->csv->setDelimiter(",");
                $data = $this->csv->getData($csvFile);
                if (!empty($data)) {
                    $this->logger->info('importing customer data');
                    foreach (array_slice($data, 1) as $key => $value) {
                        $customerData["first_name"] = trim($value['0']);
                        $customerData["last_name"] = trim($value['1']);
                        $customerData["email_address"] = trim($value['2']);
                        $this->createCustomer($customerData);
                    }
                    $this->logger->info('customer data import completed');
                }
            } else {
                $this->logger->info('csv file does not exist');
                return __('csv file not exist');
            }
        } catch (FileSystemException $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Import customers from json file
     *
     * @param string $fileName
     * @return void
     */
    public function ImportCustomersFromJson($fileName)
    {
        $customerData = [];
        $jsonFile = $this->directoryList->getPath('var') . "/" . $fileName;

        try {
            if ($this->file->isExists($jsonFile)) {
                $data = file_get_contents($jsonFile);
                $jsonData = json_decode($data);
                if (!empty($jsonData)) {
                    $this->logger->info('importing customer data');
                    foreach ($jsonData as $customer) {
                        $customerData["first_name"] = trim($customer->fname);
                        $customerData["last_name"] = trim($customer->lname);
                        $customerData["email_address"] = trim($customer->emailaddress);
                        $this->createCustomer($customerData);
                    }
                    $this->logger->info('customer data import completed');
                }
            } else {
                $this->logger->info('json file does not exist');
            }
        } catch (FileSystemException $e) {
            $this->logger->info($e->getMessage());
        }

    }

    /**
     * Create customer
     *
     * @param array $data
     * @return void
     */
    public function createCustomer($data)
    {
        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($data['email_address']);
        if (!$customer->getId()) {
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($data['first_name'])
                ->setLastname($data['last_name'])
                ->setEmail($data['email_address']);
            $customer->save();
        } else {
            $this->logger->info("customer with email " . $data["email_address"] . " already exists");
        }
    }
}
