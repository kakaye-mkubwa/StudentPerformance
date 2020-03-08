<?php
namespace App\Utils;
use App\Utils\DBConfig;

class DBConnect{

    private $username = DBConfig::DB_USERNAME;
    private $serverName = DBConfig::DB_SERVER;
    private $password = DBConfig::DB_PASS;
    private $dbName = DBConfig::DB_NAME;

    public function connection(){
        return mysqli_connect($this->serverName,$this->username,$this->password,$this->dbName);
    }
}



