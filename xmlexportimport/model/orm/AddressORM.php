<?php

/**
 * Class AddressORM
 */
class AddressORM extends ConnectionORM
{

    /**
     * @param $entityCustomerId
     *
     * @return bool
     */
    public function customerHasAddress($entityCustomerId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customer_address_entity WHERE parent_id = :parent_id');

        $stmt->execute([
            'parent_id' => $entityCustomerId
        ]);

        $results = $stmt->fetchAll();

        return (count($results) > 0);
    }

    /**
     * @param $entityCustomerId
     * @param $addressData
     *
     * @return string
     */
    public function insertAddressFromOrder($entityCustomerId, $addressData)
    {
        $incrementId = (int)$addressData->CUSTOMER_ID;

        $data = [
            'entity_type_id' => 2,
            'attribute_set_id' => 0,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'is_active' => 1,
            'parent_id' => $entityCustomerId,
            'increment_id' => $incrementId,
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp)
        ];

        $this->pdo->prepare('INSERT INTO 
        customer_address_entity(entity_type_id, attribute_set_id, created_at, is_active, updated_at, parent_id, increment_id) 
        VALUES (:entity_type_id, :attribute_set_id, :created_at, :is_active, :updated_at, :parent_id, :increment_id)')
            ->execute($data);

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $entityCustomerId
     * @param $xmlItem
     *
     * @return string
     */
    public function insertAddress($entityCustomerId, $xmlItem)
    {
        if ($this->customerHasAddress($entityCustomerId)) {
            return false;
        }

        $incrementId = (int)$xmlItem->ID;

        $this->pdo->prepare('INSERT INTO 
        customer_address_entity(entity_type_id, attribute_set_id, created_at, is_active, updated_at, parent_id, increment_id) 
        VALUES (:entity_type_id, :attribute_set_id, :created_at, :is_active, :updated_at, :parent_id, :increment_id)')
            ->execute([
                'entity_type_id' => 2,
                'attribute_set_id' => 0,
                'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
                'is_active' => 1,
                'parent_id' => $entityCustomerId,
                'increment_id' => $incrementId,
                'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp)
            ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $addressId
     * @return bool|mixed
     */
    public function getBillingAddressDataCustomer($addressId)
    {
        $stmt = $this->pdo->prepare('SELECT parent_id FROM customer_address_entity WHERE entity_id = :entity_id');

        $stmt->execute([
            'entity_id' => $addressId
        ]);

        $result = $stmt->fetch();

        if(!isset($result['parent_id'])) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT * FROM customer_entity WHERE entity_id = :entity_id');

        $stmt->execute([
            'entity_id' => $result['parent_id']
        ]);

        $result = $stmt->fetch();

        return (!empty($result)) ? $result : false;
    }
}