<?php

/**
 * Class ConnectionORM
 */
class AttributeORM extends ConnectionORM
{
    /**
     * @param $entityId
     * @param $attributeCode
     * @param $value
     * @param $dataType
     * @param $table
     * @param $entityTypeId
     *
     * @return bool
     */
    public function insertAttributeValueGeneric($entityId, $attributeCode, $value, $dataType, $table, $entityTypeId)
    {
        $attributeId = $this->selectAttributeIdByCode($attributeCode, $entityTypeId);

        if (is_null($attributeId) || $attributeId == '') {
            return false;
        }

        if (trim($value) == '') {
            echo 'skipped empty value for ' . $attributeCode . "\n";
            return false;
        }

        $query = "INSERT INTO " . $table . "$dataType (entity_id, entity_type_id, attribute_id, `value`)
                  VALUES (:entity_id, :entity_type_id, :attribute_id, :value)
                  ON DUPLICATE KEY UPDATE `value` = :newValue";
        $data = [
            'entity_type_id' => $entityTypeId,
            'entity_id' => $entityId,
            'attribute_id' => $attributeId,
            'value' => $value,
            'newValue' => $value,
        ];

        try {
            $exec = $this->pdo->prepare($query);
            $exec->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            print_r($data);
            print_r($entityId);
            echo "\n";
            echo "\n";
            return false;
        }

        return true;
    }

    /**
     * @param $value
     * @param $entityId
     * @param $table
     * @param $dataType
     *
     * @return bool
     */
    public function attributeValueForEntityExists($value, $entityId, $table, $dataType)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $table . "$dataType 
                                     WHERE `value` = :value AND `entity_id` = :entity_id");

        $stmt->execute([
            'value' => $value,
            'entity_id' => $entityId,
        ]);

        $results = $stmt->fetch();

        return count($results) > 0;
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

    /**
     * @param $fields
     * @param $newEntityId
     * @param $xmlValues
     * @param $table
     * @param $entityTypeId
     */
    public function fillAttributesGeneric($fields, $newEntityId, $xmlValues, $table, $entityTypeId)
    {
        foreach ($fields as $key => $value) {
            $this->insertAttributeValueGeneric(
                $newEntityId,
                $key,
                $xmlValues[$key],
                $value['data_type'],
                $table,
                $entityTypeId
            );
        }
    }

    /**
     * @param $entityId
     *
     * @return string
     */
    public function getAddressIdByCustomerId($entityId)
    {
        $sql = "SELECT entity_id FROM customer_address_entity where parent_id = :parent_id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'parent_id' => $entityId
        ]);

        $result = $stmt->fetch();

        return isset($result['entity_id']) ? $result['entity_id'] : '';
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
}