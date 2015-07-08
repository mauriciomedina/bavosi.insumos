<?php
/**
 * Created by PhpStorm.
 * User: gmartin
 * Date: 25/06/2015
 * Time: 10:25 AM
 */
include_once('class.db.php');

class User extends DB {

    public $id;
    public $user_name;
    public $password;
    public $name;
    public $email;
    public $is_admin;

    function __construct(){}
    function __destruct() { unset($this); }

    public function check_credentials($user_name,$password){
        if($user_name != "" && $password != ""){
            $this->query = "SELECT COUNT(*)Result FROM [usInsumos].[Usuarios] WHERE Usuario = '$user_name' and Password = '$password'";
            $this->get_results_from_query();
            foreach($this->rows as $check){
                if($check['Result'] == 1){
                    $this->get($user_name);
                    return true;
                }else{
                    return false;
                }
            }
        }
        return false;
    }

    public function get($user_name=""){
        if($user_name != ''){
            $this->query = "SELECT [IdUsuario],[Nombre],[Usuario],[Password],[Email],EsAdmin FROM [usInsumos].[Usuarios] WHERE Usuario = '".$user_name."'"; // Get a row
        }else{
            $this->query = "SELECT [IdUsuario],[Nombre],[Usuario],[Password],[Email],EsAdmin FROM [usInsumos].[Usuarios]"; // Get all rows
        }
        $this->get_results_from_query();

        if($user_name != ""){
            foreach($this->rows as $user){
                $this->id = $user['IdUsuario'];
                $this->name = $user['Nombre'];
                $this->user_name = $user['Usuario'];
                $this->password = $user['Password'];
                $this->email = $user['Email'];
                $this->is_admin = $user['EsAdmin'];
            }
        }else{
            return $this->rows;
        }
    }

    public function set($user_data = array()){
        if(array_key_exists('user_name',$user_data)){
            if(array_key_exists('password',$user_data)){
                if(array_key_exists('name',$user_data)){
                    if(array_key_exists('email',$user_data)){

                        $this->user_name = $user_data['user_name'];
                        $this->password = $user_data['password'];
                        $this->name = $user_data['name'];
                        $this->email = $user_data['email'];

                        $this->query = "INSERT INTO [usInsumos].[Usuarios] ([Nombre],[Usuario],[Password],[Email])
                                        VALUES ('$this->name','$this->user_name','$this->password','$this->email')
                                        SELECT SCOPE_IDENTITY() IdUsuario;";

                        $this->get_results_from_query();
                        foreach($this->rows as $new_user){
                            $this->id = $new_user['IdUsuario'];
                        }
                    }
                }
            }
        }
    }

    public function edit($user_data = array()){
        if(array_key_exists('user_name',$user_data)){
            $this->get($user_data['user_name']);
            if($user_data['user_name']==$this->user_name){

                $this->password = $user_data['password'];
                $this->name = $user_data['name'];
                $this->email = $user_data['email'];

                $this->query = "UPDATE [usInsumos].[Usuarios]
                                   SET [Nombre] = '$this->name'
                                      ,[Password] = '$this->password'
                                      ,[Email] = '$this->email'
                                 WHERE Usuario = '$this->user_name'";
                $this->exec_query();
            }
        }
    }
    public function delete($user_name=""){

        if($user_name != ''){
            $this->get($user_name);
            if($user_name == $this->user_name){
                $this->query = "DELETE FROM [usInsumos].[Usuarios] WHERE Usuario =  $this->user_name";
                $this->exec_query();
            }
        }

    }

} 