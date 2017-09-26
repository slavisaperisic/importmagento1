<?php

/**
 * Class PriceORM
 */
class PriceORM extends ConnectionORM
{

    private $insertPriceQuery = '
        INSERT INTO customerprices_prices(
            customer_id, 
            product_id, 
            store_id, 
            qty, 
            price, 
            special_price, 
            created_at, 
            customer_email, 
            discount
        ) 
        VALUES (
            :customer_id, 
            :product_id, 
            :store_id, 
            :qty, 
            :price, 
            :special_price, 
            :created_at, 
            :customer_email, 
            :discount
        ) ON DUPLICATE KEY UPDATE price = price';

    /**
     * @param $data
     * @param $customer
     * @param $productId
     *
     * @return string
     */
    public function insertCustomerPrice($data, $customer, $productId)
    {
        $stmt = $this->pdo->prepare($this->insertPriceQuery);

        try {
            $stmt->execute([
                'customer_id' => $customer['entity_id'],
                'product_id' => $productId,
                'store_id' => 0,
                'qty' => 1,
                'price' => $data['price'],
                'special_price' => $data['special_price'],
                'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
                'customer_email' => $customer['email'],
                'discount' => $data['discount']
            ]);

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        return $this->pdo->lastInsertId();
    }

    private $insertGroupPriceQuery = '
        INSERT INTO catalog_product_entity_group_price(
            entity_id, 
            customer_group_id, 
            value, 
            website_id,
            all_groups
        ) 
        VALUES (
            :entity_id, 
            :customer_group_id, 
            :value, 
            :website_id,
            0
        ) ON DUPLICATE KEY UPDATE value = value';

    /**
     * @param $data
     * @param $groupId
     * @param $productId
     *
     * @return string
     */
    public function insertGroupPrice($data, $groupId, $productId)
    {
        $stmt = $this->pdo->prepare($this->insertGroupPriceQuery);

        try {
            $stmt->execute([
                'entity_id' => $productId,
                'customer_group_id' => $groupId,
                'value' => $data['price'],
                'website_id' => '0'
            ]);

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $data
     * @param $groupId
     * @param $productId
     */
    public function insertFromPrice($data, $groupId, $productId)
    {
        $from = (string)$data->FROM;
        $fromQuery = '
        INSERT INTO price_from(
            group_id, 
            product_id, 
            value
        ) 
        VALUES (
            :group_id, 
            :product_id, 
            :value
        ) ON DUPLICATE KEY UPDATE value = value';

        $stmt = $this->pdo->prepare($fromQuery);

        try {
            $stmt->execute([
                'group_id' => $groupId,
                'product_id' => $productId,
                'value' => $from
            ]);

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    /**
     * @param $groupId
     * @param $productId
     *
     * @return bool
     */
    public function getFromValue($groupId, $productId)
    {
        $query = "SELECT `value` FROM price_from WHERE group_id = $groupId AND product_id = $productId";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        $result = $stmt->fetch();
        return (!empty($result)) ? $result['value'] : false;
    }

    /**
     * @param $groupId
     * @param $productId
     *
     * @return array|bool
     */
    public function getPriceByGroupAndProductId($groupId, $productId)
    {
        $query = "SELECT `value` FROM catalog_product_entity_group_price WHERE customer_group_id = $groupId AND entity_id = $productId";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        $result = $stmt->fetch();
        return (!empty($result)) ? $result['value'] : false;
    }

    public function getAllGroups()
    {
        $query = "SELECT customer_group_id FROM customer_group";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }

    public function getAllProducts()
    {
        $query = "SELECT * FROM catalog_product_entity";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }


    public function getAll()
    {
        if (!$groups = $this->getAllGroups()) {
            return false;
        }

        if (!$products = $this->getAllProducts()) {
            return false;
        }

        $priceLists = [];
        foreach ($groups as $priceList) {
            $priceListItems = [];
            foreach ($products as $product) {
                $priceListItems[] = [
                    'product' => $product,
                    'customer_group_id' => $priceList['customer_group_id']
                ];
            }
            $priceLists[] = $priceListItems;
        }

        return $priceLists;
    }
}