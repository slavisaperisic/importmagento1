<?php

/**
 * Class XMLExportImport_DB_Orders
 */
class XMLExportImport_DB_Orders
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
     * @var OrderORM
     */
    private $orderORM;

    /**
     * @var AddressORM
     */
    private $addressORM;

    /**
     * @var CustomerORM
     */
    private $customerORM;

    /**
     * @var AttributeORM
     */
    private $attributeORM;

    /**
     * @var ProductORM
     */
    private $productORM;

    /**
     * @var bool $began
     */
    private $began;

    /**
     * @var Helper $helper
     */
    private $helper;

    public function __construct(
        $pdo,
        $dateTimestamp
    )
    {
        $this->pdo = $pdo;
        $this->dateTimestamp = $dateTimestamp;
        $this->orderORM = new OrderORM($pdo);
        $this->addressORM = new AddressORM($pdo);
        $this->customerORM = new CustomerORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
        $this->productORM = new ProductORM($pdo);
        $this->helper = new Helper();
        $this->began = false;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public function createOrder($item)
    {
        try {
            $this->pdo->beginTransaction();

            $billingAddressData = $item->ADDRESS[0];
            $shippingAddressData = $item->ADDRESS[1];

            $products = $item->ITEMS->ITEM;

            if ($customerId = $this->customerORM->insertCustomerFromOrder($item)) {
                $billingAddressId = $this->createBillingAddress($customerId, $billingAddressData);
                $shippingAddressId = $this->createShippingAddress($customerId, $shippingAddressData);

                $reservedOrderId = $this->orderORM->getReservedOrderId();
                $quoteId = $this->createQuote($item, $reservedOrderId);

                $_orderValues = $this->helper->extractValuesOrder(
                    $item,
                    $billingAddressId,
                    $this->customerORM->getCustomerGroupId(),
                    $quoteId,
                    $shippingAddressId,
                    $this->getOrderWeight($item),
                    $reservedOrderId,
                    $this->getShippingMethod($item),
                    $customerId
                );

                if ($orderId = $this->orderORM->insertOrder($_orderValues)) {

                    $_orderGridValues = $this->helper->extractValuesOrderGrid(
                        $item,
                        $orderId,
                        $reservedOrderId,
                        $customerId
                    );

                    $this->orderORM->insertOrderGrid($_orderGridValues);
                    $artidAttributeId = $this->attributeORM->selectAttributeIdByCode('artid', 4);

//                    $this->pdo->commit();

                    foreach ($products as $product) {
                        $productData = $this->productORM->loadProductData(
                            (string)$product->ARTID,
                            $artidAttributeId
                        );

                        if ($productData['product_id'] != '') {
                            $_quoteItemValues = $this->helper->extractItemValuesQuote($productData, $quoteId, $product);

                            if ($quoteItemId = $this->orderORM->insertQuoteItem($_quoteItemValues)) {

                                $_orderItemValues = $this->helper->extractItemValuesOrder($productData, $orderId, $quoteItemId, $product);
                                $this->orderORM->insertOrderItem($_orderItemValues);
                            }
                        }
                    }

                    $this->orderORM->insertOrderPayment($_orderValues);

                    $_addressValuesShipping = [
                        'firstname' => (string)$shippingAddressData->FIRSTNAME,
                        'lastname' => (string)$shippingAddressData->LASTNAME,
                        'street' => (string)$shippingAddressData->STREET,
                        'country' => (string)$shippingAddressData->COUNTRY,
                        'postcode' => (string)$shippingAddressData->ZIP,
                        'city' => (string)$shippingAddressData->CITY
                    ];

                    $_addressValuesBilling = [
                        'firstname' => (string)$billingAddressData->FIRSTNAME,
                        'lastname' => (string)$billingAddressData->LASTNAME,
                        'street' => (string)$billingAddressData->STREET,
                        'country' => (string)$billingAddressData->COUNTRY,
                        'postcode' => (string)$billingAddressData->ZIP,
                        'city' => (string)$billingAddressData->CITY,
                        'telephone' => (string)$billingAddressData->PHONE,
                        'email' => (string)$billingAddressData->PHONE,
                    ];
                    $this->orderORM->insertOrderAddress($orderId, $_addressValuesShipping, 'shipping');
                    $this->orderORM->insertOrderAddress($orderId, $_addressValuesBilling, 'billing');
                }
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function getOrderWeight($order)
    {
//        $items = $order->ITEMS->ITEM;
//        foreach ($items as $item) {
//            $productWeight = $this->productORM->getProductData((string)$item->ARTID);
//        }
        return 0.0000;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getShippingMethod($item)
    {
        return (string)$item->SHIPPING;
    }

    /**
     * @param $item
     * @param $reservedOrderId
     *
     * @return string
     */
    public function createQuote($item, $reservedOrderId)
    {
        $quoteData = $this->helper->extractValuesQuote($item, $reservedOrderId);
        return $this->orderORM->insertQuote($quoteData);
    }

    /**
     * @param $customerId
     * @param $shippingAddressData
     *
     * @return bool|string
     */
    public function createShippingAddress($customerId, $shippingAddressData)
    {
        if ($shippingAddressId = $this->addressORM->insertAddressFromOrder($customerId, $shippingAddressData)) {
            $_addressValues = [
                'firstname' => (string)$shippingAddressData->FIRSTNAME,
                'lastname' => (string)$shippingAddressData->LASTNAME,
                'street' => (string)$shippingAddressData->STREET,
                'country' => (string)$shippingAddressData->COUNTRY,
                'postcode' => (string)$shippingAddressData->ZIP,
                'city' => (string)$shippingAddressData->CITY
            ];
            $_addressFields = [
                'firstname' => [
                    'data_type' => 'varchar'
                ],
                'lastname' => [
                    'data_type' => 'varchar'
                ],
                'street' => [
                    'data_type' => 'text'
                ],
                'country' => [
                    'data_type' => 'varchar'
                ],
                'postcode' => [
                    'data_type' => 'varchar'
                ],
                'city' => [
                    'data_type' => 'varchar'
                ]
            ];
            $this->attributeORM->fillAttributesGeneric($_addressFields, $shippingAddressId, $_addressValues, 'customer_address_entity_', 2);
            return $shippingAddressId;
        } else {
            return false;
        }
    }

    /**
     * @param $customerId
     * @param $billingAddressData
     * @return bool|string
     */
    public function createBillingAddress($customerId, $billingAddressData)
    {
        if ($billingAddressId = $this->addressORM->insertAddressFromOrder($customerId, $billingAddressData)) {
            $_addressValues = [
                'firstname' => (string)$billingAddressData->FIRSTNAME,
                'lastname' => (string)$billingAddressData->LASTNAME,
                'street' => (string)$billingAddressData->STREET,
                'country' => (string)$billingAddressData->COUNTRY,
                'postcode' => (string)$billingAddressData->ZIP,
                'city' => (string)$billingAddressData->CITY,
                'telephone' => (string)$billingAddressData->PHONE
            ];
            $_addressFields = [
                'firstname' => [
                    'data_type' => 'varchar'
                ],
                'lastname' => [
                    'data_type' => 'varchar'
                ],
                'street' => [
                    'data_type' => 'text'
                ],
                'country' => [
                    'data_type' => 'varchar'
                ],
                'postcode' => [
                    'data_type' => 'varchar'
                ],
                'city' => [
                    'data_type' => 'varchar'
                ],
                'telephone' => [
                    'data_type' => 'varchar'
                ],
            ];
            $this->attributeORM->fillAttributesGeneric($_addressFields, $billingAddressId, $_addressValues, 'customer_address_entity_', 2);
            return $billingAddressId;
        } else {
            return false;
        }
    }

    public function handleOrders($xml)
    {
        foreach ($xml->ORDER as $item) {
            $this->createOrder($item);
        }
    }
}