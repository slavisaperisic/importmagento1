<?php

include getcwd().'app/Mage.php';

function xmlexportimportloader($class)
{
    include 'xmlexportimport/' . $class . '.php';
}

function model_loader($class)
{
    include 'xmlexportimport/model/' . $class . '.php';
}

function model_orm_loader($class)
{
    include 'xmlexportimport/model/orm/' . $class . '.php';
}

function data_loader($class)
{
    include 'xmlexportimport/data/' . $class . '.php';
}

function connection_module($class)
{
    include 'xmlexportimport/connection/' . $class . '.php';
}

spl_autoload_register('xmlexportimportloader');
spl_autoload_register('model_loader');
spl_autoload_register('model_orm_loader');
spl_autoload_register('data_loader');
spl_autoload_register('connection_module');

Mage::app();

$dateTimestamp = time();
$xmlExportImport = new XMLExportImport();
$executor = new XMLExecutor();

$dbConfig = Mage::getConfig()->getResourceConnectionConfig('default_setup');

$connection = $xmlExportImport->createConnection(
    $dbConfig->host,
    $dbConfig->dbname,
    $dbConfig->username,
    $dbConfig->password,
    'utf8'
);

$configRegular = Mage::getStoreConfig('ftp_data/ftp_group_data');

$files = [
    'customers_addresses' => $configRegular['ftp_server_local_dir'] . '/ADRESSEN.XML',
    'categories' => $configRegular['ftp_server_local_dir'] . '/KUERZEL.XML',
    'products' => $configRegular['ftp_server_local_dir'] . '/SHOPDATEN.XML',
    'prices' => $configRegular['ftp_server_local_dir'] . '/PREISE.XML',
    'branches' => $configRegular['ftp_server_local_dir'] . '/BRANCHESTICHWORT.XML',
    'orders' => $configRegular['ftp_server_local_dir'] . '/ORDERS.XML',
];

$config = Mage::getStoreConfig('ftp_data/ftp_group_data_files');

if (!$xmlExportImport->validateFiles($files)) {
    echo 'Check files existence' . "\n";
    exit;
}

//$executor->truncate($connection);exit;
$startTime = $executor->start();

$executor->setup($connection);

$branches = $executor->importCustomers($files, $connection, $dateTimestamp);
$executor->importCategories($files, $connection, $dateTimestamp);
$executor->importProducts($files, $connection, $dateTimestamp, $config);
$executor->importPrices($files, $connection, $dateTimestamp);
$executor->importOrders($files, $connection, $dateTimestamp);

echo "\e[31m" . "Executed in " . $executor->end($startTime) . " seconds" . "\e[0m" . "\n";
