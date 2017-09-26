<?php

/**
 * Class ProductORM
 */
class ProductORM extends ConnectionORM
{
    private $insertProductQuery = '
        INSERT INTO catalog_product_entity(
            entity_type_id, 
            attribute_set_id, 
            created_at, 
            updated_at, 
            type_id, 
            has_options, 
            required_options, 
            sku
        ) 
        VALUES (
            :entity_type_id, 
            :attribute_set_id, 
            :created_at, 
            :updated_at, 
            :type_id, 
            :has_options, 
            :required_options, 
            :sku
        ) ON DUPLICATE KEY UPDATE sku = sku';

    private $insertProductCategoryQuery = '
        INSERT INTO catalog_category_product(
            category_id,
            product_id,
            `position`
        ) 
        VALUES (
            :category_id, 
            :product_id,
            :position
        ) ON DUPLICATE KEY UPDATE position = position';

    private $insertProductFileQuery = '
        INSERT INTO uni_fileuploader(
            title,
            uploaded_file,
            file_content,
            product_ids
        ) 
        VALUES (
            :title,
            :uploaded_file,
            :file_content,
            :product_ids
        ) ON DUPLICATE KEY UPDATE title = title';

    /**
     * @param $data
     *
     * @return bool
     */
    public function insertProduct($data)
    {
        if ($this->productExists($data['sku'])) {
            return false;
        }

        $stmt = $this->pdo->prepare($this->insertProductQuery);

        try {
            $stmt->execute([
                'entity_type_id' => 4,
                'attribute_set_id' => 9,
                'created_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
                'updated_at' => date('Y-m-d H:i:s', $this->dateTimestamp),
                'type_id' => 'simple',
                'has_options' => 0,
                'required_options' => 0,
                'sku' => $data['sku']
            ]);

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $data
     * @param $productId
     */
    public function insertProductCategories($data, $productId)
    {
        $stmt = $this->pdo->prepare($this->insertProductCategoryQuery);

        $categories = $data['categories'];

        foreach ($categories as $category) {
            if ($categoryId = $this->getCategoryId($category)) {
                $stmt->execute([
                    'category_id' => $categoryId,
                    'product_id' => $productId,
                    'position' => 1
                ]);
            }
        }

        $stmt->execute([
            'category_id' => 2,
            'product_id' => $productId,
            'position' => 0
        ]);

        $stmt->execute([
            'category_id' => 11,
            'product_id' => $productId,
            'position' => 0
        ]);
    }

    /**
     * @param $data
     * @param $productId
     * @param $config
     * @param $xmlFtpConnection
     */
    public function insertProductFiles($data, $productId, $config, $xmlFtpConnection, $allFiles)
    {
        $files = $data['files'];

        foreach ($files->FILE as $file) {
            $filePath = (string)$file->PATH;
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);

            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                $this->getImage($config, $productId, $filePath, $xmlFtpConnection, $allFiles);
            } else {
                $this->getFile($config, $productId, $filePath, $xmlFtpConnection, $allFiles, (string)$file->HEADER);
            }
        }
    }

    /**
     * @param $config
     * @param $productId
     * @param $filePath
     * @param FTP $xmlFtpConnection
     * @param $allFiles
     * @param $fileHeader
     *
     * @return bool
     */
    public function getFile($config, $productId, $filePath, FTP $xmlFtpConnection, $allFiles, $fileHeader)
    {
        if (strstr($filePath, '/') !== false) {
            $split = explode('/', $filePath, 2);
            $filePath = $split[1];

            if (strstr($filePath, '/') !== false) {
                $split = explode('/', $filePath, 2);
                $folderName = $split[0];
                $filePath = $folderName . DIRECTORY_SEPARATOR . $split[1];
            }
        }

        if (!$filePath = $this->findMatch($filePath, $allFiles)) {
            return false;
        }

        $localFileName = $this->getFileName($filePath, $config['ftp_server_local_dir_files_files']);

        $stmt = $this->pdo->prepare($this->insertProductFileQuery);

        $stmt->execute([
            'title' => $fileHeader,
            'uploaded_file' => 'custom/upload/' . $filePath,
            'file_content' => '',
            'product_ids' => $productId,
        ]);

        /** @var $ftpConnection FTP */
        $xmlFtpConnection->downloadFile($localFileName, $filePath, true, $config['ftp_server_local_dir_files_files']);

        return true;
    }

