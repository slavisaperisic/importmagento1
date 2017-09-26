<?php


/**
 * Class XMLExportImport_DB_Customers
 */
class XMLExportImport_DB_Customers
{
    /**
     * @var PDO $pdo
     */
    private $pdo;

    /**
     * @var $dateTimestamp
     */
    private $dateTimestamp;

    /**
     * @var bool $began
     */
    private $began;

    /**
     * @var Helper $helper
     */
    private $helper;

    /**
     * @var Customer
     */
    private $customerModel;

    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @var CustomerORM
     */
    private $customerORM;

    /**
     * @var AddressORM
     */
    private $addressORM;

    /**
     * @var AttributeORM
     */
    private $attributeORM;

    public function __construct(
        $pdo,
        $dateTimestamp
    )
    {
        $this->pdo = $pdo;
        $this->dateTimestamp = $dateTimestamp;
        $this->helper = new Helper();
        $this->began = false;
        $this->customerModel = new Customer();
        $this->addressModel = new Address();
        $this->customerORM = new CustomerORM($pdo);
        $this->addressORM = new AddressORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
    }

    /**
     * Main method that creates customer and assigns address
     *
     * @param $item
     *
     * @return bool
     */
    public function createCustomer($item)
    {
        $_customerValues = $this->helper->extractValuesCustomer($item);
        $_customerFields = $this->customerModel->getFields();

        $_addressValues = $this->helper->extractValuesAddress($item);
        $_addressFields = $this->addressModel->getFields();

        try {
            $this->pdo->beginTransaction();

            if ($entityCustomerId = $this->customerORM->insertCustomer($item)) {

                $this->attributeORM->fillAttributesGeneric($_customerFields, $entityCustomerId, $_customerValues, 'customer_entity_', 1);

                if ($entityAddressId = $this->addressORM->insertAddress($entityCustomerId, $item)) {
                    $this->attributeORM->fillAttributesGeneric($_addressFields, $entityAddressId, $_addressValues, 'customer_address_entity_', 2);
                }
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    /**
     * Main method that creates customer and assigns address
     *
     * @param $item
     *
     * @return bool
     */
    public function attachAddresses($item)
    {
        $_customerValues = $this->helper->extractValuesCustomer($item);
        $_customerFields = $this->customerModel->getFields();

        $_addressValues = $this->helper->extractValuesAddress($item);
        $_addressFields = $this->addressModel->getFields();

        try {
            $this->pdo->beginTransaction();

            $this->attributeORM->fillAttributesGeneric($_customerFields, (int)$item->NUMMER, $_customerValues, 'customer_entity_', 1);

            if ($entityAddressId = $this->addressORM->insertAddress((int)$item->NUMMER, $item)) {
                $this->attributeORM->fillAttributesGeneric($_addressFields, $entityAddressId, $_addressValues, 'customer_address_entity_', 2);
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    private $branches;

    /**
     * @param $xml
     * @param $xmlBranches
     *
     * @return mixed
     */
    public function handleCustomers($xml, $xmlBranches)
    {
        $this->loadBranches($xmlBranches);

        foreach ($this->branches as $branch) {
            foreach ($branch['groups'] as $group) {
                $this->customerORM->insertCustomerGroup($group);
            }
        }

        $addresses = $xml->ADR;

        foreach ($addresses as $item) {
            $this->createCustomer($item);
            $this->addToBranches($item);
        }

        return $this->branches;
    }

    /**
     * @param $item
     */
    public function addToBranches($item)
    {
        $email = (string)$item->CONTACT->EMAIL;

        foreach ($this->branches as $branchKey => $branches) {
            if (trim((string)$item->BRANCHE) == $branchKey && $email != '') {
                $this->branches[$branchKey]['customers'][] = $email;
            }
        }
    }

    /**
     * @param $xml
     */
    public function loadBranches($xml)
    {
        $this->branches = [];

        foreach ($xml->BRANCHE as $item) {
            $attributes = $item->attributes();

            $keywords = [];
            foreach ($item->KEYWORD as $subItem) {
                $keywords[] = (string)$subItem;
            }

            if(!empty($keywords)) {
                $this->branches[(string)$attributes['type']]['groups'] = $keywords;
            }
        }
    }
}