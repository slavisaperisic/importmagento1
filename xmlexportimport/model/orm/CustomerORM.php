<?php

/**
 * Class CustomerORM
 */
class CustomerORM extends ConnectionORM
{
    /**
     * @param $xmlItem
     *
     * @return mixed
     */
    public function insertCustomer($xmlItem)
    {
        $entityId = (int)$xmlItem->NUMMER;
        $email = (string)$xmlItem->CONTACT->EMAIL;
        $groupId = $this->getGroupId((string)$xmlItem->LINO);
        $incrementId = (int)$xmlItem->ID;

        $stmt = $this->pdo->prepare('
        INSERT INTO customer_entity(
        entity_id, 
        entity_type_id, 
        attribute_set_id, 
        website_id, 
        email, 
        group_id, 
        increment_id, 
        store_id, 
        created_at, 
        is_active, 
        updated_at) 
        VALUES (
        :entity_id,
        :entity_type_id, 
        :attribute_set_id, 
        :website_id, 
        :email, 
        :group_id, 
        :increment_id, 
        :store_id, 
        :created_at, 
        :is_active, 
        :updated_at)');

        $data = [
            'entity_id' => $entityId,
            'entity_type_id' => 1,
            'attribute_set_id' => 0,
            'website_id' => 1,
            'email' => $email,
            'group_id' => $groupId,
            'increment_id' => $incrementId,
            'store_id' => 1,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp)
        ];

        try {
            $stmt->execute($data);
            return $entityId;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @param $orderItem
     *
     * @return mixed
     */
    public function insertCustomerFromOrder($orderItem)
    {
        $entityId = (int)$orderItem->ADDRESS->CUSTOMER_NO;
        $email = (string)$orderItem->ADDRESS->EMAIL;
        $incrementId = (int)$orderItem->ADDRESS->CUSTOMER_ID;

        $stmt = $this->pdo->prepare('
        INSERT INTO customer_entity(
        entity_id, 
        entity_type_id, 
        attribute_set_id, 
        website_id, 
        email, 
        group_id, 
        increment_id, 
        store_id, 
        created_at, 
        is_active, 
        updated_at) 
        VALUES (
        :entity_id,
        :entity_type_id, 
        :attribute_set_id, 
        :website_id, 
        :email, 
        :group_id, 
        :increment_id, 
        :store_id, 
        :created_at, 
        :is_active, 
        :updated_at) ON DUPLICATE KEY UPDATE entity_id = entity_id');

        $data = [
            'entity_id' => $entityId,
            'entity_type_id' => 1,
            'attribute_set_id' => 0,
            'website_id' => 1,
            'email' => $email,
            'group_id' => 0,
            'increment_id' => $incrementId,
            'store_id' => 1,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp)
        ];

        $stmt->execute($data);

        return $entityId;
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    public function replaceSpecials($str)
    {
        $search = explode(",", "ä,Ä,ö,Ö,ü,Ü,ß,²,³,µ,Ø,ø,À,Á,Â,È,É,Ê,Ì,Í,Î,Ñ,Ò,Ó,Ô,Ù,Ú,Û,Ý,à,á,â,è,é,ê,ì,í,î,ò,ó,ô,ù,ú,û,ý,´,`,^,ˆ,€");
        $replace = explode(",", "&#228;,&#196;,&#246;,&#214;,&#252;,&#220;,&#223;,&#178;,&#179;,&#181;,&#216;,&#248;,&#192;,&#193;,&#194;,&#200;,&#201;,&#202;,&#204;,&#205;,&#206;,&#209;,&#210;,&#211;,&#212;,&#217;,&#218;,&#219;,&#221;,&#224;,&#225;,&#226;,&#232;,&#233;,&#234;,&#236;,&#237;,&#238;,&#242;,&#243;,&#244;,&#249;,&#250;,&#251;,&#253;,&#180;,&#96;,&#94;,&#136;,&#8364;");

        return str_replace($search, $replace, $str);
    }

    /**
     * @param $groupName
     *
     * @return mixed
     */
    public function insertCustomerGroup($groupName)
    {
        if ($this->customerGroupExists($groupName)) {
            return false;
        }

        $stmt = $this->pdo->prepare('
        INSERT INTO customer_group(
            customer_group_code,
            tax_class_id
        ) 
        VALUES (
            :customer_group_code,
            :tax_class_id
        )');

        $data = [
            'customer_group_code' => $groupName,
            'tax_class_id' => 3
        ];

        $stmt->execute($data);

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $group
     * @return bool | int
     */
    public function getGroupId($group)
    {
        $group = substr($group, -1);
        $stmt = $this->pdo->prepare('SELECT customer_group_id FROM customer_group WHERE `customer_group_code` = :customer_group_code');

        $stmt->execute([
            ':customer_group_code' => $group
        ]);

        $result = $stmt->fetch();

        return (isset($result['customer_group_id'])) ? $result['customer_group_id'] : false;
    }

    /**
     * todo complete
     * @return string
     */
    public function getCustomerGroupId()
    {
        return 0;
    }

    /**
     * @param $groupId
     *
     * @return array|bool
     */
    public function getCustomersByGroupId($groupId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customer_entity WHERE `group_id` = :customer_group_id');

        $stmt->execute([
            ':customer_group_id' => $groupId
        ]);

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }

    /**
     * @param $groupName
     *
     * @return bool
     */
    public function customerGroupExists($groupName)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customer_group WHERE `customer_group_code` = :groupName');

        $stmt->execute([
            'groupName' => $groupName
        ]);

        $results = $stmt->fetchAll();

        return !empty($results);
    }

    /**
     * @return array|bool
     */
    public function getAllCustomers()
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customer_entity');

        $stmt->execute();

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }
}