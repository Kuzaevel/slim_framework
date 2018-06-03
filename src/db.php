<?php
/**
 * Created by PhpStorm.
 * User: cool
 * Date: 03.06.18
 * Time: 3:18
 */

require_once '../src/db.php';

    class db{
        //properties
        private $dbhost  = "localhost";
        private $dbuser  = "admin";
        private $dbpass  = "admin";
        private $dbname  = "facepass";
        private $charset = "utf8";
//
//        private $dbhost  = $settings['db']['host'];
//        private $dbuser  = $settings['db']['username'];
//        private $dbpass  = $settings['db']['password'];
//        private $dbname  = $settings['db']['database'];
//        private $charset = $settings['db']['charset'];

        //connection
        public function connect(){

            $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname;charset=$this->charset";
            $dbConection = new PDO($mysql_connect_str,$this->dbuser,$this->dbpass);
            $dbConection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $dbConection;
        }

    };