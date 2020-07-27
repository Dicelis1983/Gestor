<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
// Configuracion de ambiente
require_once($_SERVER['DOCUMENT_ROOT']."/GOperativo/Config/enviroment.php");

require_once(CORE.'/Libs/adodb5/adodb.inc.php');
require_once(CORE_INSTANCE."/DataAcces/ConnectionObject.php");
require_once(CORE_INSTANCE."/DataAcces/EntityObjects.php");

// Implementar la interfaz de acceso a Consulta de Datos
// Ésto funcionará 
class DataAccesClassUpdate implements iDataAccesUpdate
{
	public $cadenaConexion="";
	public $consulta="";
	
	public function DAconexion_db($conObj) {
        try{
            $Driver = base64_decode($conObj->Driver);
            $Server = base64_decode($conObj->Server);
            $Db = base64_decode($conObj->Db);
            $User = base64_decode($conObj->User);
            $Pass = base64_decode($conObj->Pass);
            $this->cadenaConexion = NewADOConnection($Driver);
            $this->cadenaConexion->PConnect($Server, $User, $Pass, $Db);
            $this->cadenaConexion->execute("SET NAMES 'utf8'");
            return $this->cadenaConexion;
        }
        catch(Exception $error){
            return "Error";
        }
    }
	
	/**
     * Consulta a la tabla usuario
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DAU_ActualizartablaSesion($SessionObj){
        $var1 = $this->cadenaConexion->Param('IDSesion');
		$var2 = $this->cadenaConexion->Param('IDUsuario');
		$var3 = $this->cadenaConexion->Param('Tocken');
		$var4 = $this->cadenaConexion->Param('Logueado');
        $sql = "UPDATE tbSesiones SET Logueado=$var4 WHERE IDSesion=$var1 AND IDUsuario=$var2 AND Tocken like '$var3';";
        $bindVars = array($SessionObj->IDSesion, $SessionObj->IDUsuario, $SessionObj->Tocken,$SessionObj->Logueado);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Insertar registro de auditoria
     * Información General
     *
     * @access public
     * @return object
     */
    public function DAU_GuardartbLog($LogObj){
		$var1 = $this->cadenaConexion->Param('Tabla');
		$var2 = $this->cadenaConexion->Param('IDTabla');
		$var3 = $this->cadenaConexion->Param('TipoMovimiento');
		$var4 = $this->cadenaConexion->Param('IDRegistro');
		$var5 = $this->cadenaConexion->Param('Dato');
		$var6 = $this->cadenaConexion->Param('IDUsuario');
		$var7 = $this->cadenaConexion->Param('Usuario');
        $sql = "INSERT INTO [SAB2Logs].[Gop].[LogGestorOperativo](Tabla,IDTabla,TipoMovimiento,IDRegistro,Dato,IDUsuario,Usuario) VALUES ($var1,$var2,$var3,$var4,$var5,$var6,$var7);";
        $bindVars = array($LogObj->Tabla, $LogObj->IDTabla,$LogObj->TipoMovimiento,$LogObj->IDRegistro,ucwords(strtolower($LogObj->Dato)),$LogObj->IDUsuario,$LogObj->Usuario);
		$result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($sql);
        return $result;
    }
	
