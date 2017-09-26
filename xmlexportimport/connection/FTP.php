<?php

class FTP
{
    private $server;

    private $login;

    private $password;

    private $localDir;

    private $remoteDir;

    private $ftpConnection;

    /**
     * FTP constructor.
     * @param $server
     * @param $login
     * @param $password
     * @param $localDir
     * @param $remoteDir
     */
    public function __construct($server, $login, $password, $localDir, $remoteDir)
    {
        $this->server = $server;
        $this->login = $login;
        $this->password = $password;
        $this->localDir = $localDir;
        $this->remoteDir = $remoteDir;
    }

    /**
     * @return resource|string
     */
    public function connect()
    {
        $this->ftpConnection = ftp_connect($this->server) or die("Could not connect to $this->server");

        try {
            ftp_login($this->ftpConnection, $this->login, $this->password);
            ftp_pasv($this->ftpConnection, true);

            return $this;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $path
     * @return array
     */
    public function getAllFilesList($path)
    {
        static $allFiles = [];
        $contents = ftp_nlist($this->getFtpConnection(), $path);

        foreach ($contents as $file) {
            // assuming its a folder if there's no dot in the name
            if (strpos($file, '.') === false) {
                $this->getAllFilesList($file);
            }
            $allFiles[$path][] = $file;//substr($file, strlen($path) + 1);
        }
        return $allFiles;
    }

    /**
     * @param $path
     * @return array
     */
    public function finalFileList($path)
    {
        $allFiles = $this->getAllFilesList($path);

        $finals = [];
        foreach ($allFiles as $folderKey => $folderValue) {
            foreach ($folderValue as $file) {
                if (strpos($file, '.png') !== false || strpos($file, '.pdf') !== false || strpos($file, '.jpg') !== false) {
                    $finals[] = $file;
                }
            }
        }
        return $finals;
    }

    /**
     * @return mixed
     */
    public function getFtpConnection()
    {
        return $this->ftpConnection;
    }

    /**
     * @param $localFilename
     * @param $remoteFilename
     * @param bool $fileJust
     * @param bool $pdfUrl
     *
     * @return bool
     */
    public function downloadFile($localFilename, $remoteFilename, $fileJust = false, $pdfUrl = false)
    {
        if (!is_array(ftp_nlist($this->ftpConnection, "."))) {
            return false;
        }

        if ($pdfUrl) {
            $localFile = $pdfUrl . DIRECTORY_SEPARATOR . $localFilename;
        } else {
            $localFile = $this->localDir . DIRECTORY_SEPARATOR . $localFilename;
        }

        if ($fileJust) {
            $remoteFile = $remoteFilename;
        } else {
            $remoteFile = $this->remoteDir . DIRECTORY_SEPARATOR . $remoteFilename;
        }

        if (file_exists($localFile)) {
            return false;
        }
//
//        echo $localFile;
//        echo "\n";
//        echo $remoteFile;
//        echo "\n";
//        echo "\n";

        return ftp_get($this->ftpConnection, $localFile, $remoteFile, FTP_BINARY);
    }

    /**
     * disconnect FTP
     */
    public function disconnect()
    {
        ftp_close($this->ftpConnection);
    }
}