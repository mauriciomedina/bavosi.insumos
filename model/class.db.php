<?php
/**
 * Created by PhpStorm.
 * User: gmartin
 * Date: 18/06/2015
 * Time: 11:49 AM
 */
abstract class DB {

        private static $db_hostname = 'LOCALHOST\SISTEMAS1';      //DataBase Server

        private static $db_username = 'sa';             //User for connect to DataBase

        private static $db_password = 'oMega1234';      //Password of User for connect to DataBase

        private static $db_name = 'INSUMOS';           //DataBase Name

        private $conn = null;                           //Variable para almacenar la conexion a la Base de Datos

        protected $result = 0;

        protected  $query;                              //Variable para almacenar un consulta de SQL

        protected $rows  = array();                      //Array para almacenar los resultados de una consulta

        /**
        * Abstracts methods for ABM of the classes that inherit
        */
        abstract protected function get();
        abstract protected function set();
        abstract protected function edit();
        abstract protected function delete();

        /**
        * Connect to DataBase
        */
        private function open_connection() {
            $this->conn = mssql_connect(self::$db_hostname,self::$db_username,self::$db_password);
            mssql_select_db(self::$db_name,$this->conn);
        }

        /**
        * Disconnect from DataBase
        */
        private function close_connection() {
            mssql_close($this->conn);
        }

        /**
        * Execute a simple query ( INSERT, DELETE, UPDATE )
        */
        protected function exec_query() {
            if(is_null($this->conn)) self::open_connection();
            mssql_query($this->query,$this->conn);
            $this->close_connection();
        }

        /**
         * Get results from query to an Array
         */
        protected function get_results_from_query() {
            if(is_null($this->conn)) self::open_connection();
            $result = mssql_query($this->query,$this->conn);
            while( $row = mssql_fetch_assoc($result) ){
                $this->rows[] = $row;
            }
            $this->close_connection();
            //array_pop($this->rows);
        }
    }

?>