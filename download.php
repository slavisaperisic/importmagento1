<?php

include getcwd() . '/../app/Mage.php';

function connection_module($class)
{
    include 'xmlexportimport/connection/' . $class . '.php';
}

spl_autoload_register('connection_module');

Mage::app();

$files = [
    'customers_addresses' => ['ADRESSEN.XML', 'ADRESSEN.XML'],
    'categories' => ['KUERZEL.XML', 'KUERZEL.XML'],
    'products' => ['SHOPDATEN.XML', 'SHOPDATEN.XML'],
    'prices' => ['PREISE.XML', 'PREISE.XML'],
    'branches' => ['BRANCHESTICHWORT.XML', 'BRANCHESTICHWORT.XML'],
    'orders' => ['ORDERS.XML', 'ORDERS.XML']
];

$config = Mage::getStoreConfig('ftp_data/ftp_group_data');

$xmlFtpConnection = new FTP(
    $config['ftp_server_remote_host'],
    $config['ftp_server_remote_login'],
    $config['ftp_server_remote_pass'],
    $config['ftp_server_local_dir'],
    $config['ftp_server_remote_dir']
);

if (!$ftpConnection = $xmlFtpConnection->connect()) {
    echo 'not connected to ' . $config['ftp_server_remote_host'];
} else {
    foreach ($files as $file => $location) {
        /** @var $ftpConnection FTP */
        if ($ftpConnection->downloadFile($location[0], $location[1])) {
            echo "downloaded $file \n";
        } else {
            echo 'error downloading ' . $file . "\n";
        }
    }

    $xmlFtpConnection->disconnect();
}