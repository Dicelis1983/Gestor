<?php
error_reporting(E_ALL);
ini_set("display_errors",1);

// Configuracion de ambiente
require_once($_SERVER['DOCUMENT_ROOT']."/GOperativo/Config/enviroment.php");
require_once(CORE_INSTANCE."/IDataAcces/IDataAccesQuery.php");	
require_once(CORE_INSTANCE."/IDataAcces/iDataAccesUpdate.php");
require_once(CORE_INSTANCE."/DataAcces/ConnectionObject.php");
require_once(CORE_INSTANCE."/DataAcces/EntityObjects.php");
require_once(CORE_INSTANCE."/DataAcces/DataAccesQuery.php");	
require_once(CORE_INSTANCE."/DataAcces/DataAccesUpdate.php");
require_once(CORE_INSTANCE."/IBusinessRules/iBusinessRules.php");
require_once(CORE_INSTANCE."/Libs/Session.php"); // Librerias para evitar XSS
require_once(CORE_INSTANCE."/Libs/generalFunctions.php"); // Librerias para evitar XSS

class BusinessRulesClass  implements iBusinessRules {

    public $host = "";
    public $site = "";
    public $option = "";
    public $dac;
    public $dacU;
	public $conexion;

    public function __construct(){
        if(!$this->conexion){
            $this->BRconexion();
		}
		
		/* Validando que exista la Session */
        if(strpos($_SERVER["REQUEST_URI"],"//") === false){
            $ruta = explode("/",$_SERVER["REQUEST_URI"]);
            $lindex = array_pop($ruta);
            unset($lindex);
            $this->ruta = join("/",$ruta);
            $fileTemp = explode("/",$_SERVER["REQUEST_URI"]);
            $fileTemp = end($fileTemp);
			$fileTemp = explode("?",$fileTemp);
			$file = current($fileTemp);
            Session::validData();
			
			/* Instanciando los Objeto */
			$SessionObj 				= 	new SessionObject();
			$ErrorObj 					= 	new ErrorObject();
			
			$parametro			=	'URLClusterPortal';
			$UrlPortal			= 	$this->BR_ConsultarURLPortalUnificado($parametro);
            
			if($file != 'login.php' && (!Session::isStarted() || !Session::isData())){
				if (!isset($_SESSION['IDUsuario'])){
					Session::destroySession();
					header("Location: ".$UrlPortal);
				}
			}
			
			if($file == 'index.php' && !isset($_SESSION['IDUsuario'])){
					Session::destroySession();
					header("Location: ".$UrlPortal);
			}else if($file == 'login.php' && (Session::isStarted() && Session::isData())){
				header("Location: ".$this->ruta."/index.php");
			}else if ($file != 'MasterLogon.php'){
				/* Se valida si la variable IDAplicacion en la session está disponible */
				if(isset($_SESSION['IDAplicacion'])){
					/* Parametro Aplicativo */
					$PParametroApli				=	$_SESSION['IDAplicacion'];
					
					/* Consultando información del Aplicativo */
					$RParametroApli				=	$this->BR_ConsultartablaTbparametrosApl($PParametroApli);
					
					/* Recuperando la bandera Detener del Aplicativo */
					$VParametroApli				=	$RParametroApli['Detener'];
					
					/* Validando si el Aplicativo debe estar en funcionamiento */
					if (isset($VParametroApli) && $VParametroApli==0){
						
						if (!isset($_SESSION['IDUsuario'])){
							$_SESSION['IDUsuario']=NULL;
						}
						
						//print_r($_SESSION['IDUsuario']);
						
						/* Estadistica de Usuarios según Parametros */
						$SessionObj->IDUsuario		=	$_SESSION['IDUsuario'];
						$SessionObj->IDAplicacion	=	$PParametroApli;
						$validar_us					= 	$this->BR_ConsultarUsuarioSesion($SessionObj);
						
						if($validar_us==false){
							$ErrorObj->CodigoError				=	'0008';
							$ErrorObj->Descripcion				=	'El Usuario no es un Usuario autorizado o no existe';
							$ErrorObj->URLPortal				=	$UrlPortal;
							$ErrorObj->URLPortalContingencia	=	0;
							Session::destroySession();
							echo $this->BR_ErrorGeneral($ErrorObj);
							exit();
						}
						
					}else{
						$ErrorObj->CodigoError				=	'0007';
						$ErrorObj->Descripcion				=	'El Aplicativo se encuentra Detenido';
						$ErrorObj->URLPortal				=	$UrlPortal;
						$ErrorObj->URLPortalContingencia	=	0;
						Session::destroySession();
						echo $this->BR_ErrorGeneral($ErrorObj);
						exit();
					}
				}else{
					Session::destroySession();
					header("Location: ".$UrlPortal);
				}
			}
		}
	}

    public function BRconexion() {
        //Instancias Objeto de Acceso a Datos
        $conObj = new ConnectionObject();

        // Invocar funcion de conexiona la Base de Datos	
        $this->dac 			= new DataAccesClass();	
        $this->dacU 		= new DataAccesClassUpdate();		
        $this->conexion 	= $this->dac->DAconexion_db($conObj);
		$this->conexion 	= $this->dacU->DAconexion_db($conObj);
        return $this->conexion;
    }
	
	// Consultar datos de un CRUD
    public function BRDatosCRUD($tabla, $campos, $condicion){
        $fields = array();
        $from = array();
        $sql = "SELECT ";
        $index = 0;
        foreach($campos as $index=>$campo){
            if(!isset($campo["fk"]))
                $fields[] = $tabla.".".$index." AS ".$index;
            else{
                $fkDescription = explode(",", $campo["fkDescription"]);
                for($i=0;$i<count($fkDescription);$i++)
                    $fkDescription[$i] = $campo["fkTable"].".".$fkDescription[$i];
                $fields[] = "".join(",'-',",$fkDescription)." AS ".$index;
                $from[] = "INNER JOIN ".$campo["fkTable"]." ON ".$campo["fkTable"].".".$campo["fkReference"]."=".$tabla.".".$campo["fkReference"];
            }
            $index++;
        }
        $sql .= join(", ", $fields);
        $sql .= " FROM ".$tabla." ".join(" ", $from);
        $sql .= " WHERE ".$condicion;
        $result = $this->dac->DADatosCRUD($sql);
		//print_r($sql);
        return $result;
    }