	/**
     * Anula un pago, si este pago es del periodo anterior o está facturado, en caso contrario no hace nada
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Anular_Pago($pagosObj,$clienteObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDCliente');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$p3 = $this->cadenaConexion->Param('IDPago');
		$p4 = $this->cadenaConexion->Param('IDTipoOrigenPago');
		$p5 = $this->cadenaConexion->Param('Detalles');
		$sql = "EXEC SP_Anular_Pago @IDCliente=$p1,@IDUsuario=$p2,@IDPago=$p3,@IDTipoOrigenPago=$p4,@Observaciones=$p5;";
        $bindVars = array($clienteObj->IDCliente,$SessionObj->IDUsuario,$pagosObj->IDPago,$pagosObj->IDTipoOrigenPago,$pagosObj->Detalles);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Desasocia un Pagp, si este pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Desasociar_Pago($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$sql = "EXEC SP_Desasociar_Pago @IDPago=$p1;";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Asociar Pago de la  la tabla Pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Asociar_Pago($pagosObj,$clienteObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDCliente');
		$p3 = $this->cadenaConexion->Param('IDPortafolio');
		$sql = "EXEC SP_Asociar_Pago @IDPago=$p1,@IDCliente=$p2,@IDPortafolio=$p3;";
        $bindVars = array($pagosObj->IDPago,$clienteObj->IDCliente,$pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Aplicar Pago en la  la tabla Pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Aplicar_Pago($pagosObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDRefinancia');
		$p3 = $this->cadenaConexion->Param('IDCuota');
		$p4 = $this->cadenaConexion->Param('IDUsuario');
		$p5 = $this->cadenaConexion->Param('IDPortafolio');
		$sql = "EXEC SP_Aplicar_Pago @IDPago=$p1,@IDRefinancia=$p2,@IDCuota=$p3,@IDUsuario=$p4,@IDPortafolio=$p5;";
        $bindVars = array($pagosObj->IDPago,$pagosObj->IDRefinancia,$pagosObj->IDCuota,$SessionObj->IDUsuario,$pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Nota Debito de un Pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_NotaDebito_Pago($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDCodigoMotivoNota');
		$sql = "EXEC SP_NotaDebito_Pago @IDPago=$p1,@IDMotivoNota=$p2;";
        $bindVars = array($pagosObj->IDPago,$pagosObj->IDCodigoMotivoNota);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Nota Debito de un Pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_NotaCredito_Pago($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDCodigoMotivoNota');
		$sql = "EXEC SP_NotaCredito_Pago @IDPago=$p1,@IDMotivoNota=$p2;";
        $bindVars = array($pagosObj->IDPago,$pagosObj->IDCodigoMotivoNota);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Restituir una Cuota anulada
     * por el Campo @IDAval, @NroTitulo
     *
     * @access public
     * @return object
     */
    public function DAU_Restituir_Cuota($facturacionufegObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('PeriodoFacturacion');
		$p2 = $this->cadenaConexion->Param('IDAval');
		$p3 = $this->cadenaConexion->Param('NroTitulo');
		$p4 = $this->cadenaConexion->Param('TipoFacturacion');
		$sql = "EXEC [FacturacionElectronica].[Ctrl].[spProcesarFacturacionUFEG] @Periodo=$p1, @IDAval=$p2, @NroTitulo=$p3, @Tipo=$p4;";
        $bindVars = array($facturacionufegObj->PeriodoFacturacion,$facturacionufegObj->IDAval,$facturacionufegObj->NroTitulo,$facturacionufegObj->TipoFacturacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Reliquidar una Cuota 
     * por el Campo @IDCtrlFactUFEGCambio, @IDCtrlFactUFEG
     *
     * @access public
     * @return object
     */
    public function DAU_Reliquidar_Cuota($factufegcambiosObj,$SessionObj){
		//print_r($factufegcambiosObj);
		$p1 = $this->cadenaConexion->Param('IDCtrlFactUFEGCambio');
		$p2 = $this->cadenaConexion->Param('IDCtrlFactUFEG');
		$p3 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spReliquidarCuotaUFEG] @IDCtrlFactUFEGCambio=$p1, @IDCtrlFactUFEG=$p2, @IDUsuario=$p3;";
        $bindVars = array($factufegcambiosObj->IDCtrlFactUFEGCambio,$factufegcambiosObj->IDCtrlFactUFEG,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Notas Credito Cuota Ufeg Facturada
     * por el Campo @IDCtrlFactUFEGCambio, @IDCtrlFactUFEG, @IDUsuario, @IDMotivoNota
     *
     * @access public
     * @return object
     */
    public function DAU_NotaCredito_Cuota_Ufeg($facturacionufegObj,$factufegcambiosObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDCtrlFactUFEGCambio');
		$p2 = $this->cadenaConexion->Param('IDCtrlFactUFEG');
		$p3 = $this->cadenaConexion->Param('IDUsuario');
		$p4 = $this->cadenaConexion->Param('IDMotivoNota');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spNotaCreditoCuotaUFEG] @IDCtrlFactUFEGCambio=$p1, @IDCtrlFactUFEG=$p2, @IDUsuario=$p3, @IDMotivoNota=$p4;";
        $bindVars = array($factufegcambiosObj->IDCtrlFactUFEGCambio,$facturacionufegObj->IDCtrlFactUFEG,$SessionObj->IDUsuario,$facturacionufegObj->IDCodigoMotivoNota);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Enlazar Cheques devueltos
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Enlazar_Cheque($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDPagoAsociado');
		$sql = "EXEC SP_Enlazar_Cheque @IDPago=$p1,@IDPagoAsociado=$p2;";
        $bindVars = array($pagosObj->IDPago,$pagosObj->IDPagoAsociado);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Canjear un Cheque
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Canje_Cheque($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$sql = "EXEC SP_Canjear_Cheque @IDPago=$p1;";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Crear Nuevo Pago
     * Cambiando valor, IDTipoOrigen Pago
     *
     * @access public
     * @return object
     */
    public function DAU_Crear_Pago($pagosObj,$clienteObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDCliente');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$p3 = $this->cadenaConexion->Param('IDPago');
		$p4 = $this->cadenaConexion->Param('IDTipoOrigenPago');
		$p5 = $this->cadenaConexion->Param('Valor');
		$p6 = $this->cadenaConexion->Param('IDPortafolio');
		$sql = "EXEC SP_Crear_Pago @IDCliente=$p1,@IDUsuario=$p2,@IDPago=$p3,@IDTipoOrigenPago=$p4,@Valor=$p5,@IDPortafolio=$p6;";
        $bindVars = array($clienteObj->IDCliente,$SessionObj->IDUsuario,$pagosObj->IDPago,$pagosObj->IDTipoOrigenPago,$pagosObj->Valor,$pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Editar un Pago de la tabla Pago
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_Editar_Pago($pagosObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDTipoOrigenPago');
		$p3 = $this->cadenaConexion->Param('IDEstadoPago');
		$p4 = $this->cadenaConexion->Param('IDMoneda');
		$p5 = $this->cadenaConexion->Param('IDPortafolio');
		$p6 = $this->cadenaConexion->Param('Banco');
		$p7 = $this->cadenaConexion->Param('NroCuenta');
		$p8 = $this->cadenaConexion->Param('Valor');
		$p9 = $this->cadenaConexion->Param('Detalles');
		$sql = "EXEC SP_Editar_Pago @IDPago=$p1,@IDTipoOrigenPago=$p2,@IDEstadoPago=$p3,@IDMoneda=$p4,@IDPortafolio=$p5,@Banco=$p6,@NroCuenta=$p7,@Valor=$p8,@Detalles=$p9;";
        $bindVars = array($pagosObj->IDPago,$pagosObj->IDTipoOrigenPago,$pagosObj->IDEstadoPago,$pagosObj->IDMoneda,$pagosObj->IDPortafolio,$pagosObj->Banco,$pagosObj->NroCuenta,$pagosObj->Valor,$pagosObj->Detalles);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Devolucion, Permite cambiar el Tipo origen 
     * por medio de su PK id_pago
     *
     * @access public
     * @return object
     */
    public function DAU_DevolucionPPI_Pago($pagosObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDUsuario');
		$p2 = $this->cadenaConexion->Param('IDPago');
		$p3 = $this->cadenaConexion->Param('IDTipoOrigenPago');
		$p4 = $this->cadenaConexion->Param('Detalles');
		$sql = "EXEC SP_Devolucion_PPI_Pago @IDUsuario=$p1,@IDPago=$p2,@IDTipoOrigenPago=$p3,@Observaciones=$p4;";
        $bindVars = array($SessionObj->IDUsuario,$pagosObj->IDPago,$pagosObj->IDTipoOrigenPago,$pagosObj->Detalles);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Crear Nueva distribucion
     * Creando nuevos registros y desactivando los anteriores
     *
     * @access public
     * @return object
     */
    public function DAU_Crear_Distribucion($PagoDistribucionObj,$SessionObj){
		$p1 = $this->cadenaConexion->Param('IDPago');
		$p2 = $this->cadenaConexion->Param('IDDistribucion');
		$p3 = $this->cadenaConexion->Param('Valor');
		$p4 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC SP_Crear_DistribucionPago @IDPago=$p1,@IDDistribucion=$p2,@Valor=$p3,@IDUsuario=$p4;";
        $bindVars = array($PagoDistribucionObj->IDPago,$PagoDistribucionObj->IDDistribucion,$PagoDistribucionObj->Valor,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Autorizar Pre-Factura Ufeg
     * Marcando Registros Autorizados
     *
     * @access public
     * @return object
     */
    public function DAU_Autorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('IdFacturaCliente_UfegPre');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spAutorizarPreFacturaUFEG] @IdFacturaCliente_UfegPre=$p1,@IDUsuario=$p2;";
        $bindVars = array($prefUfegObj->IdFacturaCliente_UfegPre,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Autorizar Pre-Factura Npls
     * Marcando Registros Autorizados
     *
     * @access public
     * @return object
     */
    public function DAU_Autorizar_PreFactura_Npls($prefclienteObj,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('IdFacturaClientePre');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spAutorizarPreFacturaNPls] @IdFacturaClientePre=$p1,@IDUsuario=$p2;";
        $bindVars = array($prefclienteObj->IdFacturaClientePre,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Autorizar Pre-Factura Npls
     * Marcando Registros Autorizados
     *
     * @access public
     * @return object
     */
    public function DAU_Autorizar_todo_PreFactura_Npls($accion,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('Accion');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spAutorizarTodoPreFacturaNPls] @Accion=$p1,@IDUsuario=$p2;";
        $bindVars = array($accion,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Autorizar Pre-Factura Ufeg
     * Marcando Registros Autorizados
     *
     * @access public
     * @return object
     */
    public function DAU_Autorizar_todo_PreFactura_Ufeg($accion,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('Accion');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spAutorizarTodoPreFacturaUfeg] @Accion=$p1,@IDUsuario=$p2;";
        $bindVars = array($accion,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Autorizar Pre-Factura Avales
     * Marcando Registros Autorizados
     *
     * @access public
     * @return object
     */
    public function DAU_Autorizar_todo_PreFactura_Avales($accion,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('Accion');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spAutorizarTodoPreFacturaAvales] @Accion=$p1,@IDUsuario=$p2;";
        $bindVars = array($accion,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Desautorizar Pre-Factura Ufeg
     * Marcando Registros Desautorizar
     *
     * @access public
     * @return object
     */
    public function DAU_Desautorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj){
		//print_r($prefUfegObj);
		$p1 = $this->cadenaConexion->Param('IdFacturaCliente_UfegPre');
		$p2 = $this->cadenaConexion->Param('IDUsuario');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spDesautorizarPreFacturaUFEG] @IdFacturaCliente_UfegPre=$p1,@IDUsuario=$p2;";
        $bindVars = array($prefUfegObj->IdFacturaCliente_UfegPre,$SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Crear Progamacion Factura
     * Por IDAplicacion, FechaInicioProgramada
     *
     * @access public
     * @return object
     */
    public function DAU_Crear_Programacion_Factura($pfacturaObject){
		$p1 = $this->cadenaConexion->Param('IDAplicacion');
		$sql = "EXEC [FacturacionElectronica].[dbo].[spCrear_Programacion_Factura] @IDAplicacion = $p1, @FechaInicioProgamada=NULL;";
        $bindVars = array($pfacturaObject->IDAplicacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
}
?>