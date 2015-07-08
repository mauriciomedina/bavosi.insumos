<?php
/**
 * Created by PhpStorm.
 * User: gmartin
 * Date: 25/06/2015
 * Time: 10:25 AM
 */
include_once('class.db.php');

class Toner extends DB {


    function __construct(){}
    function __destruct() { unset($this); }

    public function listar(){
        $this->query = "SELECT [IdToner],[Toner],[Categoria],[StockMinimo],[Impresoras]FROM [INSUMOS].[usInsumos].[uvToners]";
        $this->get_results_from_query();

        return $this->rows;
    }

    protected function get(){
    }

    protected function set(){
    }

    protected function edit(){
    }

    protected function delete(){
    }
}