    /**
     * @param $config
     * @param $productId
     * @param $filePath
     * @param FTP $xmlFtpConnection
     *
     * @return bool
     */
    public function getImage($config, $productId, $filePath, FTP $xmlFtpConnection, $allFiles)
    {
        $split = explode('/', $filePath, 2);
        $filePath = $split[1];

        if (!$filePath = $this->findMatch($filePath, $allFiles)) {
            return false;
        }

        $localFileName = $this->getImageNameForDb($filePath, true, $config['ftp_server_local_dir_files']);

        /** @var $ftpConnection FTP */
        if ($xmlFtpConnection->downloadFile($localFileName, $filePath)) {
        }
        $stmt = $this->pdo->prepare("INSERT INTO catalog_product_entity_media_gallery 
(attribute_id, entity_id, `value`) 
VALUES (88, :productId, :value)");

        $stmt->execute([
            'productId' => $productId,
            'value' => $localFileName
        ]);

        return true;
    }

    /**
     * @param $filePath
     * @param $localURI
     * @param null $folderName
     *
     * @return mixed
     */
    public function getFileName($filePath, $localURI, $folderName = null)
    {
        if (strstr($filePath, '/') !== false) {
            $split = explode('/', $filePath, 2);

            if (!file_exists($localURI . '/' . $split[0])) {
                mkdir($localURI . '/' . $split[0]);
            }
        }

        return $filePath;
    }


    /**
     * @param $image
     * @param bool $trimFirst
     * @param $localURI
     *
     * @return string
     */
    public function getImageNameForDb($image, $trimFirst = false, $localURI)
    {
        if (!in_array($image[0], ['.'])) {
            $firstChar = $image[0];
        } else {
            $firstChar = '_';
        }

        if (!file_exists($localURI . '/' . $firstChar)) {
            mkdir($localURI . '/' . $firstChar);
        }

        if (!in_array($image[1], ['.'])) {
            $secondChar = $image[1];
        } else {
            $secondChar = '_';
        }

        if (!file_exists($localURI . '/' . $firstChar . '/' . $secondChar)) {
            mkdir($localURI . '/' . $firstChar . '/' . $secondChar);
        }

        if ($trimFirst) {
            return $firstChar .
                DIRECTORY_SEPARATOR . $secondChar .
                DIRECTORY_SEPARATOR . $image;
        } else {
            return DIRECTORY_SEPARATOR . $firstChar .
                DIRECTORY_SEPARATOR . $secondChar .
                DIRECTORY_SEPARATOR . $image;
        }

    }

    /**
     * @param $filePath
     * @param $allFiles
     *
     * @return mixed
     */
    public function findMatch($filePath, $allFiles)
    {
        foreach ($allFiles as $file) {
            if ($filePath == strtolower($file)) {
                $filePath = $file;
                return $filePath;
            }
        }
        return false;
    }

    /**
     * @param $item
     * @param $productId
     *
     * @return bool
     */
    public function setPermissions($item, $productId)
    {
        $groups = $this->getGroupsStringed($item->KEYWORDS);

        if ($groups == '') {
            return false;
        }

        $query = "INSERT INTO catalog_product_entity_text (`attribute_id`, `value`, `store_id`, `entity_type_id`, `entity_id`)
                  VALUES (
                  (SELECT attribute_id
                   FROM eav_attribute
                   WHERE attribute_code = 'aw_cp_disable_price'), 
                   '" . $groups . "', 
                   1, 
                   4, 
                   " . $productId . ");";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        return true;
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function setStock($productId)
    {
        $query = "INSERT INTO cataloginventory_stock_item (
                    product_id, 
                    stock_id, 
                    qty, 
                    is_qty_decimal, 
                    is_in_stock
                  )
                  VALUES (
                      :product_id, 
                      :stock_id, 
                      :qty, 
                      :is_qty_decimal, 
                      :is_in_stock
                  ) ON DUPLICATE KEY UPDATE qty = 1000;";

        $stmt = $this->pdo->prepare($query);

        try {

            $stmt->execute([
                'product_id' => $productId,
                'stock_id' => 1,
                'qty' => 1000,
                'is_qty_decimal' => 1,
                'is_in_stock' => 1
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * @param $keywords
     *
     * @return string
     */
    public function getGroupsStringed($keywords)
    {
        $groupString = '';
        foreach ($keywords->KEYWORD as $keyword) {
            if (trim((string)$keyword) != '') {
                if ($groupId = $this->getGroupIdByName((string)$keyword)) {
                    $groupString .= $groupId . ',';
                }
            }
        }
        return rtrim($groupString, ',');
    }

    /**
     * @param $groupName
     * @return bool
     */
    public function getGroupIdByName($groupName)
    {
        $stmt = $this->pdo->prepare('SELECT `customer_group_id` FROM customer_group 
                                     WHERE `customer_group_code` = :groupName');

        $stmt->execute([
            'groupName' => $groupName
        ]);

        $result = $stmt->fetch();

        return (isset($result['customer_group_id'])) ? $result['customer_group_id'] : false;
    }

    /**
     * @param $category
     * @return bool
     */
    public function getCategoryId($category)
    {
        $stmt = $this->pdo->prepare('SELECT entity_id FROM catalog_category_entity_text WHERE `value` = :value AND attribute_id = 48');

        $stmt->execute([
            'value' => $category
        ]);

        $result = $stmt->fetch();

        return (isset($result['entity_id'])) ? $result['entity_id'] : false;
    }

    /**
     * @param $sku
     *
     * @return bool
     */
    public function productExists($sku)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM catalog_product_entity 
                                     WHERE `sku` = :sku');

        $stmt->execute([
            'sku' => $sku
        ]);

        $results = $stmt->fetchAll();

        return (count($results) > 0);
    }


    /**
     * @param $artnr
     *
     * @return bool
     */
    public function getProductByArtNr($artnr)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM catalog_product_entity_varchar WHERE `attribute_id` = 132 and `value` = :attrvalue');

        $stmt->execute([
            'attrvalue' => $artnr
        ]);

        $result = $stmt->fetch();

        return (isset($result['entity_id'])) ? $result['entity_id'] : false;
    }

    /**
     * If products had weight, this method would make sense
     *
     * @param $artid
     * @return int
     */
    public function getProductData($artid)
    {
        return 0;
    }

    /**
     * @param $artID
     * @param $attributeId
     *
     * @return array|bool
     */
    public function loadProductData($artID, $attributeId)
    {
        $stmt = $this->pdo->prepare('
        SELECT `entity_id` FROM catalog_product_entity_varchar 
        WHERE `attribute_id` = ' . $attributeId . ' 
        AND `value` = :attrvalue');

        $stmt->execute([
            'attrvalue' => $artID
        ]);
        $result = $stmt->fetch();

        $entityId = (isset($result['entity_id'])) ? $result['entity_id'] : false;

        if ($entityId) {
            $data = [
                'product_id' => $entityId,
                'weight' => $this->getProductAttributeValue('weight', $entityId, 'decimal'),
                'sku' => $this->getProductSkuByEntityId($entityId),
                'name' => trim($this->getProductAttributeValue('name', $entityId, 'varchar'))
            ];

            return $data;
        }
        return false;
    }

    /**
     * @param $entityId
     *
     * @return bool
     */
    public function getProductSkuByEntityId($entityId)
    {
        $stmt = $this->pdo->prepare('
        SELECT `sku` FROM catalog_product_entity 
        WHERE `entity_id` = :entity_id');

        $stmt->execute([
            'entity_id' => $entityId
        ]);

        $result = $stmt->fetch();

        return (isset($result['sku'])) ? $result['sku'] : false;
    }

    /**
     * @param $attributeCode
     * @param $entityId
     * @param $dataType
     *
     * @return bool
     */
    public function getProductAttributeValue($attributeCode, $entityId, $dataType)
    {
        $attributeId = $this->selectAttributeIdByCode($attributeCode, 4);

        $stmt = $this->pdo->prepare("
        SELECT `value` FROM catalog_product_entity_$dataType 
        WHERE `attribute_id` = " . $attributeId . "
        AND `entity_id` = :entity_id");

        $stmt->execute([
            'entity_id' => $entityId
        ]);

        $result = $stmt->fetch();

        return (isset($result['value'])) ? $result['value'] : false;
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

        return $results['attribute_id'];
    }

    /**
     * @return array|bool
     */
    public function getAllProducts()
    {
        $stmt = $this->pdo->prepare('SELECT * FROM catalog_product_entity');

        $stmt->execute();

        $results = $stmt->fetchAll();

        return (!empty($results)) ? $results : false;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductCategories($productId)
    {
        $stmt = $this->pdo->prepare('SELECT ccev.value
                                        FROM catalog_category_product ccp
                                          JOIN catalog_category_entity cce ON ccp.category_id = cce.entity_id
                                          JOIN catalog_category_entity_varchar ccev ON cce.entity_id = ccev.entity_id
                                          JOIN eav_attribute ea ON ccev.attribute_id = ea.attribute_id
                                        WHERE ccp.product_id = :product_id
                                            AND ccev.attribute_id = 41;');

        $stmt->execute([
            'product_id' => $productId
        ]);

        $results = $stmt->fetchAll();

        $cats = [];

        if (count($results) > 0) {
            $counter = 1;
            foreach ($results as $result) {
                $cats['CAT' . $counter] = $result['value'];
                $counter++;
            }
        }

        return $cats;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductKeywords($productId)
    {
        $stmt = $this->pdo->prepare("SELECT `value`
                                        FROM catalog_product_entity_text
                                        WHERE attribute_id =
                                        (SELECT attribute_id
                                        FROM eav_attribute
                                        WHERE attribute_code = 'aw_cp_disable_price')
                                        AND entity_id = :product_id;");

        $stmt->execute([
            'product_id' => $productId
        ]);

        $results = $stmt->fetchAll();

        $keywords = [];

        if (count($results) > 0) {
            foreach ($results as $result) {
                if (strstr($result, ',') !== false) {
                    $keywords[] = $this->getGroupName($result['value']);
                } else {
                    $keywordsArray = explode(',', $result['value']);
                    foreach ($keywordsArray as $item) {
                        $customerGroup = $this->getGroupName(trim($item));
                        $keywords[] = $customerGroup;
                    }
                }
            }

            return [
                'KEYWORD' => $keywords
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $groupId
     * @return bool
     */
    public function getGroupName($groupId)
    {
        $stmt = $this->pdo->prepare('SELECT customer_group_code FROM customer_group WHERE `customer_group_id` = :customer_group_id');

        $stmt->execute([
            ':customer_group_id' => $groupId
        ]);

        $result = $stmt->fetch();

        return (isset($result['customer_group_code'])) ? $result['customer_group_code'] : false;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductFiles($productId)
    {
        $stmt = $this->pdo->prepare('SELECT * from uni_fileuploader where product_ids = :product_id;');

        $stmt->execute([
            'product_id' => $productId
        ]);

        $results = $stmt->fetchAll();

        $files = [];

        if (count($results) > 0) {
            foreach ($results as $result) {
                $files[] = [
                    'HEADER' => $result['title'],
                    'PATH' => $result['uploaded_file']
                ];
            }
        } else {
            return [];
        }

        return [
            'FILE' => $files
        ];
    }

}