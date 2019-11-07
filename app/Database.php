<?php

class Database {

    public $username;
    public $password;
    public $host;
    public $db;

    protected $connection;


    public function __construct($db, $host, $username, $password) {
        $this->db = $db;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Establish a db connection
     * @param type $username
     * @param type $password
     * @param type $server
     */
    public function connect(){
        $db_name = (isset($this->db)) ? $this->db : '';
        $dsn = "mysql:host={$this->host};dbname=$db_name";

        if(!$db_name) {
            die ("Invalid database name");
        }
        
        $conn = new \PDO($dsn, $this->username, '');
        // set the PDO error mode to exception
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
}
