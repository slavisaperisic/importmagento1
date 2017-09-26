<?php

include '/var/www/vhosts/claytec.de/httpdocs/shop/app/Mage.php';

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

$dbConfig = Mage::getConfig()->getResourceConnectionConfig('default_setup');

$xmlExportImport = new XMLExportImport();

$connection = $xmlExportImport->createConnection(
    $dbConfig->host,
    $dbConfig->dbname,
    $dbConfig->username,
    $dbConfig->password,
    'utf8'
);

$executor = new XMLExecutor();
$instantiator = new ModelInstantiator($connection);

$executor->exportCustomers($instantiator);
$executor->exportProducts($instantiator);
$executor->exportOrders($instantiator);
$executor->exportPrices($instantiator);

$executor->zipFiles();