<?php

class User{
    
    protected $table_name = 'users';
    public $name;
    public $surname;
    public $email;
    protected $_error;




    public function __construct($name, $surname, $email) {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
    }
    
    function create_table() {
        print "table created!" . PHP_EOL;
    }
    
    /**
     * validate name
     * @return boolean
     */
    private function validate_name() {
        if(!$this->name) {
            return false;
        }
        
        return true;
    }
    
    /**
     * validate surname
     * @return boolean
     */
    private function validate_surname() {
        if(!$this->surname) {
            return false;
        }
        
        return true;
    }
    
    /**
     * validate email
     * @return boolean
     */
    public function validate_email() {
       if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return false;
       }
       return true;
    }
    
    //insert the record into database
    public function save(Database $db){
       // do insert
        $conn = $db->connect();
        $stmt = $conn->prepare("INSERT INTO users (name, surname, email) VALUES(:name, :surname, :email)");
        $stmt->execute([':name' => ucwords($this->name), ':surname' => ucwords($this->surname), ':email' => strtolower($this->email)]);
        
        $conn->beginTransaction();
        try {
            // insert/update query
            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
        }
        $conn=null;
    }
    
    //no insert, dry_run is on
    public function fakeSave(){
        echo 'saved';
        return true;
    }
    
    public function getErrors(){
        return $this->error;
    }
}
