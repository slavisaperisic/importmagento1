<?php

/**
 * Class OrderORM
 */
class OrderORM extends ConnectionORM
{
    private $insertQuery = '
        INSERT INTO sales_flat_order(
            `entity_id`,
            `state`,
            `status`,
            `protect_code`,
            `shipping_description`,
            `is_virtual`,
            `store_id`,
            `customer_id`, /*nullable*/
            `base_discount_amount`,
            `base_grand_total`,
            `base_shipping_amount`,
            `base_shipping_tax_amount`,
            `base_subtotal`,
            `base_tax_amount`,
            `base_to_global_rate`,
            `base_to_order_rate`,
            `discount_amount`,
            `grand_total`,
            `shipping_amount`,
            `shipping_tax_amount`,
            `store_to_base_rate`,
            `store_to_order_rate`,
            `subtotal`,
            `tax_amount`,
            `total_qty_ordered`,
            `customer_is_guest`,
            `customer_note_notify`,
            `billing_address_id`,
            `customer_group_id`,
            `email_sent`,
            `quote_id`,
            `shipping_address_id`,
            `base_shipping_discount_amount`,
            `base_subtotal_incl_tax`,
            `shipping_discount_amount`,
            `subtotal_incl_tax`,
            `weight`,
            `increment_id`,
            `base_currency_code`,
            `customer_email`,
            `customer_firstname`,
            `customer_lastname`,
            `global_currency_code`,
            `order_currency_code`,
            `remote_ip`,
            `shipping_method`,
            `store_currency_code`,
            `store_name`,
            `created_at`,
            `updated_at`,
            `total_item_count`,
            `hidden_tax_amount`,
            `base_hidden_tax_amount`,
            `shipping_hidden_tax_amount`,
            `base_shipping_hidden_tax_amnt`,
            `shipping_incl_tax`,
            `base_shipping_incl_tax`,
            `paypal_ipn_customer_notified`,
            `tracking`
        ) 
        VALUES (
            :entity_id,
            :state,
            :status,
            :protect_code,
            :shipping_description,
            :is_virtual,
            :store_id,
            :customer_id, /* nullable */
            :base_discount_amount,
            :base_grand_total,
            :base_shipping_amount,
            :base_shipping_tax_amount,
            :base_subtotal,
            :base_tax_amount,
            :base_to_global_rate,
            :base_to_order_rate,
            :discount_amount,
            :grand_total,
            :shipping_amount,
            :shipping_tax_amount,
            :store_to_base_rate,
            :store_to_order_rate,
            :subtotal,
            :tax_amount,
            :total_qty_ordered,
            :customer_is_guest,
            :customer_note_notify,
            :billing_address_id,
            :customer_group_id,
            :email_sent,
            :quote_id,
            :shipping_address_id,
            :base_shipping_discount_amount,
            :base_subtotal_incl_tax,
            :shipping_discount_amount,
            :subtotal_incl_tax,
            :weight,
            :increment_id,
            :base_currency_code,
            :customer_email,
            :customer_firstname,
            :customer_lastname,
            :global_currency_code,
            :order_currency_code,
            :remote_ip,
            :shipping_method,
            :store_currency_code,
            :store_name,
            :created_at,
            :updated_at,
            :total_item_count,
            :hidden_tax_amount,
            :base_hidden_tax_amount,
            :shipping_hidden_tax_amount,
            :base_shipping_hidden_tax_amnt,
            :shipping_incl_tax,
            :base_shipping_incl_tax,
            :paypal_ipn_customer_notified,
            :tracking
        ) ON DUPLICATE KEY UPDATE entity_id = entity_id';


