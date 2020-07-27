<?php

// Libreria capa de conexion
require_once(CORE_INSTANCE.'/Libs/adodb5/adodb.inc.php');
require_once(CORE_INSTANCE.'/Libs/adodb5/adodb-errorpear.inc.php');

// Clase EntityObjects
require_once(CORE_INSTANCE."/DataAccess/EntityObjects/ConnectionObject.php");

Class Conexion {

    public $Driver;
    public $Server;
    public $Db;
    public $User;
    public $Pass;
    public $cadenaConexion;

    public function __construct(){
        self::initialize();
    }

    public function initialize(){
        $connect = new ConnectionObject();
        // Asociando Datos de conexion
        $this->Driver	=	$connect->Driver;
        $this->Server	=	$connect->Server;
        $this->Db		=	$connect->Db;
        $this->User	=	$connect->User;
        $this->Pass	=	$connect->Pass;
        self::conexion_db();
    }

    public function conexion_db() {
        define('ADODB_ERROR_LOG_TYPE',3);
        define('ADODB_ERROR_LOG_DEST', CORE_INSTANCE."/Log/log.log");
        try{
            //Instanciar clase ConnectionObject
            $connect = new ConnectionObject();
            $Driver = base64_decode($this->Driver);
            $Server = base64_decode($this->Server);
            $Db = base64_decode($this->Db);
            $User = base64_decode($this->User);
            $Pass = base64_decode($this->Pass);
            $this->cadenaConexion = NewADOConnection($Driver);
            $this->cadenaConexion->PConnect($Server, $User, $Pass, $Db);
            $this->cadenaConexion->fmtTimeStamp = "'Y-m-d H:i:s'";
            $this->cadenaConexion->execute("SET NAMES 'utf8'");
        }
        catch(Exception $error){
            echo "Error";
            exit();
        }
    }

}

?>
