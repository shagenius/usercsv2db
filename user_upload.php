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

$db_name = isset($config['db_name']) ? $config['db_name'] : '';


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

if(count($options)==0){
    output_help($args_list);
}

// required db parameters:
$username = isset($options['u']) ? $options['u'] : '';
$password = isset($options['p']) ? $options['p'] : '';
$host = isset($options['h']) ? $options['h'] : '';
$file = isset($options['file']) ? $options['file'] : '';
$create_table = isset($options['create_table']) ? true : '';
$dryrun = isset($options['dry_run']) ? true : false;

if($username=='') {
    die("Missing arguments, -u must be supplied, type --help for all the options!\n");
}

if($password=='') {
    die("Missing arguments, -p must be supplied, type --help for all the options!\n");
}

if($host=='') {
    die("Missing arguments, -h must be supplied, type --help for all the options!\n");
}

if($dryrun && $file=='') {
    die("Missing arguments, --file must be supplied!\n");
}

if($file == '' && !$create_table) {
    die("Missing arguments, --create_table or --file must be supplied!\n");
}

$db = new Database($db_name, $host, $username, $password);

// create/rebuild the user table
if ($create_table) {
    create_user_table($db);
    exit();
}


// validate csv file
if($file && !validateCsv($file)) {
    die("Invalid user csv file\n");
}

//process upload
if(file_exists('uploads/'.$file)){
    // check if table exists before insert/process the csv file
    try {
        $conn = $db->connect();
        $conn->query('SELECT id FROM users');
        unset($conn);
    } catch (Exception $ex) {
        die($ex->getMessage() . ", please use --create_table to create the users table or type --help for more options\n");
    }

    $file  = 'uploads/'.$file;
    $reader = Reader::createFromPath($file, 'r');
    $reader->setHeaderOffset(0);
    $records = (new Statement())->process($reader);

    foreach ($records->getRecords() as $record) {
        foreach ($record as $k => $v) {
            ${trim($k)} = trim($v);
        }
        $user = new User($name, $surname, $email);
        if(!$user->validate_email()) {
            fwrite(STDOUT, $email . " is not a valid email!\n"); 
        } else {
            if($dryrun) {
                create_test_table($db);
                $user->fakeSave($db);
            } else {
                $user->save($db);
            }
        }
        
    }
} else {
    fwrite(STDOUT, "\nFile could not be found!\n\n");
}


/**
 * Display the help menu
 * @param type $args_list
 */
function output_help($args_list) {
    print  "Options:" . PHP_EOL;
    foreach ($args_list as $k => $v) {
        print str_pad($k, 24, ' ')  . $v . PHP_EOL;
    }
    exit();
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
                MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT; \n";
        
        $conn->query($sql);
    }
    catch(\Exception $e)
    {
        echo "Connection failed - ".$e->getMessage();
        //@todo: log exception
    }
    unset($conn);
}

/**
 * Create user table
 * @param type $db
 */
function create_test_table($db) {
    try {
        $conn = $db->connect();
        $sql = "DROP TABLE IF EXISTS `user_test`;\n";
        
        $sql .= "CREATE TABLE `user_test` (
                `id` bigint(20) UNSIGNED NOT NULL,
                `name` varchar(255) NULL,
                `surname` varchar(255) NULL,
                `email` varchar(255) UNIQUE NOT NULL,
                `created_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
              ) ENGINE=BLACKHOLE DEFAULT CHARSET=utf8;\n";
        $sql .= "ALTER TABLE `users`
                ADD PRIMARY KEY (`id`);\n";
        
        $sql .= "ALTER TABLE `users`
                MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;\n";
        
        $conn->query($sql);
    }
    catch(\Exception $e)
    {
        echo "Connection failed - ".$e->getMessage() . "\n";
        //@todo: log exception
    }
    unset($conn);
}

function validateCsv($file){
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    if($ext!=='csv') {
        return false;
    }
    
    return true;
}