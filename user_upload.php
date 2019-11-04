#!/usr/bin/php
<?php
/**
 * description: Export user CSV file to datable (user table) via command line interface (CLI)
 * author: Shameemah Kurzawa <shameemah@gmail.com>
 * date: 04 Nov 2019
 */

$args_list = array(
  '--file' => '[csv file name] – this is the name of the CSV to be parsed',
  '--create_table'=> '– this will cause the MySQL users table to be built (and no further action will be taken)',
  '--dry_run' => '– this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won\'t be altered',
  '-u' => '– MySQL username',
  '-p' => '– MySQL password',
  '-h' => '– MySQL host',
  '--help' => '– which will output the above list of directives with details.'
   
);

$dryrun = false;
$is_args_complete = true; // check if the required argument are present;

// required inputs:
$username = '';
$password = '';
$host = '';

if(in_array('--help', $argv)) {
    output_help($args_list);
    exit();
}

$mandatory_arguments = $args_list;
unset($mandatory_arguments['--dry_run']);
unset($mandatory_arguments['--help']);


foreach ($mandatory_arguments as $k => $arg) {
    if(!in_array($k, $argv)) {
            $is_args_complete = false;
    }
}

if(!$is_args_complete ) {
    print "Missing arguments, to see the required arguments type \033[32m--help" . "\033[37m" . PHP_EOL;
    exit();
}

foreach($argv as $arg=>$val) {
     if($arg==='--dry_run') {
         $dryrun = true;
         unset($argv['--dry_run']);
     } else {
         // get args values
     }
}

create_user_table($username, $password, $host);

function output_help($args_list){
    print "\033[37m". "Options:" . PHP_EOL;
    foreach ($args_list as $k => $v) {
        print "\033[32m" . str_pad($k, 24, ' ') . " " . "\033[37m" . $v . PHP_EOL;
    }
}

function create_user_table () {
    print "\033[32m user" . "\033[37m table created!" . PHP_EOL;
}