<?php

class XMLExportImport
{
    /**
     * @param $host
     * @param $db
     * @param $user
     * @param $pass
     * @param $charset
     *
     * @return PDO
     */
    public function createConnection($host, $db, $user, $pass, $charset)
    {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        $pdo = new PDO($dsn, $user, $pass, $opt);
        return $pdo;
    }

    /**
     * @param $files
     *
     * @return bool
     */
    public function validateFiles($files)
    {
        $isValid = true;
        #check file existence
        foreach ($files as $file => $name) {
            if (!file_exists($name)) {
                $this->printOut('The file ' . $file . ' does not exist.');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function printOut($arg)
    {
        echo $arg . "\n";
    }
}