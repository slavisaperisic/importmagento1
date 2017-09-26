<?php

/**
 * Class XMLExportImport_DB_Products
 */
class XMLExportImport_DB_Products
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
    private $productModel;

    /**
     * @var bool
     */
    private $goodFtp = true;

    private $xmlFtpConnection;

    public function __construct(
        $pdo,
        $dateTimestamp
    )
    {
        $this->pdo = $pdo;
        $this->dateTimestamp = $dateTimestamp;
        $this->productModel = new Product();
        $this->productORM = new ProductORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
        $this->helper = new Helper();
        $this->began = false;
    }

    /**
     * @param $item
     * @param $config
     * @param $allFiles
     *
     * @return bool
     */
    public function createProduct($item, $config, $allFiles)
    {
        $_productValues = $this->helper->extractValuesProduct($item);
        $_productFields = $this->productModel->getFields();

        try {
            $this->pdo->beginTransaction();

            if ($productId = $this->productORM->insertProduct($_productValues)) {
                $this->productORM->insertProductCategories($_productValues, $productId);
                $this->attributeORM->fillAttributesGeneric($_productFields, $productId, $_productValues, 'catalog_product_entity_', 4);

                if ($this->goodFtp) {
                    $this->productORM->insertProductFiles(
                        $_productValues,
                        $productId,
                        $config,
                        $this->xmlFtpConnection,
                        $allFiles
                    );
                }

                $this->productORM->setPermissions($item, $productId);
                $this->productORM->setStock($productId);

                echo 'created product ENTITY ID = ' . $productId . "\n";
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    /**
     * @param $xml
     * @param $config
     */
    public function handleProducts($xml, $config)
    {
        $this->xmlFtpConnection = new FTP(
            $config['ftp_server_remote_host_files'],
            $config['ftp_server_remote_login_files'],
            $config['ftp_server_remote_pass_files'],
            $config['ftp_server_local_dir_files'],
            $config['ftp_server_remote_dir_files'],
            $config['ftp_server_local_dir_files_files']
        );
        if (!$this->xmlFtpConnection->connect()) {
            echo 'not connected to ' . $config['ftp_server_remote_host'] . "\n";
            $this->goodFtp = false;
        } else {
            echo 'connected to FTP file server ' . "\n";
        }

        $allFiles = $this->xmlFtpConnection->finalFileList($config['ftp_server_remote_dir_files']);

        foreach ($xml->ARTICLE as $item) {
            $this->createProduct($item, $config, $allFiles);
        }

        $this->xmlFtpConnection->disconnect();
    }
}