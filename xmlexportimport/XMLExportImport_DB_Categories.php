<?php

/**
 * Class XMLExportImport_DB_Categories
 */
class XMLExportImport_DB_Categories
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
     * @var CategoryORM
     */
    private $categoryORM;

    /**
     * @var AttributeORM
     */
    private $attributeORM;

    /**
     * @var bool $began
     */
    private $began;

    /**
     * @var Helper $helper
     */
    private $helper;

    /**
     * @var
     */
    private $categoryModel;

    public function __construct(
        $pdo,
        $dateTimestamp
    )
    {
        $this->pdo = $pdo;
        $this->dateTimestamp = $dateTimestamp;
        $this->categoryModel = new Category();
        $this->categoryORM = new CategoryORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
        $this->helper = new Helper();
        $this->began = false;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public function createCategory($item)
    {
        $_categoryValues = $this->helper->extractValuesCategory($item);
        $_categoryFields = $this->categoryModel->getFields();

        try {
            $this->pdo->beginTransaction();

            if ($categoryId = $this->categoryORM->insertCategory($item)) {
                $this->attributeORM->fillAttributesGeneric($_categoryFields, $categoryId, $_categoryValues, 'catalog_category_entity_', 3);
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function handleCategories($xml)
    {
        foreach ($xml->SHORTCUTS as $item) {
            $type = (string)$item->attributes();

            if ($type == 'SHP_KAT') {
                foreach ($item->SHORTCUT as $category) {
                    $this->createCategory($category);
                }
            }
        }
    }
}