    private $insertQuoteQuery = '
        INSERT INTO sales_flat_quote(
        `store_id`,
        `created_at`,
        `updated_at`,
        `is_active`,
        `is_virtual`,
        `is_multi_shipping`,
        `items_count`,
        `items_qty`,
        `orig_order_id`,
        `store_to_base_rate`,
        `store_to_quote_rate`,
        `base_currency_code`,
        `store_currency_code`,
        `quote_currency_code`,
        `grand_total`,
        `base_grand_total`,
        `checkout_method`,
        `customer_id`,
        `customer_tax_class_id`,
        `customer_group_id`,
        `customer_email`,
        `customer_firstname`,
        `customer_lastname`,
        `customer_note_notify`,
        `customer_is_guest`,
        `remote_ip`,
        `reserved_order_id`,
        `global_currency_code`,
        `base_to_global_rate`,
        `base_to_quote_rate`,
        `subtotal`,
        `base_subtotal`,
        `subtotal_with_discount`,
        `base_subtotal_with_discount`,
        `is_changed`,
        `trigger_recollect`,
        `is_persistent`
        ) 
        VALUES (
        :store_id,
        :created_at,
        :updated_at,
        :is_active,
        :is_virtual,
        :is_multi_shipping,
        :items_count,
        :items_qty,
        :orig_order_id,
        :store_to_base_rate,
        :store_to_quote_rate,
        :base_currency_code,
        :store_currency_code,
        :quote_currency_code,
        :grand_total,
        :base_grand_total,
        :checkout_method,
        :customer_id,
        :customer_tax_class_id,
        :customer_group_id,
        :customer_email,
        :customer_firstname,
        :customer_lastname,
        :customer_note_notify,
        :customer_is_guest,
        :remote_ip,
        :reserved_order_id,
        :global_currency_code,
        :base_to_global_rate,
        :base_to_quote_rate,
        :subtotal,
        :base_subtotal,
        :subtotal_with_discount,
        :base_subtotal_with_discount,
        :is_changed,
        :trigger_recollect,
        :is_persistent
        ) ON DUPLICATE KEY UPDATE base_subtotal = base_subtotal';

    private $insertOrderGridQuery = '
        INSERT INTO sales_flat_order_grid(
            `entity_id`,
            `status`,
            `store_id`,
            `store_name`,
            `customer_id`,
            `base_grand_total`,
            `grand_total`,
            `increment_id`,
            `base_currency_code`,
            `order_currency_code`,
            `shipping_name`,
            `billing_name`,
            `created_at`,
            `updated_at`
        ) 
        VALUES (
            :entity_id,
            :status,
            :store_id,
            :store_name,
            :customer_id,
            :base_grand_total,
            :grand_total,
            :increment_id,
            :base_currency_code,
            :order_currency_code,
            :shipping_name,
            :billing_name,
            :created_at,
            :updated_at
        ) ON DUPLICATE KEY UPDATE entity_id = entity_id';

    private $lastId;

