<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
require_once($_SERVER['DOCUMENT_ROOT']."/GOperativo/Config/enviroment.php");
require_once(CORE.'/Libs/adodb5/adodb.inc.php');
require_once(CORE_INSTANCE."/DataAcces/ConnectionObject.php");
require_once(CORE_INSTANCE."/DataAcces/EntityObjects.php");

// Implementar la interfaz de acceso a Consulta de Datos
class DataAccesClass implements iDataAccesQuery {

	public $cadenaConexion="";
	public $consulta="";

	public function DAconexion_db($conObj){
        try{
            $Driver = base64_decode($conObj->Driver);
            $Server = base64_decode($conObj->Server);
            $Db = base64_decode($conObj->Db);
            $User = base64_decode($conObj->User);
            $Pass = base64_decode($conObj->Pass);
            $this->cadenaConexion = NewADOConnection($Driver);
            $this->cadenaConexion->PConnect($Server, $User, $Pass, $Db);
            return $this->cadenaConexion;
        }
        catch(Exception $error){
            return "Error";
        }
    }
	
	 /**
     * Consulta a las tablas Por medio de un CRUD
     *
     * @access public
     * @return
     */
	public function DADatosCRUD($sql){
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
    /**
     * Consulta a la tabla Funcionarios
     * por los campos id_usuario, Retirado, Bloqueado, Rol
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarUsuarioSesion($SessionObj){
        $p1 = $this->cadenaConexion->Param('IDUsuario');
		$p2 = $this->cadenaConexion->Param('IDAplicacion');
        $sql = "EXEC [Refinancia].[dbo].[sp_Consultar_Usuario_Autorizado] @IDUsuario=$p1, @IDAplicacion=$p2;";
        $bindVars = array($SessionObj->IDUsuario,$SessionObj->IDAplicacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta a la tabla Funcionarios y tbUsuarioAplicacion
     * por el campo id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarUsuarioxIDUsuario($SessionObj){
		$p1 = $this->cadenaConexion->Param('IDUsuario');
		$p2 = $this->cadenaConexion->Param('IDAplicacion');
        $sql = "EXEC [Refinancia].[dbo].[sp_Consultar_UsuarioxId_Usuario] @IDUsuario=$p1, @IDAplicacion=$p2;";
        $bindVars = array($SessionObj->IDUsuario,$SessionObj->IDAplicacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta a la tabla Funcionarios
     * por el campo id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarFuncionarioxIDUsuario($SessionObj){
		$p1 = $this->cadenaConexion->Param('IDUsuario');
        $sql = "Select
				F.IDUsuario
				,F.TipoDocumento
				,F.Identificacion
				,F.Nombre+' '+Apellidos as Usuario
				,F.EMail
				,F.Bloqueado
				,C.IDCargo
				,C.Cargo
				,C.Rol
				,S.Sede
				,S.Pais
				,convert(varchar,F.FechaIngreso,103) as FechaIngreso
				,isnull(convert(varchar,F.FechaRetiro,103),'No Registra') as FechaRetiro
				,FE.Nombre as Estado
				FROM Refinancia.dbo.Funcionarios				AS F	WITH(NOLOCK)
				INNER JOIN Refinancia.dbo.Cargos				AS C	WITH(NOLOCK)		ON C.IDCargo=F.IDCargo
				INNER JOIN Refinancia.dbo.CONSedes				AS S	WITH(NOLOCK)		ON S.IDSede=F.IDSede
				INNER JOIN Refinancia.dbo.tbFuncionarioEstado  AS FE	WITH(NOLOCK)		ON FE.IDEstado=F.IDEstado
				WHERE F.IDUsuario=$p1";
        $bindVars = array($SessionObj->IDUsuario);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	 /**
     * Consulta a la tabla tbParametrosGeneral
     * por medio de su PK parametro
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarURLPortalUnificado($parametro){
        $p1 = $this->cadenaConexion->Param('parametro');
        $sql = "SELECT TOP 1 Valor FROM Refinancia.dbo.tbParametro WHERE parametro=$p1;";
        $bindVars = array($parametro);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta la sesion si existe
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarSesion($SessionObj){
        $var1 = $this->cadenaConexion->Param('IDSesion');
		$var2 = $this->cadenaConexion->Param('IDUsuario');
		$var3 = $this->cadenaConexion->Param('Tocken');
        $sql = "EXEC Refinancia.dbo.SP_Consultar_tbSesiones @IDSesion=$var1, @IDUsuario=$var2, @Tocken=$var3;";
        $bindVars = array($SessionObj->IDSesion, $SessionObj->IDUsuario, $SessionObj->Tocken);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbparametros
     * por el campo Parametro en caso en el parametro sea 0 devuelve todos los registros
     *
     * @access public
     * @return array
     */
	 public function DA_ConsultartablaTbparametros($Parametro){
        $p1 = $this->cadenaConexion->Param('Parametro');
        $sql = "SELECT Parametro,Descripcion,Valor 
				FROM Refinancia.dbo.tbparametro WITH(NOLOCK)";
		
		if (isset($Parametro) && $Parametro!=null){
			$sql .= "WHERE Parametro= $p1";
		}
		
		$sql   .=";";
		
		$bindVars = array($Parametro);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbParametrosSAB por Medio de un SP
     * por el campo IDParametro en caso en el parametro sea 0 devuelve todos los registros, la tabla cuenta con todos los aplicativos activos para el Portal Unificado
     *
     * @access public
     * @return array
     */
	  public function DA_ConsultartablaTbparametrosApl($IDParametro){
            $p1 = $this->cadenaConexion->Param('IDParametro');
            $sql = "EXEC Refinancia.dbo.SP_Consulta_tbParametrosSAB @Parametro=$p1";
            $bindVars = array($IDParametro);
            $result = $this->cadenaConexion->execute($sql, $bindVars);
			return $result;
	  }
	
	/**
     * Consulta el Estado del Sistema
     * Información General del Sistema
     *
     * @access public
     * @return object
     */
    public function DA_EstadoSistema(){
         $sql 	= "DECLARE @cierre BIT;
					DECLARE @FechaAplicacion DATE;
					DECLARE @Periodo VARCHAR(10);

					EXECUTE SP_Sistema_Cerrado  
					@cierre = @cierre OUTPUT,
					@FechaAplicacion = @FechaAplicacion OUTPUT,
					@Periodo = @Periodo OUTPUT;

					SELECT @cierre AS Cierre,@FechaAplicacion AS FechaCierre,  @Periodo AS Periodo;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta a la tabla Funcionarios
     * por el campo id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarUsuarioGeneral($SessionObj){
		$p1 = $this->cadenaConexion->Param('IDUsuario');
		$p2 = $this->cadenaConexion->Param('Retirado');
		$p3 = $this->cadenaConexion->Param('Bloqueado');
		$p4 = $this->cadenaConexion->Param('IDAplicacion');
		$sql = "SELECT
				F.IDUsuario
				,F.TipoDocumento
				,F.Identificacion
				,F.Nombre+' '+Apellidos as Usuario
				,F.EMail
				,F.Bloqueado
				,C.IDCargo
				,C.Cargo
				,UA.IDAplicacion
				FROM Refinancia.dbo.Funcionarios AS F WITH(NOLOCK)
				INNER JOIN Refinancia.dbo.tbUsuarioAplicacion AS UA ON UA.IDUsuario=F.IDUsuario
				INNER JOIN Refinancia.dbo.Cargos AS C WITH(NOLOCK) ON C.IDCargo=F.IDCargo
				WHERE Retirado=$p2 and ISNULL(Bloqueado,0)=$p3 and IDAplicacion=$p4;";
		$bindVars = array($SessionObj->Retirado,$SessionObj->Bloqueado,$SessionObj->IDAplicacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	
	/**
     * Consulta Pago aplicados Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagosAplicados(){
        $sql 	= " SELECT Count(1) AS PagosAplicados 
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE FechaAplicacion BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND P.Avales =0 AND FechaAplicacion IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta Pago aplicados Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarRegistroPagosAplicados($FechaInicial,$FechaFinal){
        $p1 	= $this->cadenaConexion->Param('FechaInicio');
		$p2 	= $this->cadenaConexion->Param('FechaFin');
        $sql 	= " EXEC [Gop].[sp_consultar_pagos_Asociados] @FechaInicio=$p1, @FechaFin=$p2;";
        $bindVars = array($FechaInicial,$FechaFinal);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta Partidas Pendientes x Identificar
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarRegistroPPINpls($FechaInicial,$FechaFinal){
        $p1 	= $this->cadenaConexion->Param('FechaInicio');
		$p2 	= $this->cadenaConexion->Param('FechaFin');
        $sql 	= " EXEC [Gop].[sp_consultar_PPI] @FechaInicio=$p1, @FechaFin=$p2;";
        $bindVars = array($FechaInicial,$FechaFinal);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta Partidas Pendientes x Identificar vista
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarRegistroPPI_vista(){
        $p1 	= $this->cadenaConexion->Param('FechaInicio');
		$p2 	= $this->cadenaConexion->Param('FechaFin');
        $sql 	= " EXEC [Gop].[sp_consultar_Vista_PPI] @FechaInicio=$p1, @FechaFin=$p2;";
        $bindVars = array(NULL,NULL);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta Pago no aplicados Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagosNoAplicados(){
        $sql 	= " SELECT Count(1) AS PagosNoAplicados 
				    FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE Fecha BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND P.Avales =0 AND FechaAplicacion IS NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbCanal
     * por medio de su PK IDCanal
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarNotasDebito(){
         $sql 	= " SELECT Count(1) AS NotasDebito 
				    FROM Pagos 
					WHERE Fecha IS NULL
					AND FechaAplicacion IS NOT NULL 
					AND IdTipoDocumento=2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbCanal
     * por medio de su PK IDCanal
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarNotasCredito(){
         $sql 	= " SELECT Count(1) AS NotasCredito 
				    FROM Pagos 
					WHERE Fecha IS NOT NULL 
					AND FechaAplicacion IS NOT NULL 
					AND IdTipoDocumento=3;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas NPls
     * contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadInifacturaNpls(){
         $sql 	= " SELECT Count(1) AS IniFactura
				    FROM [FacturacionElectronica].[Ini].[FacturaCliente]
					WHERE IDAplicacion=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Ini-Facturas NPls
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosInifacturaNpls(){
         $sql 	= " SELECT IdFacturaClienteIni, FechaRegistro, IdPatrimonioAutonomo,RazonSocialEmisor,IdCliente,TipoIdentificacionCliente,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,CiudadCliente,EmailCliente,ValorTotalSinImpuesto,ValorTotalConImpuesto,MedioPago,Portafolio,IdAplicacion,TipoDocumento,Moneda,FechaPago,IdPago,PagoAsociadoA,EstadoPago,CodigoProducto,ProcesadoPre,FechaProcesadoPre,CufeDocumentoModificado,CodigoMotivoNota
				    FROM [FacturacionElectronica].[Ini].[FacturaCliente]
					WHERE IDAplicacion=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas Avales
     * contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadInifacturaAvales(){
         $sql 	= " SELECT Count(1) AS IniFactura
				    FROM [FacturacionElectronica].[Ini].[FacturaCliente]
					WHERE IDAplicacion=3;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los Registros de Ini-Facturas Avales
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosInifacturaAvales(){
         $sql 	= " SELECT IdFacturaClienteIni, FechaRegistro, IdPatrimonioAutonomo,RazonSocialEmisor,IdCliente,TipoIdentificacionCliente,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,CiudadCliente,EmailCliente,ValorTotalSinImpuesto,ValorTotalConImpuesto,MedioPago,Portafolio,IdAplicacion,TipoDocumento,Moneda,FechaPago,IdPago,PagoAsociadoA,EstadoPago,CodigoProducto,ProcesadoPre,FechaProcesadoPre,CufeDocumentoModificado,CodigoMotivoNota
				    FROM [FacturacionElectronica].[Ini].[FacturaCliente] 
					WHERE IDAplicacion=3;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Pre-Facturas Npls
     * contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPrefacturaNpls(){
         $sql 	= " SELECT count(1) AS PreFactura
				    FROM [FacturacionElectronica].[Pre].[FacturaCliente]
					WHERE IDAplicacion=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Pre-Facturas NPls
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosPrefacturaNpls(){
         $sql 	= " SELECT DISTINCT FC.FechaRegistro AS FechaRegistro, FC.IdPatrimonioAutonomo, NitEmisorFactura, RazonSocialEmisor, NumeroDocumentoCliente
					,NombreRazonSocialCliente, SegundoNombreCliente, ApellidoCliente, IdCliente, CiudadCliente, DepartamentoCliente, EmailCliente
					,Moneda, rangoNumeracion, IdProgramacion, FC.IdFacturaClientePre
					,UnidadMedida, IdPago, IdPortafolio, FC.Aprobado, FC.FechaAprobacion, FC.DocumentoUsuarioAprueba
					FROM [FacturacionElectronica].[Pre].[FacturaCliente] AS FC
					INNER JOIN [FacturacionElectronica].[Pre].[FacturaClienteDetalle] AS FD ON FD.IdFacturaClientePre = FC.IdFacturaClientePre
					INNER JOIN [FacturacionElectronica].[Pre].[FacturaCliente_IdPago] AS FP ON FP.IdFacturaClientePre = FD.IdFacturaClientePre
					WHERE FC.IDAplicacion=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Pre-Facturas Avales
     * contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPrefacturaAvales(){
         $sql 	= " SELECT count(1) AS PreFactura
				    FROM [FacturacionElectronica].[Pre].[FacturaCliente]
					WHERE IDAplicacion=3;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Pre-Facturas Avales
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosPrefacturaAvales(){
         $sql 	= " SELECT IdFacturaClientePre, FechaRegistro, IdPatrimonioAutonomo,RazonSocialEmisor,IdCliente,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,CiudadCliente,EmailCliente,Moneda,Aprobado,FechaAprobacion,DocumentoUsuarioAprueba
					FROM [FacturacionElectronica].[Pre].[FacturaCliente] 
					WHERE IDAplicacion=3;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_CantidadInifacturaUfeg(){
         $sql 	= " SELECT Count(1) AS IniFactura
					FROM [FacturacionElectronica].[Ini].[FacturaCliente_Ufeg] 
					WHERE IdAplicacion =2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la Registros de Ini-Facturas en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarInifacturaUfeg(){
         $sql 	= " SELECT IdFacturaClienteUfegIni, FCU.FechaRegistro, IDPatrimonioAutonomo, RazonSocialEmisor,IDCliente,TipoIdentificacionCliente,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,CiudadCliente,EmailCliente,CodigoProducto,NombreConceptoHomologado AS Concepto,ValorTotalSinImpuesto,ValorTotalConImpuesto,MedioPago,FCU.IDAplicacion, TipoDocumento, Moneda, FechaPago,Numero_aprobacion,tasa_descuento,cuota,Fecha_expedicion,Codigo_convenio_baloto,Referencia,Codigo_convenio_bancolombia,Codigo_barras, EstadoPago, ProcesadoPre, FechaProcesadoPre,CUFEDocumentoModificado, CodigoMotivoNota, IDCtrlFactUfeg
					FROM [FacturacionElectronica].[Ini].[FacturaCliente_Ufeg] AS FCU
					INNER JOIN [FacturacionElectronica].[dbo].[HomologacionConcepto] AS  HC ON HC.CodigoConceptoHomologado=FCU.CodigoProducto
					WHERE FCU.IdAplicacion =2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Ini-Facturas en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPrefacturaUfeg(){
         $sql 	= " SELECT Count(1) AS PreFactura
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg] 
					WHERE IdAplicacion =2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de pre-Facturas en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPrefacturaUfeg(){
         $sql 	= " SELECT IdFacturaCliente_UfegPre,FechaRegistro,IdPatrimonioAutonomo,RazonSocialEmisor,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,IdCliente,CiudadCliente,EmailCliente,IdAplicacion,Moneda,FechaPago,Numero_aprobacion,Tasa_descuento,Cuota,Fecha_expedicion,rangoNumeracion,IdProgramacion,EstadoPago,Aprobado,FechaAprobacion
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg] 
					WHERE IdAplicacion =2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de pre-Facturas informe en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarInfoPrefacturaUfeg(){
         $sql 	= " SELECT FCU.IdFacturaCliente_UfegPre,FCU.FechaRegistro,FCU.IdPatrimonioAutonomo,RazonSocialEmisor,NumeroDocumentoCliente,NombreRazonSocialCliente,SegundoNombreCliente,ApellidoCliente,IdCliente,CiudadCliente,EmailCliente,IdAplicacion,Moneda,FechaPago,Numero_aprobacion,Tasa_descuento,Cuota,Fecha_expedicion,rangoNumeracion, FCUD.DescripcionProducto, ValorDescuento, ValorUnitario, ValorTotal, ValorTotalSinImpuesto, UnidadMedida,IdProgramacion,EstadoPago,Aprobado,FechaAprobacion
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg] AS FCU
					INNER JOIN [FacturacionElectronica].[Pre].[FacturaCliente_UfegDetalle] AS FCUD ON FCUD.IdFacturaCliente_UfegPre=FCU.IdFacturaCliente_UfegPre
					WHERE IdAplicacion =2
					ORDER BY IdCliente ASC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de registros Facturados en la Ufeg
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_CantidadFacturadoUfeg(){
         $sql 	= " SELECT count(1) AS Facturado 
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] 
					WHERE CUFE IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros Facturados en la Ufeg
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarFacturadoUfeg(){
         $sql 	= " SELECT TOP 2000 IDCtrlFactUFEG,FechaRegistro,PeriodoFacturacion,IDEntidad,IDAval,IDPersonaCartera,IdentificacionCartera,PersonaCartera,IDPersonaLINCE,TipoIdentificacionCliente,NombreCliente,NombreEmpresa,CiudadCliente,EmailCliente,TelefonoCliente,FechaAutorizacion, PersonaComercio,IdentificacionComercio,Cuota, CodigoAutorizacion,IDTitulo,TipoTitulo,FechaTitulo,FechaVencimientoTitulo,FechaPago,ValorTitulo,ValorDescuento,ValorCapital,ValorOtros,ValorIntereses,ValorSeguroVida,ValorTotal,FechaDesembolso,Saldo,EstadoAval,TasaDescuento, estadoCuota,CodigoConveniobaloto,Referencia, CodigoConvenioBancolombia, CodigoBarras,IDTipoDocumento,IDCodigoMotivoNota, IDRegAsociado,IDMotivoFact,CUFE,FechaIngresoCUFE
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] 
					WHERE CUFE IS NOT NULL
					ORDER BY IDCtrlFactUFEG DESC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Inconsistencia en la Ufeg
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_CantidadInconsistenciaUfeg(){
         $sql 	= " SELECT count(1) AS Inconsistencia 
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] AS FC
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]  AS FU ON FU.IDCtrlFactUFEG = FC.IDCtrlFactUFEG
					WHERE FC.Autorizado=0 AND FU.Anulado=0;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los Registros con inconsistencia en la Ufeg
     * Contador
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosInconsistenciaUfeg(){
         $sql 	= " SELECT  TOP 500 IDCtrlFactUFEGCambio, FU.FechaRegistro, FU.IDCtrlFactUFEG, FU.IDAval, FU.NroTitulo, ValorCapitalINI,ValorInteresesINI,ValorSeguroVidaINI,ValorOtrosINI, ValorTotalINI,ValorCapitalNVO, ValorInteresesNVO,ValorSeguroVidaNVO,ValorOtrosNVO,ValorTotalNVO, NovedadCambio,IDCtrlFactUFEGNota, FU.PREFactura, FU.CUFE
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] AS FC
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]  AS FU ON FU.IDCtrlFactUFEG = FC.IDCtrlFactUFEG
					WHERE FC.Autorizado=0 AND FU.Anulado=0
					ORDER BY IDCtrlFactUFEGCambio DESC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEGCambio
     * Informacion general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosInconsistenciaUfegxIDCtrlFactUFEGCambio($factufegcambiosObj){
		$p1 = $this->cadenaConexion->Param('IDCtrlFactUFEGCambio');
         $sql 	= " SELECT  IDCtrlFactUFEGCambio, FU.FechaRegistro, FU.IDCtrlFactUFEG, FU.IDAval, FU.NroTitulo, ValorCapitalINI,ValorInteresesINI,ValorSeguroVidaINI,ValorOtrosINI, ValorTotalINI,ValorCapitalNVO, ValorInteresesNVO,ValorSeguroVidaNVO,ValorOtrosNVO,ValorTotalNVO, NovedadCambio,IDCtrlFactUFEGNota, FU.PREFactura, FU.CUFE
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] AS FC
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]  AS FU ON FU.IDCtrlFactUFEG = FC.IDCtrlFactUFEG
					WHERE FC.IDCtrlFactUFEGCambio=$p1;";
		$bindVars = array($factufegcambiosObj->IDCtrlFactUFEGCambio);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEG
     * Informacion general
     *
     * @access public
     * @return object
     */
    public function DA_RInconsistenciaUfegxIDCtrlFactUFEG($factufegcambiosObj){
		$p1 = $this->cadenaConexion->Param('IDCtrlFactUFEG');
        $sql 	= " SELECT  IDCtrlFactUFEGCambio, FU.FechaRegistro, FU.IDCtrlFactUFEG, FU.IDAval, FU.NroTitulo, ValorCapitalINI,ValorInteresesINI,ValorSeguroVidaINI,ValorOtrosINI, ValorTotalINI,ValorCapitalNVO, ValorInteresesNVO,ValorSeguroVidaNVO,ValorOtrosNVO,ValorTotalNVO, NovedadCambio,IDCtrlFactUFEGNota, FU.PREFactura, FU.CUFE, FU.IDPersonaLINCE, FU.NombreCliente
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] AS FC
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]  AS FU ON FU.IDCtrlFactUFEG = FC.IDCtrlFactUFEG
					WHERE FU.IDCtrlFactUFEG=$p1;";
		$bindVars = array($factufegcambiosObj->IDCtrlFactUFEG);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Cuotas a facturar en la Ufeg
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CantidadCuotaFacturar(){
         $sql 	= " SELECT Count(1) As ContarCuotaFacturar
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]
					WHERE CUFE IS NULL AND PreFactura =0 AND Anulado=0;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta registros de Cuotas a facturar en la Ufeg
     * información general
     *
     * @access public
     * @return object
     * @return object
     */
    public function DA_RegistrosCuotaFacturar(){
        $sql 	= " SELECT FU.IDCtrlFactUFEG,FU.FechaRegistro,FU.PeriodoFacturacion,FU.IDEntidad,FU.IDAval,FU.IDPersonaCartera,FU.IdentificacionCartera,FU.PersonaCartera,FU.IDPersonaLINCE,FU.TipoIdentificacionCliente,FU.NombreCliente,FU.NombreEmpresa,FU.CiudadCliente,FU.EmailCliente,FU.TelefonoCliente,FU.FechaAutorizacion, FU.PersonaComercio,FU.IdentificacionComercio,FU.Cuota, FU.CodigoAutorizacion,FU.IDTitulo,FU.TipoTitulo,FU.FechaTitulo,FU.FechaVencimientoTitulo,FU.FechaPago,FU.ValorTitulo,FU.ValorDescuento,FU.ValorCapital,FU.ValorOtros,FU.ValorIntereses,FU.ValorSeguroVida,FU.ValorTotal,FU.FechaDesembolso,FU.Saldo,FU.EstadoAval,FU.TasaDescuento, FU.estadoCuota,FU.CodigoConveniobaloto,FU.Referencia, FU.CodigoConvenioBancolombia, FU.CodigoBarras,FU.IDTipoDocumento,FU.IDCodigoMotivoNota, FU.IDRegAsociado,FU.Anulado,FU.AnuladoPor, FU.CodigoResultadoOperador,FU.IDMotivoFact, FMF.MotivoFacturacion
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] AS FU
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG_MotivosFacturacion] AS FMF ON FMF.IDMotivoFact = FU.IDMotivoFact
					WHERE FU.CUFE IS NULL AND FU.PreFactura =0  AND FU.Anulado=0
					ORDER BY FU.NroTitulo,FU.IDPersonaLINCE ASC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Cuotas anuladas en la Ufeg
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CantidadCuotaAnulada(){
         $sql 	= " SELECT Count(1) As CuotaAnulada
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]
					WHERE CUFE IS NULL AND Anulado=1 AND IDMotivoFact!=4 ;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta registros de Cuotas a anuladas en la Ufeg
     * información general
     *
     * @access public
     * @return object
     * @return object
     */
    public function DA_RegistrosCuotaAnulada(){
        $sql 	= " SELECT FU.IDCtrlFactUFEG,FU.FechaRegistro,FU.PeriodoFacturacion,FU.IDEntidad,FU.IDAval,FU.IDPersonaCartera,FU.IdentificacionCartera,FU.PersonaCartera,FU.IDPersonaLINCE,FU.TipoIdentificacionCliente,FU.NombreCliente,FU.NombreEmpresa,FU.CiudadCliente,FU.EmailCliente,FU.TelefonoCliente,FU.FechaAutorizacion, FU.PersonaComercio,FU.IdentificacionComercio,FU.Cuota, FU.CodigoAutorizacion,FU.IDTitulo,FU.TipoTitulo,FU.FechaTitulo,FU.FechaVencimientoTitulo,FU.FechaPago,FU.ValorTitulo,FU.ValorDescuento,FU.ValorCapital,FU.ValorOtros,FU.ValorIntereses,FU.ValorSeguroVida,FU.ValorTotal,FU.FechaDesembolso,FU.Saldo,FU.EstadoAval,FU.TasaDescuento, FU.estadoCuota,FU.CodigoConveniobaloto,FU.Referencia, FU.CodigoConvenioBancolombia, FU.CodigoBarras, PreFactura, FechaPREFactura, CUFE, FechaIngresoCUFE, FU.IDTipoDocumento,FU.IDCodigoMotivoNota, FU.IDRegAsociado,FU.Anulado,FU.AnuladoPor, FU.CodigoResultadoOperador,FU.IDMotivoFact, FMF.MotivoFacturacion
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] AS FU
					INNER JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG_MotivosFacturacion] AS FMF ON FMF.IDMotivoFact = FU.IDMotivoFact
					WHERE FU.CUFE IS NULL AND FU.Anulado =1 AND FU.IDMotivoFact!=4;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Ufeg
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_CantidadAutFacturaUfeg(){
         $sql 	= " SELECT Count(1) AS PreFactura
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg] 
					WHERE IdAplicacion =2 AND Aprobado=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Npls
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_CantidadAutFacturaNpls(){
         $sql 	= " SELECT Count(1) AS PreFactura
					FROM [FacturacionElectronica].[Pre].[FacturaCliente] 
					WHERE IdAplicacion =1 AND Aprobado=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad Facturas Autorizadas en la Npls
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_CantidadAutFacturaAvales(){
         $sql 	= " SELECT Count(1) AS PreFactura
					FROM [FacturacionElectronica].[Pre].[FacturaCliente] 
					WHERE IdAplicacion =3 AND Aprobado=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta registros Facturas No Autorizadas Ufeg
     * información general
     *
     * @access public
     * @return object
     * @return object
     */
    public function DA_RegistrosAutFacturaUfeg(){
	$sql 	= " SELECT IDFacturaCliente_UFegPre, FechaRegistro, IDPatrimonioAutonomo, NitEmisorFactura, RazonSocialEmisor, TelefonoEmisor, NombreRazonSocialCliente, SegundoNombreCliente
					  , ApellidoCliente, IdCliente, CiudadCliente, DepartamentoCliente, DireccionCliente, EmailCliente, TelefonoCliente, Moneda, FechaPago, Numero_aprobacion, tasa_descuento
					  , Cuota, Fecha_expedicion, Codigo_convenio_bancolombia, Codigo_barras, rangoNumeracion, IDProgramacion, Aprobado, FechaAprobacion, DocumentoUsuarioAprueba, Notificado, IDCtrlFactUFEG, CUFEResultado
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg] 
					WHERE IdAplicacion =2;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta registros Facturas No Autorizadas Npls
     * información general
     *
     * @access public
     * @return object
     * @return object
     */
    public function DA_RegistrosAutFacturaNpls(){
	$sql 	= " SELECT IdFacturaClientePre, FechaRegistro, IDPatrimonioAutonomo, NitEmisorFactura, RazonSocialEmisor, TelefonoEmisor, NombreRazonSocialCliente, SegundoNombreCliente
				, ApellidoCliente, IdCliente, CiudadCliente, DepartamentoCliente, DireccionCliente, EmailCliente, TelefonoCliente, Moneda, rangoNumeracion, IDProgramacion, Aprobado, FechaAprobacion, DocumentoUsuarioAprueba, Notificado, CUFEResultado
				FROM [FacturacionElectronica].[Pre].[FacturaCliente] 
				WHERE IdAplicacion =1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta couta facturar x IDCtrlFactUFEG
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CuotaFacturarxIDCtrlFactUFEG($facturacionufegObj){
		$p1 = $this->cadenaConexion->Param('IDCtrlFactUFEG');
        $sql 	= " SELECT *
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG]
					WHERE IDCtrlFactUFEG = $p1;";
		$bindVars = array($facturacionufegObj->IDCtrlFactUFEG);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta pre-facturas ufeg x IdFacturaCliente_UfegPre
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_PreFacturaUfegxIdFacturaClientePre($prefUfegObj){
		$p1 = $this->cadenaConexion->Param('IdFacturaCliente_UfegPre');
        $sql 	= " SELECT *
					FROM [FacturacionElectronica].[Pre].[FacturaCliente_Ufeg]
					WHERE IdFacturaCliente_UfegPre = $p1;";
		$bindVars = array($prefUfegObj->IdFacturaCliente_UfegPre);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Pagos Facturados Npls
     * Contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadFacturadoNpls(){
         $sql 	= " SELECT Count(1) AS Facturado 
				    FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio=PG.IDPortafolio
					WHERE P.Avales=0 AND FechaIngresoCUFE BETWEEN DATEADD(mm, DATEDIFF(mm,0,DATEADD(mm,-1,getdate())), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0)) AND CUFE IS NOT NULL AND Valor>0;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Pagos Facturados Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosFacturadoNpls(){
        $sql 	= " SELECT IDPago, IDRefinancia, IDCliente, IDCuota,PG.IDPortafolio, IDMoneda,AsociadoA,FechaIngreso,Fecha As FechaPago,Valor, FechaPago AS FechaPAsociacion,FechaAplicacion AS FechaUAsociacion, AplicadoPor, NAplicadoPor, Banco, NroCuenta,Referencia1,Referencia2, PreFactura, RecaudadoPor, NRecaudadoPor , FechaPreFactura, CUFE, FechaIngresoCUFE, IDTipoDocumento, IDPagoAsociado, IDCodigoMotivoNota
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio=PG.IDPortafolio
					WHERE P.Avales=0 AND FechaIngresoCUFE BETWEEN DATEADD(mm, DATEDIFF(mm,0,DATEADD(mm,-1,getdate())), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0)) AND CUFE IS NOT NULL AND Valor>0
					ORDER BY FechaIngresoCufe DESC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Pagos Facturados Avales
     * Contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadFacturadoAvales(){
         $sql 	= " SELECT Count(1) AS Facturado 
				    FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio=PG.IDPortafolio
					WHERE P.Avales>0 AND CUFE IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Pagos Facturados Avales
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosFacturadoAvales(){
        $sql 	= " SELECT IDPago, IDRefinancia, IDCliente, IDCuota,PG.IDPortafolio, IDMoneda,AsociadoA,FechaIngreso,Fecha As FechaPago,Valor, FechaPago AS FechaPAsociacion,FechaAplicacion AS FechaUAsociacion, AplicadoPor, NAplicadoPor, Banco, NroCuenta,Referencia1,Referencia2, PreFactura, RecaudadoPor, NRecaudadoPor , FechaPreFactura, CUFE, FechaIngresoCUFE, IDTipoDocumento, IDPagoAsociado, IDCodigoMotivoNota
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio=PG.IDPortafolio
					WHERE P.Avales>0 AND CUFE IS NOT NULL
					ORDER BY FechaIngresoCufe DESC;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Facturas generadas Npls
     * Contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadFacturasNpls(){
         $sql 	= " SELECT Count(1) AS Facturas
					FROM [FacturacionElectronica].[His].[FacturaCliente] 
					WHERE IdAplicacion=1 AND ProcesadoPro=1 AND Resultado ='Correcto';";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Facturas No facturadas Npls
     * Contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadNoFacturasNpls(){
         $sql 	= " SELECT COUNT(1) AS Facturas
					FROM [FacturacionElectronica].[His].[FacturaCliente] 
					WHERE IdAplicacion=1 AND ProcesadoPro=1 AND Resultado <> 'Correcto';";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Facturas generadas Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosFacturasNpls(){
        $sql 	= " SELECT TOP 1000 [IdFacturaClienteHis]
					  ,[FechaRegistro]
					  ,[NitEmisorFactura]
					  ,[RazonSocialEmisor]
					  ,[DireccionEmisor]
					  ,[TelefonoEmisor]
					  ,[NumeroDocumentoCliente]
					  ,[NombreRazonSocialCliente]
					  ,[SegundoNombreCliente]
					  ,[ApellidoCliente]
					  ,[IdCliente]
					  ,[CiudadCliente]
					  ,[DepartamentoCliente]
					  ,[DireccionCliente]
					  ,[EmailCliente]
					  ,[TelefonoCliente]
					  ,[Moneda]
					  ,[InformacionAdicional]
					  ,[consecutivoDocumento]
					  ,[rangoNumeracion]
					  ,[IdProgramacion]
					  ,[EstadoPago]
					  ,[Aprobado]
					  ,[FechaAprobacion]
					  ,[DocumentoUsuarioAprueba]
					  ,[Notificado]
					  ,[FechaProcesadoPro]
					  ,[ConsecutivoDocumentoResultado]
					  ,[CUFEResultado]
					  ,[FechaRespuestaResultado]
					  ,[MensajeResultado]
					  ,[Resultado]
					  ,[FechaEmision]
					  ,[consecutivoDocumentoModificado]
					  ,[CUFEModificado]
					  ,[CodigoMotivoNotaHomologado]
					  ,[fechaEmisionDocumentoModificado]
					  ,[FechaHistorico]
				FROM [FacturacionElectronica].[His].[FacturaCliente] 
				WHERE IdAplicacion=1 AND ProcesadoPro=1 AND Resultado ='Correcto';";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Facturas No generadas Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosNoFacturasNpls(){
        $sql 	= " SELECT TOP 1000 [IdFacturaClienteHis]
					  ,[FechaRegistro]
					  ,[NitEmisorFactura]
					  ,[RazonSocialEmisor]
					  ,[DireccionEmisor]
					  ,[TelefonoEmisor]
					  ,[NumeroDocumentoCliente]
					  ,[NombreRazonSocialCliente]
					  ,[SegundoNombreCliente]
					  ,[ApellidoCliente]
					  ,[IdCliente]
					  ,[CiudadCliente]
					  ,[DepartamentoCliente]
					  ,[DireccionCliente]
					  ,[EmailCliente]
					  ,[TelefonoCliente]
					  ,[Moneda]
					  ,[InformacionAdicional]
					  ,[consecutivoDocumento]
					  ,[rangoNumeracion]
					  ,[IdProgramacion]
					  ,[EstadoPago]
					  ,[Aprobado]
					  ,[FechaAprobacion]
					  ,[DocumentoUsuarioAprueba]
					  ,[Notificado]
					  ,[FechaProcesadoPro]
					  ,[ConsecutivoDocumentoResultado]
					  ,[CUFEResultado]
					  ,[FechaRespuestaResultado]
					  ,[MensajeResultado]
					  ,[Resultado]
					  ,[FechaEmision]
					  ,[consecutivoDocumentoModificado]
					  ,[CUFEModificado]
					  ,[CodigoMotivoNotaHomologado]
					  ,[fechaEmisionDocumentoModificado]
					  ,[FechaHistorico]
				FROM [FacturacionElectronica].[His].[FacturaCliente] 
				WHERE IdAplicacion=1 AND ProcesadoPro=1 AND Resultado <> 'Correcto';";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta la cantidad de Facturas generadas Avales
     * Contar
     *
     * @access public
     * @return object
     */
    public function DA_CantidadFacturasAvales(){
         $sql 	= " SELECT Count(1) AS Facturas
					FROM [FacturacionElectronica].[His].[FacturaCliente] 
					WHERE IdAplicacion=3 AND ProcesadoPro=1 AND CUFEResultado IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta los registros de Facturas generadas Avales
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_RegistrosFacturasAvales(){
        $sql 	= " SELECT TOP 1000 [IdFacturaClienteHis]
					  ,[FechaRegistro]
					  ,[NitEmisorFactura]
					  ,[RazonSocialEmisor]
					  ,[DireccionEmisor]
					  ,[TelefonoEmisor]
					  ,[NumeroDocumentoCliente]
					  ,[NombreRazonSocialCliente]
					  ,[SegundoNombreCliente]
					  ,[ApellidoCliente]
					  ,[IdCliente]
					  ,[CiudadCliente]
					  ,[DepartamentoCliente]
					  ,[DireccionCliente]
					  ,[EmailCliente]
					  ,[TelefonoCliente]
					  ,[Moneda]
					  ,[InformacionAdicional]
					  ,[consecutivoDocumento]
					  ,[rangoNumeracion]
					  ,[IdProgramacion]
					  ,[EstadoPago]
					  ,[Aprobado]
					  ,[FechaAprobacion]
					  ,[DocumentoUsuarioAprueba]
					  ,[Notificado]
					  ,[FechaProcesadoPro]
					  ,[ConsecutivoDocumentoResultado]
					  ,[CUFEResultado]
					  ,[FechaRespuestaResultado]
					  ,[MensajeResultado]
					  ,[Resultado]
					  ,[FechaEmision]
					  ,[consecutivoDocumentoModificado]
					  ,[CUFEModificado]
					  ,[CodigoMotivoNotaHomologado]
					  ,[fechaEmisionDocumentoModificado]
					  ,[FechaHistorico]
				FROM [FacturacionElectronica].[His].[FacturaCliente] 
				WHERE IdAplicacion=3 AND ProcesadoPro=1 AND CUFEResultado IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbCanal
     * por medio de su PK IDCanal
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarNoFacturado(){
         $sql 	= " SELECT Count(1) AS PreNoAutorizado
					FROM [FacturacionElectronica].[Pre].[FacturaCliente] 
					WHERE IdAplicacion =2 AND Aprobado=0";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por IdPago
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxIdPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= " EXEC SP_ConsultarPago_IdPago @IDPago=$p1;";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta si un Pago es de tipo Cheque
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoChequexIdPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= " SELECT IDPago FROM Pagos AS P
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago=P.IDTipoOrigenPago
					where IDPago=$p1 AND TP.TipoOrigenPago LIKE '%cheque%';";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Identificacion del Cliente
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxIdentCliente($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Identificacion');
        $sql 	= " SELECT top 100 IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					INNER JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					LEFT JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					LEFT JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE c.Identificacion=$p1 ORDER BY Fecha DESC;";
        $bindVars = array($pagosObj->IdentCliente);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Cliente por Identificacion del Cliente
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarClientexIdentCliente($clienteObj,$TipoBusqueda){
		$p1 	= $this->cadenaConexion->Param('TipoBusqueda');
		$p2 	= $this->cadenaConexion->Param('ValorBuscado');
        $sql 	= " EXEC SP_ConsultarGeneral_Cliente 
					 @TipoBusqueda		= $p1
					,@ValorBuscado		= $p2;";
        $bindVars = array($TipoBusqueda,$clienteObj->ValorBuscado);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Credito
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxCredito($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Credito');
        $sql 	= " EXEC SP_ConsultarPago_Credito @Credito=$p1;";
        $bindVars = array($pagosObj->Credito);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Acuerdo
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxAcuerdo($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Acuerdo');
        $sql 	= " EXEC SP_ConsultarPago_Acuerdo @Acuerdo=$p1;";
        $bindVars = array($pagosObj->Acuerdo);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Acuerdo
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarChequexFechaPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Fecha');
        $sql 	= " SELECT IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					INNER JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					LEFT JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					LEFT JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE P.Fecha=$p1 AND P.IdTipoOrigenPago in (21,28) ORDER BY IDPago DESC;";
        $bindVars = array($pagosObj->Fecha);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por Numero de Acuerdo
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoSinAsociarxFechaPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Fecha');
        $sql 	= " EXEC SP_PartidasPendientesXIdentificar @FechaPago=$p1;";
        $bindVars = array($pagosObj->Fecha);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de Pagos por IDCliente
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxIDCliente($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDCliente');
        $sql 	= " SELECT IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					INNER JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					INNER JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					INNER JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE P.IDCliente=$p1 ORDER BY IDPago DESC;";
        $bindVars = array($pagosObj->IDCliente);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de Pagos por Asociar
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagoxAsociar($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Fecha');
        $sql 	= " SELECT IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					INNER JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					INNER JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					INNER JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE P.IDCuota=0 AND IDRefinancia=0 AND P.Fecha=$p1 ORDER BY IDPago,IDCliente DESC;";
        $bindVars = array($pagosObj->Fecha);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de Saldos a Favor
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarSaldosaFavor($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Fecha');
        $sql 	= " SELECT IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					INNER JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					INNER JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					INNER JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE P.IDTipoOrigenPago=14 AND P.Fecha=$p1 ORDER BY IDPago,IDCliente DESC;";
        $bindVars = array($pagosObj->Fecha);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion traslado Tesoreria
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTrasladoTesoreria($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Fecha');
        $sql 	= " SELECT IDPago,IDRefinancia,C.IDCliente,lower(CONCAT(C.Nombre1,' ',C.APellido1)) AS Cliente,IDCuota,P.IDPortafolio,PF.IDPais,PA.CodigoInternet,P.IDMoneda,IDEstadoPago,P.IDTipoOrigenPago,AsociadoA
					,DetalleAsociacion,Credito,CAST(Fecha AS DATE) AS Fecha,Valor,CAST(FechaPago AS DATE) AS FechaPago,Origen,Banco,NroComprobante,NroCuenta,Detalles,FechaIngreso
					,CargadoDesde,TipoPago,CAST(FechaAplicacion AS DATE) AS FechaAplicacion,AplicadoPor,NAplicadoPor,IDRecaudadoPor,RecaudadoPor,NRecaudadoPor
					,PeriodoAplicaRecaudo,CodigoBarras,PreFactura,FechaPrefactura,CUFE,FechaIngresoCUFE,IDTipoDocumento,IDPagoAsociado
					,IDCodigoMotivoNota,Anulado,TP.TipoOrigenPago, Referencia1, Referencia2, PF.Avales
					FROM Pagos AS P
					LEFT JOIN Clientes AS C ON C.IDCliente = P.IDCliente
					INNER JOIN TiposOrigenPago AS TP ON TP.IDTipoOrigenPago = P.IDTipoOrigenPago
					LEFT JOIN Portafolios As PF ON PF.IDPortafolio = P.IDPortafolio
					LEFT JOIN Paises As PA ON PA.IDPais = PF.IDPais
					WHERE P.IDTipoOrigenPago=31 AND P.Fecha=$p1 ORDER BY IDPago,IDCliente DESC;";
        $bindVars = array($pagosObj->Fecha);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por IdPersonaLince
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarCuotaFacturarxIdPersonaLince($fUfegObj){
		$p1 	= $this->cadenaConexion->Param('IDPersonaLINCE');
        $sql 	= " SELECT FU.IDCtrlFactUFEG,FU.FechaRegistro,PeriodoFacturacion,IDEntidad,FU.IDAval,IDPersonaCartera,IdentificacionCartera,PersonaCartera,IDPersonaLINCE,TipoIdentificacionCliente,IdentificacionCliente,NombreCliente,NombreEmpresa,CiudadCliente,EmailCliente,TelefonoCliente,FechaAutorizacion, PersonaComercio,IdentificacionComercio,Cuota, CodigoAutorizacion,IDTitulo,TipoTitulo,FechaTitulo,FechaVencimientoTitulo,FechaPago,ValorTitulo,ValorDescuento,ValorCapital,ValorOtros,ValorIntereses,ValorSeguroVida,ValorTotal,FechaDesembolso,Saldo,EstadoAval,TasaDescuento, estadoCuota,CodigoConveniobaloto,Referencia, CodigoConvenioBancolombia, CodigoBarras,IDTipoDocumento,IDCodigoMotivoNota, IDRegAsociado,PreFactura,FechaPREFactura,CUFE,FechaIngresoCUFE,Anulado,AnuladoPor,IDMotivoFact, FUC.IDCtrlFactUFEGCambio
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] AS FU
					LEFT JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] as FUC ON FUC.IDCtrlFactUFEG=FU.IDCtrlFactUFEG
					WHERE FU.IDPersonaLINCE=$p1;";
        $bindVars = array($fUfegObj->IDPersonaLINCE);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por Identificacion cliente
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarCuotaFacturarxIdentificacioncliente($fUfegObj){
		$p1 	= $this->cadenaConexion->Param('IdentificacionCliente');
        $sql 	= " SELECT FU.IDCtrlFactUFEG,FU.FechaRegistro,PeriodoFacturacion,IDEntidad,FU.IDAval,IDPersonaCartera,IdentificacionCartera,PersonaCartera,IDPersonaLINCE,TipoIdentificacionCliente,IdentificacionCliente,NombreCliente,NombreEmpresa,CiudadCliente,EmailCliente,TelefonoCliente,FechaAutorizacion, PersonaComercio,IdentificacionComercio,Cuota, CodigoAutorizacion,IDTitulo,TipoTitulo,FechaTitulo,FechaVencimientoTitulo,FechaPago,ValorTitulo,ValorDescuento,ValorCapital,ValorOtros,ValorIntereses,ValorSeguroVida,ValorTotal,FechaDesembolso,Saldo,EstadoAval,TasaDescuento, estadoCuota,CodigoConveniobaloto,Referencia, CodigoConvenioBancolombia, CodigoBarras,IDTipoDocumento,IDCodigoMotivoNota, IDRegAsociado,PreFactura,FechaPREFactura,CUFE,FechaIngresoCUFE,Anulado,AnuladoPor,IDMotivoFact, FUC.IDCtrlFactUFEGCambio
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] AS FU
					LEFT JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] as FUC ON FUC.IDCtrlFactUFEG=FU.IDCtrlFactUFEG
					WHERE FU.IdentificacionCliente=$p1;";
        $bindVars = array($fUfegObj->IdentificacionCliente);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion Cuotas a Facturar por Identificacion comercio
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarCuotaFacturarxIdentificacioncomercio($fUfegObj){
		$p1 	= $this->cadenaConexion->Param('IdentificacionComercio');
        $sql 	= " SELECT FU.IDCtrlFactUFEG,FU.FechaRegistro,PeriodoFacturacion,IDEntidad,FU.IDAval,IDPersonaCartera,IdentificacionCartera,PersonaCartera,IDPersonaLINCE,TipoIdentificacionCliente,IdentificacionCliente,NombreCliente,NombreEmpresa,CiudadCliente,EmailCliente,TelefonoCliente,FechaAutorizacion, PersonaComercio,IdentificacionComercio,Cuota, CodigoAutorizacion,IDTitulo,TipoTitulo,FechaTitulo,FechaVencimientoTitulo,FechaPago,ValorTitulo,ValorDescuento,ValorCapital,ValorOtros,ValorIntereses,ValorSeguroVida,ValorTotal,FechaDesembolso,Saldo,EstadoAval,TasaDescuento, estadoCuota,CodigoConveniobaloto,Referencia, CodigoConvenioBancolombia, CodigoBarras,IDTipoDocumento,IDCodigoMotivoNota, IDRegAsociado,PreFactura,FechaPREFactura,CUFE,FechaIngresoCUFE,Anulado,AnuladoPor,IDMotivoFact, FUC.IDCtrlFactUFEGCambio
					FROM [FacturacionElectronica].[Ctrl].[tbFacturacionUFEG] AS FU
					LEFT JOIN [FacturacionElectronica].[Ctrl].[tbFacturacionUFEGCambios] as FUC ON FUC.IDCtrlFactUFEG=FU.IDCtrlFactUFEG
					WHERE FU.IdentificacionComercio=$p1;";
        $bindVars = array($fUfegObj->IdentificacionComercio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta informacion de un Pago por IdPago
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaPagoxIdPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= "EXEC SP_ConsultarPago_IdPago @IDPago=$p1;";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Portafolio x id_pago
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaPortafolioxIdPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= "SELECT PG.IDPortafolio, P.Avales
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
				   WHERE PG.IDPago=$p1;";
        $bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta cliente x identificacion
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaClientexIdentificacion($clienteObj){
		$p1 	= $this->cadenaConexion->Param('TipoDocumento');
		$p2 	= $this->cadenaConexion->Param('Identificacion');
        $sql 	= "EXEC SP_Consultar_Cliente @IDTipoDocumento=$p1,@Identificacion=$p2;";
        $bindVars = array($clienteObj->TipoDocumento,$clienteObj->Identificacion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta a la tabla TipoDocumento
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTablaTipoDocumento(){
        $sql 	= "SELECT DISTINCT TD.TipoDocumento,TD.NombreTipoDocumento 
					FROM TiposDocumento AS TD
					INNER JOIN tbTiposDocumentoXPais AS TDP ON TDP.TipoDocumento=TD.TipoDocumento
					WHERE TDP.IDPais in (select DISTINCT IDPais from Refinancia.dbo.CONSedes WHERE Visible=1)
					ORDER BY TipoDocumento ASC;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta a la tabla TipoDocumento
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPortafolioxIDCliente($clienteObj){
		$p1 	= $this->cadenaConexion->Param('IDCliente');
        $sql 	= "SELECT DISTINCT P.IDPortafolio,LOWER(P.Portafolio) AS Portafolio
					FROM Negocios AS N
					INNER JOIN Portafolios AS P ON P.IDPortafolio=N.IDPortafolio
					WHERE N.IDCliente=$p1;";
		$bindVars = array($clienteObj->IDCliente);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Creditos por IDCliente
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarCreditoXIDCliente($clienteObj,$pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDCliente');
		$p2 	= $this->cadenaConexion->Param('IDPortafolio');
        $sql 	= "EXEC SP_ConsultarCredito_IDCliente @IDCliente=$p1, @IDPortafolio=$p2;";
        $bindVars = array($clienteObj->IDCliente,$pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Acuerdo por IDCliente
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarAcuerdoXIDCliente($clienteObj,$pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDCliente');
		$p2 	= $this->cadenaConexion->Param('IDPortafolio');
        $sql 	= "EXEC SP_ConsultarCuotaAcuerdo_IDCliente @IDCliente=$p1, @IDPortafolio=$p2;";
        $bindVars = array($clienteObj->IDCliente,$pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta a la tabla MotivoNotaxTipoDocumento
     * por medio de su PK id_usuario
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTablaMotivoNotaxTipoDocumento(){
        $sql 	= "SELECT 
				 [IdRegistro]
				,[IdTipoDocumentoFactura]
				,[FechaRegistro]
				,[Codigo]
				,[Nombre]
				FROM [Fte].[MotivoNotaxTipoDocumento]
				WHERE IdTipoDocumentoFactura=3
				ORDER BY Nombre ASC;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Opciones de Fraccionar un Pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarOpcionFraccionarPago(){
        $sql 	= "EXEC SP_Consultar_CantidadFraccion_Pago ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Tipo de Busqueda Pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarOpcionBusqueda($IDAplicacion){
		$p1 	= $this->cadenaConexion->Param('IDAplicacion');
        $sql 	= "EXEC [Gop].SP_Consultar_OpcionBusqueda @IDAplicacion=$p1;";
		$bindVars = array($IDAplicacion);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Tipo de Aplicacion
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarOpcionAplicacion(){
        $sql 	= "SELECT [IdAplicacion]
						 ,[Nombre]
						 ,[TipoFormato]
				   FROM [FacturacionElectronica].[dbo].[Aplicacion];";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Tipo de Busqueda Cliente
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarOpcionBusquedaCliente(){
        $sql 	= "EXEC SP_Consultar_OpcionBusqueda_Cliente ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Tipo origen pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTipoOrigenPago(){
        $sql 	= "EXEC SP_Consultar_TipoOrigen_Pago ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Tipo origen pago Devolucion
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTipoOrigenPago_Devolucion(){
        $sql 	= "SELECT IdTipoOrigenPago, TipoOrigenPago FROM TiposOrigenPago WHERE Devolucion = 1 ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Estado pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarEstadoPago(){
        $sql 	= "EXEC SP_Consultar_Estado_Pago ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Moneda pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarMonedaPago(){
        $sql 	= "EXEC SP_Consultar_Moneda ;";
        $result = $this->cadenaConexion->execute($sql);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consulta Banco pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarBancoPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPortafolio');
        $sql 	= "EXEC SP_Consultar_Banco @IDPortafolio=$p1;";
		$bindVars = array($pagosObj->IDPortafolio);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Combo Numero cuenta Banco pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarNCuentaPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('Banco');
        $sql 	= "EXEC SP_Consultar_NCuenta_Pago @Banco=$p1;";
        $bindVars = array($pagosObj->Banco);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
		//print_r($result);
        return $result;
    }
	
	/**
     * Consultar distribucion de pagos activas
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaDistribucionPagos($TipoDistribucion){
		$p1 	= $this->cadenaConexion->Param('IDTDistribucionP');
        $sql 	= "SELECT * FROM [Gop].[tb_distribucion_pago] WHERE IDTDistribucionP=$p1 AND Activo=1;";
		$bindVars = array($TipoDistribucion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consultar distribucion activa x IDDistribucion
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaDistribucionxIDDistribucion($DistribucionpagoObj){
		$p1 	= $this->cadenaConexion->Param('IDDistribucion');
        $sql 	= "SELECT * FROM [Gop].[tb_distribucion_pago] WHERE IDDistribucion=$p1 AND Activo=1;";
		$bindVars = array($DistribucionpagoObj->IDDistribucion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consultar tipo distribucion de pagos
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarTipoDistribucionPago(){
        $sql 	= "SELECT * FROM [Gop].[tb_tipo_distribucion_pago] WHERE Activo=1;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	
	/**
     * Consultar si un pago cuenta con Distribucion
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaDistribucionxPago($IDPago){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= "SELECT * FROM [Gop].[tb_pago_distribucion] AS PD
					INNER JOIN [Gop].[tb_distribucion_pago] AS DP ON DP.IDDistribucion=PD.IDDistribucion
					WHERE IDPago=$p1 AND Anulado=0;";
		$bindVars = array($IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consultar tipo de Distribucion de un pago
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaTipoDistribucionxPago($pagosObj){
		$p1 	= $this->cadenaConexion->Param('IDPago');
        $sql 	= "SELECT DISTINCT TDP.IDTDistribucionP,TDP.Nombre FROM [Gop].[tb_pago_distribucion] AS PD
				  INNER JOIN [Gop].[tb_distribucion_pago] AS DP ON DP.IDDistribucion=PD.IDDistribucion
				  INNER JOIN [Gop].[tb_tipo_distribucion_pago] AS TDP ON TDP.IDTDistribucionP=DP.IDTDistribucionP
				  WHERE IDPago=$p1 AND Anulado=0;";
		$bindVars = array($pagosObj->IDPago);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consultar tipo de Distribucion por IDDistribucion
     * Información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaTipoDistribucionxIDDistribucion($IDDistribucion){
		$p1 	= $this->cadenaConexion->Param('IDDistribucion');
        $sql 	= "SELECT DISTINCT TDP.IDTDistribucionP,TDP.Nombre 
					FROM [Gop].[tb_distribucion_pago] AS DP
					INNER JOIN [Gop].[tb_tipo_distribucion_pago] AS TDP ON TDP.IDTDistribucionP=DP.IDTDistribucionP
				  WHERE DP.IDDistribucion=$p1 AND DP.Activo=1;";
		$bindVars = array($IDDistribucion);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/**
     * Consulta partidas pendientes por identificar
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPPI(){
        $sql 	= " SELECT Count(1) AS PPI 
					FROM Pagos
					WHERE Fecha BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND IDCliente=0 AND IDTipoOrigenPago=30;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta total de pagos Npls
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPagosNpls(){
        $sql 	= " SELECT Count(1) AS PagosNpls 
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE Fecha BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND IDCliente!=0 AND IDTipoOrigenPago!=30 AND P.Avales =0;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta total de pagos Avales
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_CantidadPagosAvales(){
        $sql 	= " SELECT Count(1) AS PagosNpls 
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE Fecha BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND IDCliente!=0 AND IDTipoOrigenPago!=30 AND P.Avales >0;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta Pago aplicados Avales
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagosAplicadosAvales(){
        $sql 	= " SELECT Count(1) AS PagosAplicados 
					FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE FechaAplicacion BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND P.Avales >0 AND FechaAplicacion IS NOT NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta Pago no aplicados Avales
     * información general
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagosNoAplicadosAvales(){
        $sql 	= " SELECT Count(1) AS PagosNoAplicados 
				    FROM Pagos AS PG
					INNER JOIN Portafolios AS P ON P.IDPortafolio = PG.IDPortafolio
					WHERE Fecha BETWEEN DATEADD(mm, DATEDIFF(mm,0,GETDATE()), 0) AND DATEADD(ms,-3,DATEADD(mm, DATEDIFF(m,0,GETDATE()  )+1, 0))
					AND P.Avales >0 AND FechaAplicacion IS NULL;";
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta informacion Pagos avales
     * información de pago y distribucion
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarPagosAvalesDistribucion(){
		/*print_r($ClienteObj);
		$var1 = $this->cadenaConexion->Param('IDCliente');
        $var2 = $this->cadenaConexion->Param('IDPortafolio');*/
        $sql = "EXEC [dbo].[SP_Reporte_PagosAvales_distribucion]";
        /*$bindVars = array($ClienteObj->IDCliente,$ClienteObj->IDPortafolio);*/
        $result = $this->cadenaConexion->execute($sql);
        return $result;
    }
	
	/**
     * Consulta ultima facturacion en Dashboard
     * información Especifica
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarUltimaFacturacionDashboard($IDAplicacion){
		$p1 = $this->cadenaConexion->Param('IDAplicacion');
        $sql 	= " SELECT TOP 1 [TotalFacturasProcesarAprobadas] AS Aprobadas
					  ,[TotalFacturasNoProcesadasAprobadas] AS NoProcesadas
					  ,[TotalFacturasNoAprobadas] AS Desaprobadas
				  FROM [FacturacionElectronica].[dbo].[ResultadoFacturacion] AS RF
				  INNER JOIN [FacturacionElectronica].[dbo].[Programacion_Factura] AS PF ON PF.IdProgramacion=RF.IdProgramacion
				  WHERE PF.IdAplicacion=$p1 AND PF.Procesado=1
				  ORDER BY RF.FechaRegistro DESC;";
		$bindVars = array($IDAplicacion);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta ultima facturacion en Dashboard
     * información General
     *
     * @access public
     * @return object
     */
    public function DA_ConsultaUInfoFactDashboard($IDAplicacion){
		$p1 = $this->cadenaConexion->Param('IDAplicacion');
        $sql 	= " SELECT TOP 1 *
				  FROM [FacturacionElectronica].[dbo].[ResultadoFacturacion] AS RF
				  INNER JOIN [FacturacionElectronica].[dbo].[Programacion_Factura] AS PF ON PF.IdProgramacion=RF.IdProgramacion
				  WHERE PF.IdAplicacion=$p1 AND PF.Procesado=1
				  ORDER BY RF.FechaRegistro DESC;";
		$bindVars = array($IDAplicacion);
        $result = $this->cadenaConexion->execute($sql,$bindVars);
        return $result;
    }
	
	/**
     * Consulta a la tabla tbParametros de Facturacion Electronica
     * por el campo Nombre
     *
     * @access public
     * @return array
     */
	public function DA_ConsultartbParametrosFact($Parametro){
		$p1 = $this->cadenaConexion->Param('Nombre');
		$sql = "EXEC[FacturacionElectronica].[dbo].spParametros_Consultar @Nombre=$p1";
		$bindVars = array($Parametro);
		$result = $this->cadenaConexion->execute($sql, $bindVars);
		return $result;
	}
	
	/**
     * Ejecutar procesos Ini NPls
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function DA_EjecutarProcesoFacturacionNpls(){
		$sql = "EXEC [dbo].[sp_InformacionBasicaFacturacionNpls];";
		$result = $this->cadenaConexion->execute($sql);
		return $result;
	}
	
	/**
     * Ejecutar procesos Ini Ufeg
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function DA_EjecutarProcesoFacturacionUfeg(){
		$sql = "EXEC [FacturacionElectronica].[Ctrl].[spCargueFacturacionINI_Insertar];";
		$result = $this->cadenaConexion->execute($sql);
		return $result;
	}
	
	/**
     * Ejecutar procesos Ini Avales
     * Informacion General
     *
     * @access public
     * @return array
     */
	public function DA_EjecutarProcesoFacturacionAvales(){
		$sql = "EXEC [dbo].[sp_InformacionBasicaFacturacionAvales];";
		$result = $this->cadenaConexion->execute($sql);
		return $result;
	}
	
	/**
     * Consulta si existe una programacion
     * por el campo IDAplicacion, Procesado
     *
     * @access public
     * @return array
     */
	public function DA_ConsultarProgramacion_Factura($pfacturaObject){
		$p1 = $this->cadenaConexion->Param('IDAplicacion');
		$p2 = $this->cadenaConexion->Param('Procesado');
		$sql = "EXEC[FacturacionElectronica].[dbo].[spConsultar_Programacion_Factura] @IDAplicacion=$p1, @Procesado=$p2";
		$bindVars = array($pfacturaObject->IDAplicacion,$pfacturaObject->Procesado);
		$result = $this->cadenaConexion->execute($sql, $bindVars);
		return $result;
	}
	
	/**
     * Consulta los Modulos por Perfil asociados a la aplicación
     * por el campo id_perfil
     *
     * @access public
     * @return object
     */
    public function DA_ConsultarModuloxId_Perfil($SessionObj){
		$p1 = $this->cadenaConexion->Param('IDPerfil');
        $sql = "EXEC [Refinancia].[dbo].[sp_Consultar_ModuloxPerfil] @IDPerfil=$p1;";
        $bindVars = array($SessionObj->IDPerfil);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
		
	/**
     * Consulta las opciones  por modulo  asociados al perfil
     * por el campo id_modulo
     *
     * @access public
     * @return array
    */
    public function DA_ConsultarOpcionesxId_Modulo($ModuloOpcionObj){
		$p1 = $this->cadenaConexion->Param('IDModulo');
        $sql = "EXEC [Refinancia].[dbo].[sp_ConsultarOpcionesxModulo] @IDModulo=$p1;";
        $bindVars = array($ModuloOpcionObj->Id_Modulo);
        $result = $this->cadenaConexion->execute($sql, $bindVars);
        return $result;
    }
	
	/*------------------------------------------------ Funciones Especiales-----------------------------------------------------------*/

}
?>