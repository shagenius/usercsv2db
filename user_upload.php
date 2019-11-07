#!/usr/bin/php
<?php
/**
 * description: Export user CSV file to datable (user table) via command line interface (CLI)
 * author: Shameemah Kurzawa <shameemah@gmail.com>
 * date: 04 Nov 2019
 */
include 'vendor/autoload.php';
$config = include 'config/app.php';
include 'app/Database.php';
include 'app/User.php';

use League\Csv\Reader;
use League\Csv\Statement;

$db = isset($config['db_name']) ? $config['db_name'] : '';
$dryrun = false;


$args_list = array(
    '--file' => '[csv file name] – this is the name of the CSV to be parsed',
    '--create_table' => '– this will cause the MySQL users table to be built (and no further action will be taken)',
    '--dry_run' => '– this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won\'t be altered',
    '-u' => '– MySQL username',
    '-p' => '– MySQL password',
    '-h' => '– MySQL host',
    '--help' => '– which will output the above list of directives with details.'
);

$options = getParams();

if (array_key_exists('help', $options)) {
    output_help($args_list);
}

if (array_key_exists('dry_run', $options)) {
    $dryrun = true;
}

//check if the arguments/parameters are valuid
if (!valid_arguments($options)) {
    echo "Missing arguments, to see the required arguments type --help" . PHP_EOL;
    exit;
}

// required inputs:
$username = $options['u'];
$password = $options['p'];
$host = $options['h'];



$dbCon = new Database($db, $host, $username, $password);

// create/rebuild the user table
if (array_key_exists('create_table', $options)) {
    create_user_table($dbCon);
}

// validate csv file
$filename = $options['file'];

if(!validateCsv($filename)) {
    die("Invalid user file");
}

//process upload
if(file_exists('uploads/'.$filename)){
    $file  = 'uploads/'.$filename;
    $reader = Reader::createFromPath($file, 'r');
    $reader->setHeaderOffset(0);
    $records = (new Statement())->process($reader);

    foreach ($records->getRecords() as $record) {
        foreach ($record as $k => $v) {
            ${trim($k)} = trim($v);
        }
        $user = new User($name, $surname, $email);
        if(!$user->validate_email()) {
            fwrite(STDOUT, "Invalid email!\n"); 
        } else {
            $user->save($dbCon);
        }
        
    }
}


/**
 * Display the help menu
 * @param type $args_list
 */
function output_help($args_list) {
    $green_font = "";
    $white_font = "";
    print $white_font . "Options:" . PHP_EOL;
    foreach ($args_list as $k => $v) {
        print $green_font . str_pad($k, 24, ' ') . " " . $white_font . $v . PHP_EOL;
    }
    print PHP_EOL;
}

/**
 * get the params entered by the user via command line
 * @return type
 */
function getParams() {
    $options = getopt('u:p:h:', ['create_table', 'file:', 'dry_run', 'help']);
    return $options;
}

/**
 * check if the arguments are valid
 * @param type $user_args
 * @return boolean
 */
function valid_arguments($user_args) {
    $mandatory_args = array('u', 'p', 'h', 'file');
    $invalid_count = 0;

    foreach ($mandatory_args as $key) {
        if (!array_key_exists($key, $user_args)) {
            $invalid_count++;
        }
    }

    if ($invalid_count > 0) {
        return false;
    }

    return true;
}

/**
 * Create user table
 * @param type $db
 */
function create_user_table($db) {
    try {
        $conn = $db->connect();
        $sql = "DROP TABLE IF EXISTS `users`;\n";
        
        $sql .= "CREATE TABLE `users` (
                `id` bigint(20) UNSIGNED NOT NULL,
                `name` varchar(255) NULL,
                `surname` varchar(255) NULL,
                `email` varchar(255) UNIQUE NOT NULL,
                `created_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
        
        $sql .= "ALTER TABLE `users`
                ADD PRIMARY KEY (`id`);\n";
        
        $sql .= "ALTER TABLE `users`
                MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;\n";
        
        $conn->query($sql);
    }
    catch(\Exception $e)
    {
        echo "Connection failed - ".$e->getMessage();
        //@todo: log exception
    }
    $conn = null;
}

function validateCsv($filename){
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if($ext!=='csv') {
        return false;
    }
    
    return true;
}