    /**
     * @param $order
     *
     * @return bool|string
     */
    public function insertOrder(
        $order
    )
    {
        if ($this->orderExists($order)) {
            return false;
        }

        $stmt = $this->pdo->prepare($this->insertQuery);

        $stmt->execute([
            'entity_id' => $order['entity_id'],
            'state' => 'new',
            'status' => 'pending',
            'protect_code' => 'b91639',
            'shipping_description' => $order['shipping_description'],
            'is_virtual' => 0,
            'store_id' => 1,
            'customer_id' => $order['customer_id'],
            'base_discount_amount' => 0.0000,
            'base_grand_total' => $order['base_grand_total'],
            'base_shipping_amount' => $order['base_shipping_amount'],
            'base_shipping_tax_amount' => 0.0000,
            'base_subtotal' => $order['base_subtotal'],
            'base_tax_amount' => 0.0000,
            'base_to_global_rate' => 1.0000,
            'base_to_order_rate' => 1.0000,
            'discount_amount' => 0.0000,
            'grand_total' => $order['grand_total'],
            'shipping_amount' => $order['shipping_amount'],
            'shipping_tax_amount' => 0.0000,
            'store_to_base_rate' => 1.0000,
            'store_to_order_rate' => 1.0000,
            'subtotal' => $order['subtotal'],
            'tax_amount' => 0.0000,
            'total_qty_ordered' => $order['total_qty_ordered'],
            'customer_is_guest' => 0,
            'customer_note_notify' => 1,
            'billing_address_id' => $order['billing_address_id'],
            'customer_group_id' => $order['customer_group_id'],
            'email_sent' => 1,
            'quote_id' => $order['quote_id'],
            'shipping_address_id' => $order['shipping_address_id'],
            'base_shipping_discount_amount' => 0.0000,
            'base_subtotal_incl_tax' => $order['base_subtotal_incl_tax'],
            'shipping_discount_amount' => 0.0000,
            'subtotal_incl_tax' => $order['subtotal_incl_tax'],
            'weight' => $order['weight'],
            'increment_id' => $order['increment_id'],
            'base_currency_code' => 'EUR',
            'customer_email' => $order['customer_email'],
            'customer_firstname' => $order['customer_firstname'],
            'customer_lastname' => $order['customer_lastname'],
            'global_currency_code' => 'EUR',
            'order_currency_code' => 'EUR',
            'remote_ip' => '0.0.0.0',
            'shipping_method' => $order['shipping_method'],
            'store_currency_code' => 'EUR',
            'store_name' => 'Main Website ' . "\n" . 'Main Website Store' . "\n" . 'Default Store View',
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'total_item_count' => $order['total_item_count'],
            'hidden_tax_amount' => 0.0000,
            'base_hidden_tax_amount' => 0.0000,
            'shipping_hidden_tax_amount' => 0.0000,
            'base_shipping_hidden_tax_amnt' => 0.0000,
            'shipping_incl_tax' => $order['shipping_incl_tax'],
            'base_shipping_incl_tax' => $order['base_shipping_incl_tax'],
            'paypal_ipn_customer_notified' => 0,
            'tracking' => 0
        ]);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function orderExists($order)
    {
        $entity_id = $order['entity_id'];
        $stmt = $this->pdo->prepare('SELECT * FROM sales_flat_order
                                     WHERE entity_id = :entity_id');

        $stmt->execute([
            'entity_id' => $entity_id
        ]);

        $results = $stmt->fetchAll();

        return (count($results) > 0);
    }

    /**
     * @param $quote
     *
     * @return string
     */
    public function insertQuote($quote)
    {
        $stmt = $this->pdo->prepare($this->insertQuoteQuery);

        $data = [
            'store_id' => 1,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'is_active' => 0,
            'is_virtual' => 0,
            'is_multi_shipping' => 0,
            'items_count' => $quote['items_count'],
            'items_qty' => 0.0000,
            'orig_order_id' => 0,
            'store_to_base_rate' => 1.0000,
            'store_to_quote_rate' => 1.0000,
            'base_currency_code' => 'EUR',
            'store_currency_code' => 'EUR',
            'quote_currency_code' => 'EUR',
            'grand_total' => $quote['grand_total'],
            'base_grand_total' => $quote['base_grand_total'],
            'checkout_method' => $quote['guest'],
            'customer_id' => 0,
            'customer_tax_class_id' => 3,
            'customer_group_id' => 0,
            'customer_email' => $quote['customer_email'],
            'customer_firstname' => $quote['customer_firstname'],
            'customer_lastname' => $quote['customer_lastname'],
            'customer_note_notify' => 1,
            'customer_is_guest' => 1,
            'remote_ip' => '1.1.1.1',
            'reserved_order_id' => $quote['reserved_order_id'],
            'global_currency_code' => 'EUR',
            'base_to_global_rate' => 1.0000,
            'base_to_quote_rate' => 1.0000,
            'subtotal' => $quote['subtotal'],
            'base_subtotal' => $quote['base_subtotal'],
            'subtotal_with_discount' => $quote['subtotal_with_discount'],
            'base_subtotal_with_discount' => $quote['base_subtotal_with_discount'],
            'is_changed' => 1,
            'trigger_recollect' => 0,
            'is_persistent' => 0
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    public function insertOrderGrid($orderGrid)
    {
        $stmt = $this->pdo->prepare($this->insertOrderGridQuery);

        $data = [
            'entity_id' => $orderGrid['entity_id'],
            'status' => 'pending',
            'store_id' => 1,
            'store_name' => 'Main Website ' . "\n" . 'Main Website Store' . "\n" . 'Default Store View',
            'customer_id' => $orderGrid['customer_id'],
            'base_grand_total' => $orderGrid['base_grand_total'],
            'grand_total' => $orderGrid['grand_total'],
            'increment_id' => $orderGrid['increment_id'],
            'base_currency_code' => 'EUR',
            'order_currency_code' => 'EUR',
            'shipping_name' => $orderGrid['shipping_name'],
            'billing_name' => $orderGrid['billing_name'],
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    /**
     * @return int
     */
    public function getReservedOrderId()
    {
        $stmt = $this->pdo->prepare('SELECT `increment_id` FROM sales_flat_order ORDER BY `increment_id` DESC LIMIT 1');

        $stmt->execute();

        $result = $stmt->fetch();

        return (isset($result['increment_id'])) ? ($result['increment_id'] + 1) : 100000001;
    }

    private $insertOrderItemQuery = '
        INSERT INTO sales_flat_order_item(
            `order_id`,
            `quote_item_id`,
            `store_id`,
            `created_at`,
            `updated_at`,
            `product_id`,
            `product_type`,
            `product_options`,
            `weight`,
            `is_virtual`,
            `sku`,
            `name`,
            `free_shipping`,
            `is_qty_decimal`,
            `no_discount`,
            `qty_ordered`,
            `price`,
            `base_price`,
            `original_price`,
            `base_original_price`,
            `row_total`,
            `base_row_total`,
            `price_incl_tax`,
            `base_price_incl_tax`,
            `row_total_incl_tax`,
            `base_row_total_incl_tax`,
            `is_nominal`
        ) 
        VALUES (
            :order_id,
            :quote_item_id,
            :store_id,
            :created_at,
            :updated_at,
            :product_id,
            :product_type,
            :product_options,
            :weight,
            :is_virtual,
            :sku,
            :name,
            :free_shipping,
            :is_qty_decimal,
            :no_discount,
            :qty_ordered,
            :price,
            :base_price,
            :original_price,
            :base_original_price,
            :row_total,
            :base_row_total,
            :price_incl_tax,
            :base_price_incl_tax,
            :row_total_incl_tax,
            :base_row_total_incl_tax,
            :is_nominal
        ) ON DUPLICATE KEY UPDATE order_id = order_id';

    public function insertOrderItem($orderItem)
    {
        $stmt = $this->pdo->prepare($this->insertOrderItemQuery);

        $data = [
            'order_id' => $orderItem['order_id'],
            'quote_item_id' => $orderItem['quote_item_id'],
            'store_id' => 1,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'product_id' => $orderItem['product_id'],
            'product_type' => 'simple',
            'product_options' => '',
            'weight' => $orderItem['weight'],
            'is_virtual' => 0,
            'sku' => $orderItem['sku'],
            'name' => $orderItem['name'],
            'free_shipping' => 0,
            'is_qty_decimal' => 0,
            'no_discount' => 0,
            'qty_ordered' => 1.0000,
            'price' => $orderItem['price'],
            'base_price' => $orderItem['price'],
            'original_price' => $orderItem['price'],
            'base_original_price' => $orderItem['price'],
            'row_total' => $orderItem['price'],
            'base_row_total' => $orderItem['price'],
            'price_incl_tax' => $orderItem['price'],
            'base_price_incl_tax' => $orderItem['price'],
            'row_total_incl_tax' => $orderItem['price'],
            'base_row_total_incl_tax' => $orderItem['price'],
            'is_nominal' => 0
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    private $insertQuoteItemQuery = '
        INSERT INTO sales_flat_quote_item(
            `quote_id`,
            `store_id`,
            `created_at`,
            `updated_at`,
            `product_id`,
            `product_type`,
            `weight`,
            `is_virtual`,
            `sku`,
            `name`,
            `free_shipping`,
            `is_qty_decimal`,
            `no_discount`,
            `price`,
            `base_price`,
            `row_total`,
            `base_row_total`,
            `price_incl_tax`,
            `base_price_incl_tax`,
            `row_total_incl_tax`,
            `base_row_total_incl_tax`
        ) 
        VALUES (
            :quote_id,
            :store_id,
            :created_at,
            :updated_at,
            :product_id,
            :product_type,
            :weight,
            :is_virtual,
            :sku,
            :name,
            :free_shipping,
            :is_qty_decimal,
            :no_discount,
            :price,
            :base_price,
            :row_total,
            :base_row_total,
            :price_incl_tax,
            :base_price_incl_tax,
            :row_total_incl_tax,
            :base_row_total_incl_tax
        ) ON DUPLICATE KEY UPDATE quote_id = quote_id';

    /**
     * @param $quoteItem
     *
     * @return string
     */
    public function insertQuoteItem($quoteItem)
    {
        $stmt = $this->pdo->prepare($this->insertQuoteItemQuery);


        $data = [
            'quote_id' => $quoteItem['quote_id'],
            'store_id' => 1,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'product_id' => $quoteItem['product_id'],
            'product_type' => 'simple',
            'weight' => $quoteItem['weight'],
            'is_virtual' => 0,
            'sku' => $quoteItem['sku'],
            'name' => $quoteItem['name'],
            'free_shipping' => 0,
            'is_qty_decimal' => 0,
            'no_discount' => 0,
            'price' => $quoteItem['price'],
            'base_price' => $quoteItem['price'],
            'row_total' => $quoteItem['price'],
            'base_row_total' => $quoteItem['price'],
            'price_incl_tax' => $quoteItem['price'],
            'base_price_incl_tax' => $quoteItem['price'],
            'row_total_incl_tax' => $quoteItem['price'],
            'base_row_total_incl_tax' => $quoteItem['price']
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    private $insertOrderPaymentQuery = '
        INSERT INTO sales_flat_order_payment(
            `parent_id`,
            `method`
        ) 
        VALUES (
            :parent_id,
            :method
        ) ON DUPLICATE KEY UPDATE parent_id = parent_id';

    /**
     * @param $orderItem
     * @return string
     */
    public function insertOrderPayment($orderItem)
    {
        $stmt = $this->pdo->prepare($this->insertOrderPaymentQuery);

        $data = [
            'parent_id' => $orderItem['entity_id'],
            'method' => 'banktransfer',
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    private $insertOrderAddressQuery = '
        INSERT INTO sales_flat_order_address(
            `parent_id`,
            `postcode`,
            `lastname`,
            `street`,
            `city`,
            `email`,
            `telephone`,
            `country_id`,
            `firstname`,
            `address_type`
        ) 
        VALUES (
            :parent_id,
            :postcode,
            :lastname,
            :street,
            :city,
            :email,
            :telephone,
            :country_id,
            :firstname,
            :address_type
        ) ON DUPLICATE KEY UPDATE parent_id = parent_id';

    /**
     * @param $orderId
     * @param $shippingAddressData
     * @param $type
     * @return string
     */
    public function insertOrderAddress($orderId, $shippingAddressData, $type)
    {
        $stmt = $this->pdo->prepare($this->insertOrderAddressQuery);

        $data = [
            'parent_id' => $orderId,
            'postcode' => $shippingAddressData['postcode'],
            'lastname' => $shippingAddressData['lastname'],
            'street' => $shippingAddressData['street'],
            'city' => $shippingAddressData['city'],
            'email' => $shippingAddressData['email'],
            'telephone' => $shippingAddressData['telephone'],
            'country_id' => $shippingAddressData['country'],
            'firstname' => $shippingAddressData['firstname'],
            'address_type' => $type
        ];

        $stmt->execute($data);

        $this->lastId = $this->pdo->lastInsertId();

        return $this->lastId;
    }

    /**
     * @return array|bool
     */
    public function getOrders()
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sales_flat_order');

        $stmt->execute();

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }

    /**
     * @param $entityId
     *
     * @return array|bool
     */
    public function getPaymentData($entityId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sales_flat_order_payment WHERE parent_id = :parent_id");

        $stmt->execute(['parent_id' => $entityId]);

        $results = $stmt->fetch();

        return (!empty($results)) ? $results : false;
    }

    /**
     * @param $entityId
     * @return array|bool
     */
    public function getOrderItems($entityId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sales_flat_order_item WHERE order_id = :order_id");

        $stmt->execute(['order_id' => $entityId]);

        $results = $stmt->fetchAll();

        if (empty($results)) {
            return false;
        }

        $items = [];
        foreach ($results as $result) {
            $items[] = [
                'ITEM' => [
                    'QTY' => $result['qty_ordered'],
                    'ARTID' => $this->getAttributeValueByCode('artid', $result['product_id'], 4, 'varchar', 'catalog_product_entity_'),
                    'EAN' => $this->getAttributeValueByCode('ean', $result['product_id'], 4, 'varchar', 'catalog_product_entity_'),
                    'PRICE' => $result['price'],
                ]
            ];
        }
        return $items;
    }

    /**
     * @param $code
     * @param $entityId
     * @param $entityTypeId
     * @param $dataType
     * @param $table
     * @return string
     */
    public function getAttributeValueByCode($code, $entityId, $entityTypeId, $dataType, $table)
    {
        $attrId = $this->selectAttributeIdByCode($code, $entityTypeId);
        if (trim($attrId) == '') {
            return '';
        }

        $sql = "SELECT * FROM " . $table . "$dataType 
                 WHERE entity_id = :entity_id
                 AND entity_type_id = :entity_type_id
                 AND attribute_id = :attribute_id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'entity_id' => $entityId,
            'entity_type_id' => $entityTypeId,
            'attribute_id' => $attrId,
        ]);

        $result = $stmt->fetch();

        return isset($result['value']) ? $result['value'] : '';
    }

    /**
     * @param $attributeCode
     * @param $entityTypeId
     *
     * @return mixed
     */
    public function selectAttributeIdByCode($attributeCode, $entityTypeId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM eav_attribute 
                                     WHERE attribute_code = :attribute_code
                                     AND entity_type_id = :entity_type_id');

        $stmt->execute([
            'attribute_code' => $attributeCode,
            'entity_type_id' => $entityTypeId,
        ]);

        $results = $stmt->fetch();

        return isset($results['attribute_id']) ? $results['attribute_id'] : '';
    }
}