    /**
     * Consulta a la tabla Funcionarios
     * por los campos id_usuario, Retirado, Bloqueado, Rol
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarUsuarioSesion($SessionObj){
        $result = $this->dac->DA_ConsultarUsuarioSesion($SessionObj);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result->fields;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	 /**
     * Consulta a la tabla Funcionarios y tbUsuarioAplicacion
     * por el campo id_usuario
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarUsuarioxIDUsuario($SessionObj){
        $result = $this->dac->DA_ConsultarUsuarioxIDUsuario($SessionObj);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta a la tabla Funcionarios
     * por el campo id_usuario
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarFuncionarioxIDUsuario($SessionObj){
        $result = $this->dac->DA_ConsultarFuncionarioxIDUsuario($SessionObj);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta a la tabla tbparametrosGeneral
     * por el campo parametro
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarURLPortalUnificado($parametro){
        $result = $this->dac->DA_ConsultarURLPortalUnificado($parametro);
		if($result!=false){
			if($result->rowCount() > 0 ){
				return $result->fields['Valor'];
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	 /**
     * Consulta a la tabla tbsesion
     * por el campo IDSesion
     *
     * @access public
     * @return array
     */
	 public function BR_ConsultarSesion($SessionObj){
        $result = $this->dac->DA_ConsultarSesion($SessionObj);
		if($result!=false){
			if($result->rowCount() > 0 ){
				return $result;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Actualizar la tabla tbsesion
     * por el campo IDSesion, IDUsuario, Tocken
     *
     * @access public
     * @return array
     */
	 public function BR_ActualizartablaSesion($SessionObj){
        $result = $this->dacU->DAU_ActualizartablaSesion($SessionObj);
         if($result != false){
            return true;
        }else{
            return false;
        }
    }
	
	/**
     * Consulta a la tabla tbparametros
     * por el campo Parametro
     *
     * @access public
     * @return array
     */
	 public function BR_ConsultartablaTbparametros($Parametro){
        $result = $this->dac->DA_ConsultartablaTbparametros($Parametro);
		if($result != false){
			if($result->rowCount() > 0 ){
				return $result->fields['Valor'];
			}
			else{
				return false;
			}
		}else{
            return false;
        }
    }
	
	/**
     * Consulta a la tabla tbparametros
     * por el campo Parametro
     *
     * @access public
     * @return array
     */
	 public function BR_ConsultartablaTbparametrosGeneral($Parametro){
        $result = $this->dac->DA_ConsultartablaTbparametros($Parametro);
		if($result != false){
			if($result->rowCount() > 0 ){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
            return false;
        }
    }
	
	/**
     * Consulta a la tabla tbparametros
     * por el campo Parametro
     *
     * @access public
     * @return array
     */
	public function BR_ConsultartablaTbparametrosApl($IDParametro){
        $result = $this->dac->DA_ConsultartablaTbparametrosApl($IDParametro);
		if($result != false){
			if($result->rowCount() > 0 ){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
            return false;
        }
    }
	
	/**
     * Consulta a la tabla tbParametros de Facturacion Electronica
     * por el campo Nombre
     *
     * @access public
     * @return array
     */
	public function BR_ConsultartbParametrosFact($Parametro){
        $result = $this->dac->DA_ConsultartbParametrosFact($Parametro);
		if($result != false){
			if($result->rowCount() > 0 ){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
            return false;
        }
    }
	
	/**
     * Consulta el Estado del Sistema
     * Información General del Sistema
     *
     * @access public
     * @return array
     */
    public function BR_EstadoSistema(){
	$result = $this->dac->DA_EstadoSistema();
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
	}

	/**
     * Consulta a la tabla Funcionarios
     * por el campo id_usuario
     *
     * @access public
     * @return array
     */
    public function BR_CantidadUsuario($SessionObj){
        $result = $this->dac->DA_ConsultarUsuarioGeneral($SessionObj);
        if($result->rowCount() > 0){
            return $result->rowCount();
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta tabla tbParametros Facturacion Electronica
     * Por Parametro
     *
     * @access public
     * @return array
     */
    public function BR_ConsultatbParametro($IdParametro){
        $result = $this->dac->DA_ConsultatbParametro($IdParametro);
        if($result->rowCount() > 0){
            return $result->fields['Valor'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta Pago aplicados Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPagosAplicados(){
        $result = $this->dac->DA_ConsultarPagosAplicados();
        if($result->rowCount() > 0){
            return $result->fields['PagosAplicados'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta Pago aplicados Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagosAplicados($Rango){
		
		// se declaran las variables
		$FechaInicial 	=	NULL;
		$FechaFinal 	=	NULL;
		
		// se declaran la fecha inicial y final
		if (!is_null($Rango) && $Rango != 0 ){
			$result_explode = explode("-",$Rango);
			
			$FechaInicial	=	reset($result_explode);
			$FechaFinal		=	end($result_explode);
			
		}else{
			$FechaInicial	=	NULL;
			$FechaFinal		=	NULL;
		}
		
        $result = $this->dac->DA_ConsultarRegistroPagosAplicados($FechaInicial,$FechaFinal);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}
    }
	
	/**
     * Consulta Reporte Partidas Pendientes por identificar
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPPINpls($Rango){
		
		// se declaran las variables
		$FechaInicial 	=	NULL;
		$FechaFinal 	=	NULL;
		
		// se declaran la fecha inicial y final
		if (!is_null($Rango) && $Rango != 0 ){
			$result_explode = explode("-",$Rango);
			
			$FechaInicial	=	reset($result_explode);
			$FechaFinal		=	end($result_explode);
			
		}else{
			$FechaInicial	=	NULL;
			$FechaFinal		=	NULL;
		}
		
        $result = $this->dac->DA_ConsultarRegistroPPINpls($FechaInicial,$FechaFinal);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}
    }
	
	/**
     * Consulta vista Partidas Pendientes por identificar
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPPIN_Vista(){
        $result = $this->dac->DA_ConsultarRegistroPPI_vista();
		if($result!=false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}
    }
	
	/**
     * Consulta Pago no aplicados Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPagosNoAplicados(){
        $result = $this->dac->DA_ConsultarPagosNoAplicados();
        if($result->rowCount() > 0){
            return $result->fields['PagosNoAplicados'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta Pago aplicados Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagosAplicadosAvales(){
        $result = $this->dac->DA_ConsultarPagosAplicadosAvales();
        if($result->rowCount() > 0){
            return $result->fields['PagosAplicados'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta Pago no aplicados Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagosNoAplicadosAvales(){
        $result = $this->dac->DA_ConsultarPagosNoAplicadosAvales();
        if($result->rowCount() > 0){
            return $result->fields['PagosNoAplicados'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta a la tabla Centro de Costo
     * Información General si el parametro es 0 muestra todos los registros
     *
     * @access public
     * @return array
     */
    public function BR_CantidadNotasDebito(){
        $result = $this->dac->DA_ConsultarNotasDebito();
        if($result->rowCount() > 0){
            return $result->fields['NotasDebito'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta a la tabla Centro de Costo
     * Información General si el parametro es 0 muestra todos los registros
     *
     * @access public
     * @return array
     */
    public function BR_CantidadNotasCredito(){
        $result = $this->dac->DA_ConsultarNotasCredito();
        if($result->rowCount() > 0){
            return $result->fields['NotasCredito'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas NPls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadInifacturaNpls(){
        $result = $this->dac->DA_CantidadInifacturaNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['IniFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Registros de Ini-Facturas NPls
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_RegistrosInifacturaNpls(){
        $result = $this->dac->DA_RegistrosInifacturaNpls();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas Avales
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadInifacturaAvales(){
        $result = $this->dac->DA_CantidadInifacturaAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['IniFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Registros de Ini-Facturas Avales
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_RegistrosInifacturaAvales(){
        $result = $this->dac->DA_RegistrosInifacturaAvales();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Pre-Facturas Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPrefacturaNpls(){
        $result = $this->dac->DA_CantidadPrefacturaNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Registros de Pre-Facturas Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosPrefacturaNpls(){
        $result = $this->dac->DA_RegistrosPrefacturaNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Pre-Facturas Avales
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPrefacturaAvales(){
        $result = $this->dac->DA_CantidadPrefacturaAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Registros de Pre-Facturas Avales
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosPrefacturaAvales(){
        $result = $this->dac->DA_RegistrosPrefacturaAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadInifacturaUfeg(){
        $result = $this->dac->DA_CantidadInifacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['IniFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la Registros de Ini-Facturas en la Ufeg
     * Informacion general
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosInifacturaUfeg(){
        $result = $this->dac->DA_ConsultarInifacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Pre-Facturas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPrefacturaUfeg(){
        $result = $this->dac->DA_CantidadPrefacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la Registros de Pre-Facturas en la Ufeg
     * Informacion general
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosPrefacturaUfeg(){
        $result = $this->dac->DA_ConsultarPrefacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la Registros de Pre-Facturas informe en la Ufeg
     * Informacion general
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosInfoPrefacturaUfeg(){
        $result = $this->dac->DA_ConsultarInfoPrefacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Facturas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_CantidadFacturadoUfeg(){
        $result = $this->dac->DA_CantidadFacturadoUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturado'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la Registros Facturados en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_RegistrosFacturadoUfeg(){
        $result = $this->dac->DA_ConsultarFacturadoUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Inconsistencia en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_CantidadInconsistenciaUfeg(){
        $result = $this->dac->DA_CantidadInconsistenciaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Inconsistencia'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Registros con inconsistencia en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_RegistrosInconsistenciaUfeg(){
        $result = $this->dac->DA_RegistrosInconsistenciaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEGCambio
     * Informacion general
     *
     * @access public
     * @return array
     */
	 public function BR_RInconsistenciaUfegxIDCtrlFactUFEGCambio($factufegcambiosObj){
        $result = $this->dac->DA_RegistrosInconsistenciaUfegxIDCtrlFactUFEGCambio($factufegcambiosObj);
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEG
     * Informacion general
     *
     * @access public
     * @return array
     */
	 public function BR_RInconsistenciaUfegxIDCtrlFactUFEG($factufegcambiosObj){
        $result = $this->dac->DA_RInconsistenciaUfegxIDCtrlFactUFEG($factufegcambiosObj);
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Cuotas anuladas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_CantidadCuotaAnulada(){
        $result = $this->dac->DA_CantidadCuotaAnulada();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['CuotaAnulada'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta registros de Cuotas a anuladas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
	 public function BR_RegistrosCuotaAnulada(){
        $result = $this->dac->DA_RegistrosCuotaAnulada();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Cuotas a facturar en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadCuotaFacturar(){
        $result = $this->dac->DA_CantidadCuotaFacturar();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['ContarCuotaFacturar'];
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadAutFacturaUfeg(){
        $result = $this->dac->DA_CantidadAutFacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadAutFacturaNpls(){
        $result = $this->dac->DA_CantidadAutFacturaNpls();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadAutFacturaAvales(){
        $result = $this->dac->DA_CantidadAutFacturaAvales();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['PreFactura'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta registros Facturas No Autorizadas Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosAutFacturaUfeg(){
        $result = $this->dac->DA_RegistrosAutFacturaUfeg();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta registros Facturas No Autorizadas Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosAutFacturaNpls(){
        $result = $this->dac->DA_RegistrosAutFacturaNpls();
        if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta couta facturar x IDCtrlFactUFEG
     * informacion general
     *
     * @access public
     * @return array
     */
    public function BR_CuotaFacturarxIDCtrlFactUFEG($facturacionufegObj){
        $result = $this->dac->DA_CuotaFacturarxIDCtrlFactUFEG($facturacionufegObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta pre-facturas ufeg x IdFacturaCliente_UfegPre
     * informacion general
     *
     * @access public
     * @return array
     */
    public function BR_PreFacturaUfegxIdFacturaClientePre($prefUfegObj){
        $result = $this->dac->DA_PreFacturaUfegxIdFacturaClientePre($prefUfegObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
    }
	
	/**
     * Consulta la cantidad de Pagos Facturados Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadFacturadoNpls(){
        $result = $this->dac->DA_CantidadFacturadoNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturado'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los registros de Pagos Facturados Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosFacturadoNpls(){
        $result = $this->dac->DA_RegistrosFacturadoNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Pagos Facturados Avales
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadFacturadoAvales(){
        $result = $this->dac->DA_CantidadFacturadoAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturado'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los registros de Pagos Facturados Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosFacturadoAvales(){
        $result = $this->dac->DA_RegistrosFacturadoAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Facturas generadas Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadFacturasNpls(){
        $result = $this->dac->DA_CantidadFacturasNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturas'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Facturas No facturadas Npls
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadNoFacturasNpls(){
        $result = $this->dac->DA_CantidadNoFacturasNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturas'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los registros de Facturas generadas Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosFacturasNpls(){
        $result = $this->dac->DA_RegistrosFacturasNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los registros de Facturas No generadas Npls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosNoFacturasNpls(){
        $result = $this->dac->DA_RegistrosNoFacturasNpls();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Facturas generadas Avales
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadFacturasAvales(){
        $result = $this->dac->DA_CantidadFacturasAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields['Facturas'];
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los registros de Facturas generadas Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosFacturasAvales(){
        $result = $this->dac->DA_RegistrosFacturasAvales();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return 0;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta la cantidad de Facturas
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_CantidadNoFacturado(){
        $result = $this->dac->DA_ConsultarNoFacturado();
        if($result->rowCount() > 0){
            return $result->fields['PreNoAutorizado'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta informacion de un Pago
     * Información General por IdPago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxIdPago($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxIdPago($pagosObj);
		return $result;
    }
	
	/**
     * Consulta si un Pago es de tipo Cheque
     * Información General por IdPago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoChequexIdPago($pagosObj){
        $result = $this->dac->DA_ConsultarPagoChequexIdPago($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Identificacion del Cliente
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxIdentCliente($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxIdentCliente($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Identificacion del Cliente
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarClientexIdentCliente($clienteObj,$TipoBusqueda){
        $result = $this->dac->DA_ConsultarClientexIdentCliente($clienteObj,$TipoBusqueda);
		return $result;
    }
	
	/**
     * Consulta Pago x id_pago
     * Información General si el parametro es 0 muestra todos los registros
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaPagoxIdPago($pagosObj){
	$result = $this->dac->DA_ConsultaPagoxIdPago($pagosObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
	}
	
	/**
     * Consulta Portafolio x id_pago
     * Información General
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaPortafolioxIdPago($pagosObj){
	$result = $this->dac->DA_ConsultaPortafolioxIdPago($pagosObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
	}
	
	/**
     * Desasociar Pago de la  la tabla Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Desasociar_Pago($pagosObj,$clienteObj,$SessionObj){
		// llama la funcion nota debito
		$result = $this->BR_NotaCredito_Pago($pagosObj,$clienteObj,$SessionObj);
		if($result != false){
			// llama la funcion crear un nuevo pago
			$result = $this->BR_Crear_Pago($pagosObj,$clienteObj,$SessionObj);
			
			if($result != false){
				//Desasocia un Pago
				$result = $this->dacU->DAU_Desasociar_Pago($pagosObj);
				
				if($result != false){
					if($result->EOF != false ){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Asociar Pago de la  la tabla Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Asociar_Pago($pagosObj,$clienteObj){
        $result = $this->dacU->DAU_Asociar_Pago($pagosObj,$clienteObj);
        if($result != false){
			if($result->EOF != false ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Aplicar Pago en la  la tabla Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Aplicar_Pago($pagosObj,$SessionObj){
        $result = $this->dacU->DAU_Aplicar_Pago($pagosObj,$SessionObj);
        if($result != false){
			if($result->EOF != false ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Desasociar y asociar Pago de la  la tabla Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Desasociar_Asociar_Pago($pagosObj,$clienteObj,$SessionObj){
        // llama la funcion desasociar pago 
		$result = $this->BR_Desasociar_Pago($pagosObj,$clienteObj,$SessionObj);
		if($result != false){
			// Llama la funcion asociar pago
			$result = $this->BR_Asociar_Pago($pagosObj,$clienteObj);
			if($result != false){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta cliente x identificacion
     * Información General del cliente x identificacion
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaClientexIdentificacion($clienteObj){
	$result = $this->dac->DA_ConsultaClientexIdentificacion($clienteObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
	}
	
	/**
	* Consulta a la tabla Tipo Documento
	* por el campo IDPais
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionTipoDocumento(){
       $result = $this->dac->DA_ConsultarTablaTipoDocumento();
        $html = "<option value=''>Seleccione Tipo Documento...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["TipoDocumento"]."'>".$result->fields["NombreTipoDocumento"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Portafolios del Cliente
	* por el campo 
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionPortafolioxCliente($clienteObj){
       $result = $this->dac->DA_ConsultarPortafolioxIDCliente($clienteObj);
        $html = "<option value=''>Seleccione Portafolio...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IDPortafolio"]."'>".ucwords($result->fields["Portafolio"])."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Portafolios del Cliente x Pago
	* por el campo 
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionPortafolioxClientePago($clienteObj,$pagosObj){
       $result = $this->dac->DA_ConsultarPortafolioxIDCliente($clienteObj);
        $html = "<option value=''>Seleccione Portafolio...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IDPortafolio"]==$pagosObj->IDPortafolio){
						$html .= "<option value='".$result->fields["IDPortafolio"]."' selected>".ucwords($result->fields["Portafolio"])."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDPortafolio"]."'>".ucwords($result->fields["Portafolio"])."</option>";
					}
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Combo Creditos vigentes del Cliente
	* por el campo IDPais
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionCredito($clienteObj,$pagosObj){
       $result = $this->dac->DA_ConsultarCreditoXIDCliente($clienteObj,$pagosObj);
	   $opcion_defaul = 'Seleccione Crédito...';
        $html = "<option value=''>".utf8_encode($opcion_defaul)."</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IDRefinancia"]==$pagosObj->IDRefinancia){
						$html .= "<option value='".$result->fields["IDRefinancia"]."' selected>".$result->fields["Prioridad"]." - ".$result->fields["Credito"]." - ".ucwords($result->fields["EstadoNegocio"])." - ".ucwords($result->fields["PortafolioAbreviado"])."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDRefinancia"]."'>".$result->fields["Prioridad"]." - ".$result->fields["Credito"]." - ".ucwords($result->fields["EstadoNegocio"])." - ".ucwords($result->fields["PortafolioAbreviado"])."</option>";
					}
					$result->MoveNext();
                }
            }else{
				$opcion_defaul = 'No se encontraron Creditos';
				$html = "<option value=''>".utf8_encode($opcion_defaul)."</option>";
			}
        }
        return $html;
    }
	
	/**
	* Combo Acuerdo vigentes del Cliente
	* por el campo IDPais
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionAcuerdo($clienteObj,$pagosObj){
       $result = $this->dac->DA_ConsultarAcuerdoXIDCliente($clienteObj,$pagosObj);
        if($result != false){
            if($result->rowCount() > 0){
				$opcion_defaul = 'Seleccione Acuerdo...';
				$html = "<option value=''>".utf8_encode($opcion_defaul)."</option>";
                while(!$result->EOF){
					if($result->fields["IDCuota"]==$pagosObj->IDCuota){
						$html .= "<option value='".$result->fields["IDCuota"]."' selected>".$result->fields["IDAcuerdo"]." - ".$result->fields["Cuota"]." - ".$result->fields["Abreviado"]." - ".$result->fields["FechaCuota"]." - ".number_format($result->fields["ValorCuota"])."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDCuota"]."'>".$result->fields["IDAcuerdo"]." - ".$result->fields["Cuota"]." - ".$result->fields["Abreviado"]." - ".$result->fields["FechaCuota"]." - ".number_format($result->fields["ValorCuota"])."</option>";
					}
					$result->MoveNext();
                }
            }else{
				$opcion_defaul = 'No se encontraron Acuerdos vigentes';
				$html = "<option value=''>".utf8_encode($opcion_defaul)."</option>";
			}
        }
        return $html;
    }
	
	/**
     * Nota Debito de un Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_NotaDebito_Pago($pagosObj,$clienteObj,$SessionObj){
		
		// Anula el pago prendiendo el bit de Anulado y creando un pago Negativo
		$result = $this->BR_Anular_Pago($pagosObj,$clienteObj,$SessionObj);
		
		if($result != false){
			
			// Crea una nota debito siempre y cuando el pago se encuentre facturado
			$result = $this->dacU->DAU_NotaDebito_Pago($pagosObj);
			if($result != false){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Nota Debito de un Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_NotaCredito_Pago($pagosObj,$clienteObj,$SessionObj){
		
		// Anula el pago prendiendo el bit de Anulado y creando un pago Negativo
		$result = $this->BR_Anular_Pago($pagosObj,$clienteObj,$SessionObj);

		if($result != false){
			// Crea una nota debito siempre y cuando el pago se encuentre facturado
			$result_nota = $this->dacU->DAU_NotaCredito_Pago($pagosObj);
			if($result_nota != false){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Restituir una Cuota anulada
     * por el Campo @IDAval, @NroTitulo
     *
     * @access public
     * @return array
     */
	public function BR_Restituir_Cuota($facturacionufegObj,$SessionObj){
		$result = $this->dacU->DAU_Restituir_Cuota($facturacionufegObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Reliquidar una Cuota 
     * por el Campo @IDCtrlFactUFEGCambio, @IDCtrlFactUFEG
     *
     * @access public
     * @return array
     */
	public function BR_Reliquidar_Cuota($factufegcambiosObj,$SessionObj){
		$result = $this->dacU->DAU_Reliquidar_Cuota($factufegcambiosObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Notas Credito Cuota Ufeg Facturada
     * por el Campo @IDCtrlFactUFEG
     *
     * @access public
     * @return array
     */
	public function BR_NotaCredito_Cuota_Ufeg($facturacionufegObj,$factufegcambiosObj,$SessionObj){
		$result = $this->dacU->DAU_NotaCredito_Cuota_Ufeg($facturacionufegObj,$factufegcambiosObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
	* Consulta a la tabla MotivoNotaxTipoDocumento
	* por el campo IDPais
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionMotivoNotaxTipoDocumento(){
       $result = $this->dac->DA_ConsultarTablaMotivoNotaxTipoDocumento();
        $html = "<option value=''>Seleccione Motivo Nota...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IdRegistro"]."'>".$result->fields["Nombre"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
     * Enlazar Cheques devueltos
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Enlazar_Cheque($pagosObj){
        $result = $this->dacU->DAU_Enlazar_Cheque($pagosObj);
        if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Canjear un Cheque
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Canje_Cheque($pagosObj){
        $result = $this->dacU->DAU_Canje_Cheque($pagosObj);
        if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Credito
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxCredito($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxCredito($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Acuerdo
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxAcuerdo($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxAcuerdo($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de cheques por Fecha de Pago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarChequexFechaPago($pagosObj){
        $result = $this->dac->DA_ConsultarChequexFechaPago($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de Pagos sin Asociar por Fecha de Pago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoSinAsociarxFechaPago($pagosObj){
        $result = $this->dac->DA_ConsultarPagoSinAsociarxFechaPago($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de Pagos por IDCliente
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxIDCliente($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxIDCliente($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de Pagos Por Asociar
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagoxAsociar($pagosObj){
        $result = $this->dac->DA_ConsultarPagoxAsociar($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion de Saldos a Favor
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarSaldosaFavor($pagosObj){
        $result = $this->dac->DA_ConsultarSaldosaFavor($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion traslado Tesoreria
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarTrasladoTesoreria($pagosObj){
        $result = $this->dac->DA_ConsultarTrasladoTesoreria($pagosObj);
		return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por IdPersonaLince
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarCuotaFacturarxIdPersonaLince($fUfegObj){
        $result = $this->dac->DA_ConsultarCuotaFacturarxIdPersonaLince($fUfegObj);
		return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por Identificacion Cliente
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarCuotaFacturarxIdentificacioncliente($fUfegObj){
        $result = $this->dac->DA_ConsultarCuotaFacturarxIdentificacioncliente($fUfegObj);
		return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por Identificacion Comercio
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarCuotaFacturarxIdentificacioncomercio($fUfegObj){
        $result = $this->dac->DA_ConsultarCuotaFacturarxIdentificacioncomercio($fUfegObj);
		return $result;
    }
	
	/**
	* Consulta Opciones de Fraccionar un Pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionFraccionarPago(){
       $result = $this->dac->DA_ConsultarOpcionFraccionarPago();
        $html = "<option value=''>Seleccione Fraccion Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IdFraccion"]."'>".$result->fields["Fraccion"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Tipo de Busqueda Pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionBusqueda($IDAplicacion){
       $result = $this->dac->DA_ConsultarOpcionBusqueda($IDAplicacion);
        $html = "<option value=''>Seleccione Tipo Busqueda...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IDTipoBusqueda"]."'>".$result->fields["TipoBusqueda"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Tipo de Aplicacion
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionAplicacion(){
       $result = $this->dac->DA_ConsultarOpcionAplicacion();
	   $opcion_defaul = 'Seleccione Negocio...';
        $html = "<option value=''>".utf8_encode($opcion_defaul)."</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IdAplicacion"]."'>".$result->fields["Nombre"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Tipo origen pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionTipoOrigenPago($pagosObj){
       $result = $this->dac->DA_ConsultarTipoOrigenPago();
        $html = "<option value=''>Seleccione Tipo Origen Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IdTipoOrigenPago"]==$pagosObj->IDTipoOrigenPago){
						$html .= "<option value='".$result->fields["IdTipoOrigenPago"]."' selected>".$result->fields["TipoOrigenPago"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IdTipoOrigenPago"]."'>".$result->fields["TipoOrigenPago"]."</option>";
					}
					$result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Tipo origen pago Devolucion
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionTipoOrigenPago_Devolucion($pagosObj){
       $result = $this->dac->DA_ConsultarTipoOrigenPago_Devolucion();
        $html = "<option value=''>Seleccione Tipo Origen Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IdTipoOrigenPago"]==$pagosObj->IDTipoOrigenPago){
						$html .= "<option value='".$result->fields["IdTipoOrigenPago"]."' selected>".$result->fields["TipoOrigenPago"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IdTipoOrigenPago"]."'>".$result->fields["TipoOrigenPago"]."</option>";
					}
					$result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Estado pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionTipoEstadoPago($pagosObj){
       $result = $this->dac->DA_ConsultarEstadoPago();
        $html = "<option value=''>Seleccione Estado Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IDEstadoPago"]==$pagosObj->IDEstadoPago){
						$html .= "<option value='".$result->fields["IDEstadoPago"]."' selected>".$result->fields["EstadoPago"]." - Aplicable ".$result->fields["Aplicable"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDEstadoPago"]."'>".$result->fields["EstadoPago"]." - Aplicable ".$result->fields["Aplicable"]."</option>";
					}
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Moneda pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionMonedaPago($pagosObj){
       $result = $this->dac->DA_ConsultarMonedaPago();
        $html = "<option value=''>Seleccione Moneda Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IDMoneda"]==$pagosObj->IDMoneda){
						$html .= "<option value='".$result->fields["IDMoneda"]."' selected>".$result->fields["Moneda"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDMoneda"]."'>".$result->fields["Moneda"]."</option>";
					}
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Banco pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionBancoPago($pagosObj){
       $result = $this->dac->DA_ConsultarBancoPago($pagosObj);
        $html = "<option value=''>Seleccione Banco Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["Banco"]==$pagosObj->Banco){
						$html .= "<option value='".$result->fields["Banco"]."' selected>".$result->fields["Descripcion"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["Banco"]."'>".$result->fields["Descripcion"]."</option>";
					}
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Combo Numero cuenta Banco pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionNCuentaPago($pagosObj){
       $result = $this->dac->DA_ConsultarNCuentaPago($pagosObj);
        $html = "<option value=''>Seleccione NroCuenta Pago...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["NroCuenta"]==$pagosObj->NroCuenta){
						$html .= "<option value='".$result->fields["NroCuenta"]."' selected>".$result->fields["NroCuenta"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["NroCuenta"]."'>".$result->fields["NroCuenta"]."</option>";
					}
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
	* Consulta Tipo de Busqueda Pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboOpcionBusquedaCliente(){
       $result = $this->dac->DA_ConsultarOpcionBusquedaCliente();
        $html = "<option value=''>Seleccione Tipo Busqueda...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
                    $html .= "<option value='".$result->fields["IDTipoBusqueda"]."'>".$result->fields["TipoBusqueda"]."</option>";
                    $result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
     * Crear Nuevo Pago
     * Cambiando valor, IDTipo Origen Pago
     *
     * @access public
     * @return array
     */
	public function BR_Crear_Pago($pagosObj,$clienteObj,$SessionObj){
		$result = $this->dacU->DAU_Crear_Pago($pagosObj,$clienteObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Anular un Pago, realizando la marca de anulado del mismo, y creando un pago Negativo
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Anular_Pago($pagosObj,$clienteObj,$SessionObj){
		$result = $this->dacU->DAU_Anular_Pago($pagosObj,$clienteObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Editar un Pago de la tabla Pago
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_Editar_Pago($pagosObj){
        $result = $this->dacU->DAU_Editar_Pago($pagosObj);
        if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Devolucion Partidas
     * por el Campo IDPago
     *
     * @access public
     * @return array
     */
	public function BR_DevolucionPPI_Pago($pagosObj,$SessionObj){
		$result = $this->dacU->DAU_DevolucionPPI_Pago($pagosObj,$SessionObj);
        if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
	* Consulta Distribucion de Pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ConsultaDistribucionPagos($TipoDistribucion){
       $result = $this->dac->DA_ConsultaDistribucionPagos($TipoDistribucion);
        if($result->rowCount() > 0){
            return $result;
        }
        else{
            return false;
        }
    }
	
	/**
	* Consulta Tipo distribucion pago
	* Información General
	*
	* @access public
	* @return array
	*/
	public function BR_ComboTipoDistribucionPago($IDTDistribucionP){
       $result = $this->dac->DA_ConsultarTipoDistribucionPago();
        $html = "<option value=''>Seleccione Tipo Distribucion...</option>";
        if($result != false){
            if($result->rowCount() > 0){
                while(!$result->EOF){
					if($result->fields["IDTDistribucionP"]==$IDTDistribucionP){
						$html .= "<option value='".$result->fields["IDTDistribucionP"]."' selected>".$result->fields["Nombre"]."</option>";
                    }else{
						$html .= "<option value='".$result->fields["IDTDistribucionP"]."'>".$result->fields["Nombre"]."</option>";
					}
					$result->MoveNext();
                }
            }
        }
        return $html;
    }
	
	/**
     * Crear Nueva distribucion
     * Creando nuevos registros y desactivando los anteriores
     *
     * @access public
     * @return array
     */
	public function BR_Crear_Distribucion($PagoDistribucionObj,$SessionObj){
		$result = $this->dacU->DAU_Crear_Distribucion($PagoDistribucionObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Consultar si un pago cuenta con Distribucion
     * Información de distribución del pago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaDistribucionxPago($IDPago){
	$result = $this->dac->DA_ConsultaDistribucionxPago($IDPago);
        if($result->rowCount() > 0){
            return $result;
        }
        else{
            return false;
        }
	}
	
	/**
     * Consultar distribucion activa x IDDistribucion
     * Información general
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaDistribucionxIDDistribucion($DistribucionpagoObj){
	$result = $this->dac->DA_ConsultaDistribucionxIDDistribucion($DistribucionpagoObj);
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
				return false;
			}
	}
	
	/**
     * Consultar el Tipo de Distribucion por IDPago
     * Información de distribución del pago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaTipoDistribucionxPago($pagosObj){
	$result = $this->dac->DA_ConsultaTipoDistribucionxPago($pagosObj);
        if($result->rowCount() > 0){
            return $result->fields;
        }
        else{
            return false;
        }
	}
	
	/**
     * Consultar tipo de Distribucion por IDDistribucion
     * Información de distribución del pago
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaTipoDistribucionxIDDistribucion($IDDistribucion){
	$result = $this->dac->DA_ConsultaTipoDistribucionxIDDistribucion($IDDistribucion);
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
     * Consulta partidas pendientes por identificar
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPPI(){
        $result = $this->dac->DA_CantidadPPI();
        if($result->rowCount() > 0){
            return $result->fields['PPI'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta total de pagos Npls
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPagosNpls(){
        $result = $this->dac->DA_CantidadPagosNpls();
        if($result->rowCount() > 0){
            return $result->fields['PagosNpls'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta total de pagos Avales
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_CantidadPagosAvales(){
        $result = $this->dac->DA_CantidadPagosAvales();
        if($result->rowCount() > 0){
            return $result->fields['PagosNpls'];
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta total de pagos Avales con Distribucion
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarPagosAvalesDistribucion(){
        $result = $this->dac->DA_ConsultarPagosAvalesDistribucion();
        if($result->rowCount() > 0){
            return $result;
        }
        else{
            return 0;
        }
    }
	
	/**
     * Consulta registros de Cuotas a facturar en la Ufeg
     * contar
     *
     * @access public
     * @return array
     */
    public function BR_RegistrosCuotaFacturar(){
        $result = $this->dac->DA_RegistrosCuotaFacturar();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Autorizar Pre-Factura Ufeg
     * Marcando Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Autorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj){
		$result = $this->dacU->DAU_Autorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Autorizar Pre-Factura Npls
     * Marcando Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Autorizar_PreFactura_Npls($prefclienteObj,$SessionObj){
		$result = $this->dacU->DAU_Autorizar_PreFactura_Npls($prefclienteObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Autorizar todo Pre-Factura Npls
     * Marcando Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Autorizar_todo_PreFactura_Npls($accion,$SessionObj){
		$result = $this->dacU->DAU_Autorizar_todo_PreFactura_Npls($accion,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Autorizar todo Pre-Factura Ufeg
     * Marcando Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Autorizar_todo_PreFactura_Ufeg($accion,$SessionObj){
		$result = $this->dacU->DAU_Autorizar_todo_PreFactura_Ufeg($accion,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Autorizar todo Pre-Factura Avales
     * Marcando Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Autorizar_todo_PreFactura_Avales($accion,$SessionObj){
		$result = $this->dacU->DAU_Autorizar_todo_PreFactura_Avales($accion,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Desautorizar Pre-Factura Ufeg
     * Desautorizar Registros Autorizados
     *
     * @access public
     * @return array
     */
	public function BR_Desautorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj){
		$result = $this->dacU->DAU_Desautorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
		if($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Consulta ultima facturacion en Dashboard
     * información especifica
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarUltimaFacturacionDashboard($IDAplicacion){
        $result = $this->dac->DA_ConsultarUltimaFacturacionDashboard($IDAplicacion);
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta informacion de aplicacion en general
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_ConsultarOpcionAplicacion(){
        $result = $this->dac->DA_ConsultarOpcionAplicacion();
		if ($result != false){
			if($result->rowCount() > 0){
				return $result;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta ultima facturacion en Dashboard
     * información general
     *
     * @access public
     * @return array
     */
    public function BR_ConsultaUInfoFactDashboard($IDAplicacion){
        $result = $this->dac->DA_ConsultaUInfoFactDashboard($IDAplicacion);
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Ejecutar procesos Ini NPls
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_EjecutarProcesoFacturacionNpls(){
        $result = $this->dac->DA_EjecutarProcesoFacturacionNpls();
		if ($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Ejecutar procesos Ini Ufeg
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_EjecutarProcesoFacturacionUfeg(){
        $result = $this->dac->DA_EjecutarProcesoFacturacionUfeg();
		if ($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Ejecutar procesos Ini Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
    public function BR_EjecutarProcesoFacturacionAvales(){
        $result = $this->dac->DA_EjecutarProcesoFacturacionAvales();
		if ($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Ejecutar procesos Ini Negocio
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function BR_EjecutarProcesoFacturacion($IDAplicacion){
		if($IDAplicacion==1){
			$result = $this->dac->DA_EjecutarProcesoFacturacionNpls();
			if ($result != false){
				return true;
			}else{
				return false;
			}				
		}
		
		if($IDAplicacion==2){
			$result = $this->dac->DA_EjecutarProcesoFacturacionUfeg();
			if ($result != false){
				return true;
			}else{
				return false;
			}				
		}
		
		if($IDAplicacion==3){
			$result = $this->dac->DA_EjecutarProcesoFacturacionAvales();
			if ($result != false){
				return true;
			}else{
				return false;
			}				
		}
    }
	
	/**
     * Ejecutar procesos Ini Negocio
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function BR_Crear_Programacion_Factura($pfacturaObject){
		$result = $this->dacU->DAU_Crear_Programacion_Factura($pfacturaObject);
		if ($result != false){
			return true;
		}else{
			return false;
		}
    }
	
	/**
     * Ejecutar procesos Ini Negocio
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function BR_ConsultarProgramacion_Factura($pfacturaObject){
		$result = $this->dac->DA_ConsultarProgramacion_Factura($pfacturaObject);
		if ($result != false){
			if($result->rowCount() > 0){
				return $result->fields;
			}
			else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/**
     * Consulta los Modulos por Perfil asociados a la aplicación
     * por el campo id_perfil
     *
     * @access public
     * @return array
    */
    public function BR_ConsultarModuloxId_Perfil($SessionObj){
        $result = $this->dac->DA_ConsultarModuloxId_Perfil($SessionObj);
		if($result !=false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }

	/**
     * Consulta las opciones  por modulo  asociados al perfil
     * por el campo id_modulo
     *
     * @access public
     * @return array
    */
    public function BR_ConsultarOpcionesxId_Modulo($ModuloOpcionObj){
        $result = $this->dac->DA_ConsultarOpcionesxId_Modulo($ModuloOpcionObj);
		if($result!=false){
			if($result->rowCount() > 0){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
	
	/*------------------------------------------------ Funciones Especiales-----------------------------------------------------------*/
	/*
     * Validar si una URL HTTP devuelve algo o existe
     */
	public function BR_urlexists($url){
		if( empty( $url ) ){
			return false;
		}

		// get_headers() realiza una petición GET por defecto
		// cambiar el método predeterminadao a HEAD
		stream_context_set_default(
			array(
				'http' => array(
					'method' => 'HEAD'
				 )
			)
		);
		$headers = @get_headers( $url );
		sscanf( $headers[0], 'HTTP/%*d.%*d %d', $httpcode );

		//Aceptar solo respuesta 200 (Ok), 301 (redirección permanente) o 302 (redirección temporal)
		$accepted_response = array( 200, 301, 302, 403 );
		if( in_array( $httpcode, $accepted_response ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
     * Asignación de Fotografia a Usuario Logueado
     */
	public function BR_AsignandoFotografiaUs($identificacion){
		
		/* definiendo parametro URL_Intranet2 (P) Parametro (R) Result (V) Variable */
		$P_url_intranet2		=	'URL_Intranet2';
		$R_url_intranet2		=	$this->BR_ConsultartablaTbparametros($P_url_intranet2);
		
		/* definiendo parametro UFotos_Funcionarios (P) Parametro (R) Result (V) Variable */
		$P_ufotosf				=	'UFotos_Funcionarios';
		$R_ufotosf				=	$this->BR_ConsultartablaTbparametros($P_ufotosf);
				
		/* definiendo url de fotografias Us */
		$urlfotodefault			=	'./images/user.png';
		$urlfotoidentificacion	=	$R_url_intranet2.'/'.$R_ufotosf.'/'.$identificacion.'.jpg';
		
		/* Verificando si el us cuenta con fotografia en el directorio de telefono */
		$urlfotoexists 			= $this->BR_urlexists($urlfotoidentificacion);
		
		/* en caso de que no cuente con fotografia asignar la defaults */
		if($urlfotoexists==false){
			return $urlfotodefault;
		}else{
			return $urlfotoidentificacion;
		}
	}
	
	
	/*
     * Generación de Errores Generales de la Aplicación la función esta creada en generalFunctions.php se encuentra en Libs
    */
	public function BR_ErrorGeneral($ErrorObj){
		$htmlerror  = FN_ErrorGeneral($ErrorObj);
		return $htmlerror;
	}
	
	/**
     * Guardar en la tabla tbLogEComercial de la base de datos SAB2Logs
     * Información General
     *
     * @access public
     * @return array
     */
	public function BR_GuardartbLog($LogObj){
		$result = $this->dacU->DAU_GuardartbLog($LogObj);
		if($result != false){
			return $result;
		}else{
			return false;
		}
	}
}
?>