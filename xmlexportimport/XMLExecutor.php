<?php

class XMLExecutor
{
    /**
     * @var ModelInstantiator
     */
    private $instantiator;

    /**
     * @return array|mixed
     */
    public function start()
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        return $mtime;
    }

    public function end($startTime)
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        return ($endtime - $startTime);
    }

    /**
     * @param $files
     * @param $connection
     * @param $dateTimestamp
     *
     * @return mixed
     */
    function importCustomers($files, $connection, $dateTimestamp)
    {
        $db = new XMLExportImport_DB_Customers($connection, $dateTimestamp);
        $fullFileName = $files['customers_addresses'];
        $fullFileNameBranches = $files['branches'];

        $xml = simplexml_load_file($fullFileName);
        $xmlBranches = simplexml_load_file($fullFileNameBranches);

        $this->printOut('==> Importing ==> customers_addresses');

        return $db->handleCustomers($xml, $xmlBranches);
    }

    /**
     * @param $files
     * @param $connection
     * @param $dateTimestamp
     */
    function importCategories($files, $connection, $dateTimestamp)
    {
        $db = new XMLExportImport_DB_Categories($connection, $dateTimestamp);
        $fullFileName = $files['categories'];

        $xml = simplexml_load_file($fullFileName);


        $this->printOut('==> Importing ==> categories');

        $db->handleCategories($xml);
    }

    /**
     * @param $files
     * @param $connection
     * @param $dateTimestamp
     * @param $config
     */
    function importProducts($files, $connection, $dateTimestamp, $config)
    {
        $db = new XMLExportImport_DB_Products($connection, $dateTimestamp);
        $fullFileName = $files['products'];

        $xml = simplexml_load_file($fullFileName);

        $this->printOut("==> Importing ==> products");

        $db->handleProducts($xml, $config);
    }

    /**
     * @param PDO $connection
     */
    public function setup(PDO $connection)
    {
        $connection->beginTransaction();

        $connection->query("CREATE TABLE IF NOT EXISTS `price_from` (
          `id` int(5) NOT NULL AUTO_INCREMENT,
          `product_id` int(5) NOT NULL,
          `group_id` int(5) NOT NULL,
          `value` varchar(50) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $connection->commit();
    }

    /**
     * @param PDO $connection
     */
    public function truncate(PDO $connection)
    {
        $connection->beginTransaction();

        $connection->query("SET FOREIGN_KEY_CHECKS = 0;");
        $connection->query("TRUNCATE TABLE uni_fileuploader;");
        $connection->query("TRUNCATE TABLE catalog_product_bundle_option_value;");
        $connection->query("TRUNCATE TABLE catalog_product_bundle_selection;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_datetime;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_decimal;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_gallery;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_int;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_media_gallery;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_media_gallery_value;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_group_price;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_text;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_tier_price;");
        $connection->query("TRUNCATE TABLE catalog_product_entity_varchar;");
        $connection->query("TRUNCATE TABLE catalog_product_entity;");
        $connection->query("TRUNCATE TABLE customerprices_prices;");
        $connection->query("TRUNCATE TABLE cataloginventory_stock_item;");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1;");

        $connection->commit();
    }

    /**
     * @param $files
     * @param $connection
     * @param $dateTimestamp
     */
    function importPrices($files, $connection, $dateTimestamp)
    {
        $db = new XMLExportImport_DB_Prices($connection, $dateTimestamp);
        $fullFileNamePrices = $files['prices'];

        $xmlPrices = simplexml_load_file($fullFileNamePrices);

        $this->printOut('==> Importing ==> prices');

        $db->handlePrices($xmlPrices);
    }

    /**
     * @param $files
     * @param $connection
     * @param $dateTimestamp
     */
    function importOrders($files, $connection, $dateTimestamp)
    {
        $db = new XMLExportImport_DB_Orders($connection, $dateTimestamp);
        $fullFileNameOrders = $files['orders'];

        $xmlOrders = simplexml_load_file($fullFileNameOrders);

        $this->printOut('==> Importing ==> orders');

        $db->handleOrders($xmlOrders);
    }

    /**
     * @param $arg
     */
    public function printOut($arg)
    {
        echo "\e[31m" . $arg . "\e[0m" . "\n";
    }

    public function createBlock($customer)
    {
        $addressId = $this->instantiator->getAttributeORM()->getAddressIdByCustomerId($customer['entity_id']);

        if (trim($addressId) == '') {
            return '';
        }

        return
            [
                '@attributes' => ['name' => ''],
                'NUMMER' => $customer['entity_id'],
                'ID' => '333',
                'APPELATION' => [
                    '@attributes' => [
                        'short' => 'ADR_FIRMA0'
                    ],
                    '@value' => $customer['group_id']
                ],
                'GROUP' => [
                    '@attributes' => [
                        'short' => 'ADR_GRUPPE'
                    ],
                    '@value' => '&#214;K+'
                ],
                'LINO' => 'A&#214;K+_270347',
                'ADDRESS' => [
                    'LINE1' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'address', $addressId, 2, 'varchar', 'customer_address_entity_'
                    ),
                    'LINE2' => '',
                    'LINE3' => '',
                    'LINE4' => '',
                    'HOME' => [
                        'STREET' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                            'street', $addressId, 2, 'text', 'customer_address_entity_'
                        ),
                        'COUNTRY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                            'country_id', $addressId, 2, 'varchar', 'customer_address_entity_'
                        ),
                        'ZIP' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                            'postcode', $addressId, 2, 'varchar', 'customer_address_entity_'
                        ),
                        'CITY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                            'city', $addressId, 2, 'varchar', 'customer_address_entity_'
                        ),
                    ],
                    'POBOX' => [
                        'ZIP' => '',
                        'CITY' => '',
                        'POBOX' => ''
                    ],
                ],
                'CONTACT' => [
                    'PHONE' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'telephone', $addressId, 2, 'varchar', 'customer_address_entity_'
                    ),
                    'FAX' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'fax', $addressId, 2, 'varchar', 'customer_address_entity_'
                    ),
                    'EMAIL' => $customer['email'],
                    'MOBILE' => '',
                    'URL' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'customerurl', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                ],
                'ACCOUNTDETAILS' => [
                    'ACCOUNTNO' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'accountno', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                    'BANKIDENTIFICATION' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'bankidentification', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                    'BANK' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'bank', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                    'IBAN' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'iban', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                    'BIC' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'bic', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                    ),
                    'ISOLK' => [
                        '@attributes' => [
                            'short' => 'ADR_ISOLK'
                        ],
                        '@value' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                            'isolk', $customer['entity_id'], 1, 'varchar', 'customer_entity_'
                        ),
                    ]
                ],
                'BRANCHE' => 'LFH'
            ];
    }

    /**
     * @param $product
     *
     * @return array
     */
    public function createBlockProduct($product)
    {
        $productId = $product['entity_id'];

        $productCategories = $this->instantiator->getProductORM()->getProductCategories($productId);
        $productKeywords = $this->instantiator->getProductORM()->getProductKeywords($productId);
        $productFiles = $this->instantiator->getProductORM()->getProductFiles($productId);

        return [
            '@attributes' => ['active' => '1'],
            'ARTNO' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                'artno', $productId, 4, 'varchar', 'catalog_product_entity_'
            ),
            'ARTID' => $product['sku'],
            'EAN' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                'ean', $productId, 4, 'varchar', 'catalog_product_entity_'
            ),
            'TAX' => '',
            'CATEGORIES' => $productCategories,
            'HAUPTNR' => '',
            'FARBE' => '',
            'GEWICHT' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                'weight', $productId, 4, 'decimal', 'catalog_product_entity_'
            ),
            'KEYWORDS' => $productKeywords,
            'TEXTS' => [
                'LANG' => [
                    '@attributes' => [
                        'lang' => 'D'
                    ],
                    'TEXT' => [
                        [
                            '@attributes' => [
                                'type' => 'DE_BESCHR',
                                'name' => 'Kurzbeschreibung Deutsch',
                            ],
                            '@value' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                                'description', $productId, 4, 'text', 'catalog_product_entity_'
                            ),
                        ],
                        [
                            '@attributes' => [
                                'type' => 'DE_LANGBES',
                                'name' => 'Langbeschreibung Deutsch',
                            ],
                            '@value' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                                'short_description', $productId, 4, 'text', 'catalog_product_entity_'
                            ),
                        ],
                        [
                            '@attributes' => [
                                'type' => 'DE_TITEL',
                                'name' => 'Artikel&#252;berschrift Deutsch',
                            ],
                            '@value' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                                'name', $productId, 4, 'varchar', 'catalog_product_entity_'
                            ),
                        ]
                    ]
                ]
            ],
            'FILES' =>
                $productFiles
            ,
            'CROSSSELL' => '',
            'UPSELL' => ''
        ];
    }

    /**
     * @param $order
     *
     * @return array
     */
    public function createBlockOrder($order)
    {
        $paymentData = $this->instantiator->getOrderORM()->getPaymentData($order['entity_id']);
        $billingAddressDataCustomer = $this->instantiator->getAddressORM()->getBillingAddressDataCustomer($order['billing_address_id']);
        $orderItems = $this->instantiator->getOrderORM()->getOrderItems($order['entity_id']);

        return [
            '@attributes' => ['active' => '1'],
            'SHOP' => 'CLB2B',
            'ORDER_NO' => $order['entity_id'],
            'ORDER_DATE' => $order['created_at'],
            'PAYMENT' => $paymentData['method'],
            'PAYMENT_AMOUNT' => $order['base_grand_total'],
            'CURRENCY' => 'EUR',
            'SHIPPING' => $order['shipping_description'],
            'SHIPPING_COST' => $order['base_shipping_amount'],
            'ADDRESS' =>
                [
                    [
                        '@attributes' => [
                            'lang' => 'R'
                        ],
                        'CUSTOMER_ID' => $billingAddressDataCustomer['entity_id'],
                        'CUSTOMER_NO' => $billingAddressDataCustomer['entity_id'],
                        'SALUTATION' => [],
                        'FIRSTNAME' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('firstname', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'LASTNAME' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('lastname', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'STREET' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('street', $order['billing_address_id'], 2, 'text', 'customer_address_entity_'),
                        'ZIP' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('postcode', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'COUNTRY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('country_id', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'CITY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('city', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'EMAIL' => $billingAddressDataCustomer['email'],
                        'PHONE' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('telephone', $order['billing_address_id'], 2, 'varchar', 'customer_address_entity_')
                    ],
                    [
                        '@attributes' => [
                            'lang' => 'L'
                        ],
                        'FIRSTNAME' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('firstname', $order['shipping_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'LASTNAME' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('lastname', $order['shipping_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'STREET' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('street', $order['shipping_address_id'], 2, 'text', 'customer_address_entity_'),
                        'ZIP' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('postcode', $order['shipping_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'COUNTRY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('country_id', $order['shipping_address_id'], 2, 'varchar', 'customer_address_entity_'),
                        'CITY' => $this->instantiator->getAttributeORM()->getAttributeValueByCode('city', $order['shipping_address_id'], 2, 'varchar', 'customer_address_entity_')
                    ]
                ],
            'ITEMS' => $orderItems
        ];
    }

    public function createBlockPrice($priceListElement)
    {
        $articles = [];

        foreach ($priceListElement as $item) {
            $product = $item['product'];
            $price = $this->instantiator->getPriceORM()->getPriceByGroupAndProductId($item['customer_group_id'], $product['entity_id']);
            if ($price) {
                $articles[] = [
                    'ARTNO' => $this->instantiator->getAttributeORM()->getAttributeValueByCode(
                        'artnr', $product['entity_id'], 4, 'varchar', 'catalog_product_entity_'
                    ),
                    'ARTID' => $product['sku'],
                    'PRICE' => $price,
                    'DISCOUNT' => 0,
                    'FROM' =>  $this->instantiator->getPriceORM()->getFromValue($item['customer_group_id'], $product['entity_id']),
                ];
            }
        }

        $return = [
            '@attributes' => ['active' => '1'],
            'LINO' => $priceListElement[0]['customer_group_id'],
            'CURRENCY' => [
                '@attributes' => [
                    'short' => 'ADR_WAEHR'
                ],
                '@value' => 'EUR',
            ],
            'BN' => 'N',
            'ARTICLE' => $articles
        ];

        return $return;
    }

    /**
     * @param ModelInstantiator $instantiator
     *
     * @return bool|int
     */
    public function exportCustomers(ModelInstantiator $instantiator)
    {
        $this->instantiator = $instantiator;

        $customers = $instantiator->getCustomerORM()->getAllCustomers();

        $customerBlocks = [];
        foreach ($customers as $customer) {
            $customerBlocks[] = $this->createBlock($customer);
        }

        $parentAttributes = [
            'id' => 'CLB2B',
            'name' => 'B2B Shop',
            'exportdate' => '19.07.2017',
            'exporttype' => 'ADDRESSES'
        ];

        $dataBlock = [
            '@attributes' => $parentAttributes,
            'ADR' => $customerBlocks
        ];

        $xml = Array2XML::createXML('SHOP', $dataBlock);
        $this->printOut('exported customers');
        return file_put_contents('/var/www/vhosts/claytec.de/httpdocs/shop/shell/exported/ADDRESSEN.XML', $xml->saveXML());
    }

    /**
     * @param ModelInstantiator $instantiator
     *
     * @return bool|int
     */
    public function exportProducts(ModelInstantiator $instantiator)
    {
        $this->instantiator = $instantiator;

        $products = $instantiator->getProductORM()->getAllProducts();

        $productBlocks = [];
        foreach ($products as $product) {
            $productBlocks[] = $this->createBlockProduct($product);
        }

        $parentAttributes = [
            'id' => 'CLB2B',
            'name' => 'B2B Shop',
            'exportdate' => '19.07.2017',
            'exporttype' => 'SHOPDATTEN'
        ];

        $dataBlock = [
            '@attributes' => $parentAttributes,
            'ARTICLE' => $productBlocks
        ];

        $xml = Array2XML::createXML('SHOP', $dataBlock);

        $this->printOut('exported products');

        return file_put_contents('/var/www/vhosts/claytec.de/httpdocs/shop/shell/exported/' . $parentAttributes['exporttype'] . '.XML', $xml->saveXML());
    }

    /**
     * @param ModelInstantiator $instantiator
     */
    public function exportOrders(ModelInstantiator $instantiator)
    {
        $this->instantiator = $instantiator;

        $orders = $instantiator->getOrderORM()->getOrders();

        $blocks = [];
        foreach ($orders as $order) {
            $blocks[] = $this->createBlockOrder($order);
        }

        $parentAttributes = [
            'exporttype' => 'ORDERS'
        ];

        $dataBlock = [
            'ORDER' => $blocks
        ];

        $xml = Array2XML::createXML('ORDERS', $dataBlock);

        $this->printOut('exported orders');

        file_put_contents('/var/www/vhosts/claytec.de/httpdocs/shop/shell/exported/' . $parentAttributes['exporttype'] . '.XML', $xml->saveXML());
    }

    public function exportPrices(ModelInstantiator $instantiator)
    {
        $this->instantiator = $instantiator;

        $prices = $instantiator->getPriceORM()->getAll();

        $blocks = [];
        foreach ($prices as $priceList) {
            $blocks[] = $this->createBlockPrice($priceList);
        }

        $parentAttributes = [
            'id' => 'CLB2B',
            'name' => 'B2B Shop',
            'exportdate' => '19.07.2017',
            'exporttype' => 'PREISE'
        ];

        $dataBlock = [
            'PRICELIST' => $blocks
        ];

        $xml = Array2XML::createXML('SHOP', $dataBlock);

        $this->printOut('exported prices');

        file_put_contents('/var/www/vhosts/claytec.de/httpdocs/shop/shell/exported/' . $parentAttributes['exporttype'] . '.XML', $xml->saveXML());
    }

    public function zipFiles()
    {
        // Get real path for our folder
        $rootPath = realpath('/var/www/vhosts/claytec.de/httpdocs/shop/shell/exported');

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open('../file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }
}