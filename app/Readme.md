# usercsv2db

usercsv2db is a php script that process a csv file containing user data and store them to the database

## Installation

1. Install the csv league package dependency, which is required to read the csv file

```bash
composer install 
``` 

2. Edit the config file 'config/app' and change the 'db_name' value to the name of your database / create a database called 'cat' (default)

## Usage

In command line, run the php script user_upload.php with the following arguments

--file                  [csv file name] – this is the name of the CSV to be parsed

--create_table          – this will cause the MySQL users table to be built (and no further action will be taken)

--dry_run               – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered

-u                      – MySQL username

-p                      – MySQL password

-h                      – MySQL host

--help                  – which will output the above list of directives with details.

# Example - create user table

run the command below:
```bash
php user_upload.php -u root -p root -h localhost --create_table
```