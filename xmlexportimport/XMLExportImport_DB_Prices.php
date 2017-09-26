<?php

/**
 * Class XMLExportImport_DB_Prices
 */
class XMLExportImport_DB_Prices
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
     * @var PriceORM
     */
    private $priceORM;

    /**
     * @var CustomerORM
     */
    private $customerORM;

    /**
     * @var ProductORM
     */
    private $productORM;

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
    private $priceModel;

    public function __construct(
        $pdo,
        $dateTimestamp
    )
    {
        $this->pdo = $pdo;
        $this->dateTimestamp = $dateTimestamp;
        $this->priceModel = new Price();
        $this->customerORM = new CustomerORM($pdo);
        $this->productORM = new ProductORM($pdo);
        $this->priceORM = new PriceORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
        $this->helper = new Helper();
        $this->began = false;
    }

    private $unitValues = [
        'BBgs' => 4,
        'Satz' => 5,
        'Stck' => 6,
        'Eim.' => 7,
        'Bund' => 8
    ];

    /**
     * @param $item
     * @param $groupId
     * @return bool
     */
    public function createGroupPrice($item, $groupId)
    {
        $_priceValues = $this->helper->extractValuesPrice($item);

        $productId = $this->productORM->getProductByArtNr($_priceValues['artnr']);

        try {
            $this->pdo->beginTransaction();
            $this->priceORM->insertGroupPrice($_priceValues, $groupId, $productId);
            $this->priceORM->insertFromPrice($item, $groupId, $productId);

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function setUnit($article)
    {
        echo "setting unit \n";
        $unitString = (string)$article->UNIT;
        $artnr = (string)$article->ARTNR;
        $productId = $this->productORM->getProductByArtNr($artnr);
//        echo $productId;exit;

        try {
            $this->pdo->beginTransaction();

            $this->attributeORM->insertAttributeValueGeneric(
                $productId,
                'unit',
                $this->unitValues[$unitString],
                'int',
                'catalog_product_entity_',
                4
            );

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;

    }

    /**
     * @param $lino
     *
     * @return bool|string
     */
    public function getGroupName($lino)
    {
        $data = explode('_', $lino);
        $groupName = $data[0];
        return substr($groupName, 1);
    }

    /**
     * @param $lino
     *
     * @return bool|string
     */
    public function getGroupNameReal($lino)
    {
        return substr($lino, -1);
    }

    /**
     * @param $xmlPrices
     */
    public function handlePrices($xmlPrices)
    {
        $setUnit = true;
        foreach ($xmlPrices->PRICELIST as $item) {
            $groupId = $this->customerORM->getGroupId($this->getGroupNameReal($item->LINO));

            foreach ($item->ARTICLE as $article) {
                echo 'setting group prices for product ' . (string)$article->ARTID . "\n";
                $this->createGroupPrice($article, $groupId);
                if ($setUnit) {
                    $this->setUnit($article);
                }
            }
            $setUnit = false;
        }
    }
}