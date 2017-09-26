<?php

/**
 * Class CategoryORM
 */
class CategoryORM extends ConnectionORM
{

    private $insertQuery = '
        INSERT INTO catalog_category_entity(
            entity_type_id, 
            attribute_set_id, 
            created_at, 
            updated_at, 
            path, 
            `position`, 
            `level`, 
            children_count
        ) 
        VALUES (
            :entity_type_id, 
            :attribute_set_id, 
            :created_at, 
            :updated_at, 
            :path, 
            :position, 
            :level, 
            :children_count
        ) ON DUPLICATE KEY UPDATE entity_id = entity_id';

    private $lastId;

    /**
     * @param $name
     *
     * @return bool|string
     */
    public function insertCategory($name)
    {
        if ($this->categoryExists($name)) {
            return false;
        }

        $stmt = $this->pdo->prepare($this->insertQuery);

        $stmt->execute([
            'entity_type_id' => 3,
            'attribute_set_id' => 3,
            'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
            'path' => '1/2/11',
            'position' => 0,
            'level' => 0,
            'children_count' => 0,
        ]);

        $this->lastId = $this->pdo->lastInsertId();

        $updateQuery = 'UPDATE catalog_category_entity SET path = concat(path, "/' . $this->lastId . '") WHERE entity_id = ' . $this->lastId;
        $stmt = $this->pdo->prepare($updateQuery);

        $stmt->execute();

        return $this->lastId;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function categoryExists($name)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM catalog_category_entity_varchar 
                                     WHERE `value` = :value 
                                     AND attribute_id = 41');

        $stmt->execute([
            'value' => $name
        ]);

        $results = $stmt->fetchAll();

        return (count($results) > 0);
    }
}