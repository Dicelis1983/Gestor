<?php
//Clase Trasversal para objeto Session
class SessionObject{
	public 	$IDSesion="";	
	public 	$IDUsuario="";
	public 	$Tocken="";
	public	$Logueado="";
	public	$Retirado="";
	public	$Bloqueado="";
	public	$Rol="";
	public	$IDAplicacion="";
	public	$IDPerfil="";
}

class ErrorObject{
	public 	$CodigoError="";	
	public 	$Descripcion="";
	public 	$URLPortal="";
	public 	$URLPortalContingencia="";
}

class LogObject{
	public 	$IDLogEComercial="";
	public 	$FechaRegistro="";
	public 	$Tabla="";
	public 	$IDTabla="";
	public	$TipoMovimiento="";
	public	$IDRegistro="";
	public	$Dato="";
	public	$IDUsuario="";
	public	$Usuario="";
}

class PagosObject{
	Public $IDPago				="";
	Public $IdentCliente		="";
	Public $IDRefinancia		="";
	Public $IDCliente			="";
	Public $IDCuota				="";
	Public $IDPortafolio		="";
	Public $IDMoneda			="";
	Public $IDEstadoPago		="";
	Public $IDTipoOrigenPago	="";
	Public $AsociadoA			="";
	Public $DetalleAsociacion	="";
	Public $Credito				="";
	Public $Acuerdo				="";
	Public $Fecha				="";
	Public $Valor				="";
	Public $FechaPago			="";
	Public $Origen				="";
	Public $Banco				="";
	Public $NroComprobante		="";
	Public $NroCuenta			="";
	Public $Detalles			="";
	Public $FechaIngreso		="";
	Public $CargadoDesde		="";
	Public $TipoPago			="";
	Public $FechaAplicacion		="";
	Public $AplicadoPor			="";
	Public $NAplicadoPor		="";
	Public $IDRecaudadoPor		="";
	Public $RecaudadoPor		="";
	Public $NRecaudadoPor		="";
	Public $PeriodoAplicaRecaudo="";
	Public $CodigoBarras		="";
	Public $PreFactura			="";
	Public $FechaPrefactura		="";
	Public $CUFE				="";
	Public $FechaIngresoCUFE	="";
	Public $IDTipoDocumento		="";
	Public $IDPagoAsociado		="";
	Public $IDCodigoMotivoNota	="";
	Public $Anulado				="";
	Public $Eliminado			="";
}

class ClienteObject{
	public 	$IDCliente="";
	public 	$TipoDocumento="";
	public 	$Identificacion="";
	public 	$Cliente="";
	public	$Nombre1="";
	public	$Nombre2="";
	public	$Apellido1="";
	public	$Apellido2="";
	public	$ValorBuscado="";
}

class PagoDistribucionObject{
	public 	$IDPagoDistribucion="";
	public 	$IDPago="";
	public 	$IDDistribucion="";
	public 	$FechaRegistro="";
	public	$IDusuario="";
	public	$RegistradoPor="";
	public	$NRegistradoPor="";
	public	$Valor="";
}

class FacturacionUFEGObject{
	public 	$IDCtrlFactUFEG="";
	public 	$FechaRegistro="";
	public 	$PeriodoFacturacion="";
	public 	$IDAval="";
	public	$NroTitulo="";
	public	$IDPersonaLINCE="";
	public	$TipoIdentificacionCliente="";
	public	$IdentificacionCliente="";
	public	$IdentificacionComercio="";
	public	$IDTitulo="";
	public	$Cuota="";
	public	$IDCodigoMotivoNota="";
	public	$TipoFacturacion="";
}

class tbFacturacionUFEGCambiosObject{
	public 	$IDCtrlFactUFEGCambio="";
	public 	$FechaRegistro="";
	public 	$IDCtrlFactUFEG="";
	public 	$IDAval="";
	public	$NroTitulo="";
	public	$ValorCapitalINI="";
	public	$ValorInteresesINI="";
	public	$ValorSeguroVidaINI="";
	public	$ValorOtrosINI="";
	public	$ValorTotalINI="";
	public	$ValorCapitalNVO="";
	public	$ValorInteresesNVO="";
	public	$ValorSeguroVidaNVO="";
	public	$ValorOtrosNVO="";
	public	$ValorTotalNVO="";
	public	$NovedadCambio="";
	public	$Autorizado="";
	public	$IDCtrlFactUFEGNota="";
}

class PreFacturaCliente_UfegObject{
	public 	$IdFacturaCliente_UfegPre="";
	public 	$Aprobado="";
	public 	$FechaAprobacion="";
}

class PreFacturaClienteObject{
	public 	$IdFacturaClientePre="";
	public 	$Aprobado="";
	public 	$FechaAprobacion="";
	public 	$DocumentoUsuarioAprueba="";
}

class ProgramacionFacturaObject{
	public 	$IdProgramacion="";
	public 	$FechaRegistro="";
	public 	$FechaInicioProgramada="";
	public 	$IdAplicacion="";
	public 	$EnProceso="";
	public 	$Procesado="";
	public 	$FechaProcesado="";
}

Class ModuloOpcionObject {
	public $Id_ModuloOpcion="";
	public $Id_Modulo="";
	public $Id_Opcion="";
	public $Activo="";
	public $Fecha_Registro="";
	public $IdUsuario_Registrado_Por="";
}
	
	
Class OpcionObject {
	public $Id_Opcion="";
	public $Nombre="";
	public $Titulo="";
	public $Funcion="";
	public $Activo="";
	public $Fecha_Registro="";
	public $Descripcion="";
	public $IdUsuario_Registrado_Por="";
}


Class DistribucionpagoObject {
	public $IDDistribucion="";
	public $IDTDistribucionP="";
	public $FechaRegistro="";
	public $IDusuario="";
	public $RegistradoPor="";
	public $NRegistradoPor="";
	public $Nombre="";
	public $Descripcion="";
	public $CodigoProducto="";
	public $Activo="";
	public $Facturable="";
}
?>