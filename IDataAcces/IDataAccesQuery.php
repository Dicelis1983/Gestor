<?php

// Declarar la interfaz 'iDataAccesQuery'
interface iDataAccesQuery {

    public function DAconexion_db($conObj);
	
	/* Consulta a la tabla Funcionarios por los campos id_usuario, Retirado, Bloqueado, Rol */
	public function DADatosCRUD($sql);
	
	/* Consulta a la tabla Funcionarios por los campos id_usuario, Retirado, Bloqueado, Rol */
	public function DA_ConsultarUsuarioSesion($SessionObj);
	
	/* Consulta a la tabla Funcionarios y tbUsuarioAplicacion por el campo id_usuario */
	public function DA_ConsultarUsuarioxIDUsuario($SessionObj);
	
	/* Consulta a la tabla Funcionarios por el campo id_usuario */
	public function DA_ConsultarFuncionarioxIDUsuario($SessionObj);
	
	/* Consulta en la tabla tbparametrosGeneral la URL de Portal Unificado */
	public function DA_ConsultarURLPortalUnificado($parametro);
	
	/* Consultar la sesion si existe */
	public function DA_ConsultarSesion($SessionObj);
	
	/* Consulta a la tabla tbparametros por el campo Parametro */
	public function DA_ConsultartablaTbparametros($Parametro);
	
	/* Consulta a la tabla tbparametrosSAB por el campo IDParametro */
	public function DA_ConsultartablaTbparametrosApl($IDParametro);
	
	/* Consulta el Estado del Sistema */
	public function DA_EstadoSistema();
	
	/* Consulta a la tabla usuarios */
	public function DA_ConsultarUsuarioGeneral($SessionObj);
	
	/* Consulta a la tabla tbSegmento */
	public function DA_ConsultarPagosAplicados();
	
	/* Consulta a la tabla tbSegmento */
	public function DA_ConsultarRegistroPagosAplicados($FechaInicial,$FechaFinal);
	
	/* Consulta Partidas Pendientes x Identificar */
	public function DA_ConsultarRegistroPPINpls($FechaInicial,$FechaFinal);
	
	/* Consulta Partidas Pendientes x Identificar vista */
	public function DA_ConsultarRegistroPPI_vista();
	
	/* Consulta a la tabla tbCentrodeCosto */
	public function DA_ConsultarPagosNoAplicados();
	
	/* Consulta a la tabla tbCanal */
	public function DA_ConsultarNotasDebito();
	
	/* Consulta a la tabla tbCanal */
	public function DA_ConsultarNotasCredito();
	
	/* Consulta la cantidad de Ini-Facturas NPls */
	public function DA_CantidadInifacturaNpls();
	
	/* Consulta los registros de Ini-Facturas NPls */
	public function DA_RegistrosInifacturaNpls();
	
	/* Consulta la cantidad de Ini-Facturas Avales */
	public function DA_CantidadInifacturaAvales();
	
	/* Consulta los Registros de Ini-Facturas Avales */
	public function DA_RegistrosInifacturaAvales();
	
	/* Consulta la cantidad de Pre-Facturas Npls */
	public function DA_CantidadPrefacturaNpls();
	
	/* Consulta los registros de Pre-Facturas NPls */
	public function DA_RegistrosPrefacturaNpls();
	
	/* Consulta la cantidad de Pre-Facturas Avales */
	public function DA_CantidadPrefacturaAvales();
	
	/* Consulta los registros de Pre-Facturas Avales */
	public function DA_RegistrosPrefacturaAvales();
	
	/* Consulta la cantidad de Ini-Facturas en la Ufeg */
	public function DA_CantidadInifacturaUfeg();
	
	/* Consulta Registros de Ini-Facturas en la Ufeg */
	public function DA_ConsultarInifacturaUfeg();
	
	/* Consulta la cantidad de Pre-Facturas en la Ufeg */
	public function DA_CantidadPrefacturaUfeg();
	
	/* Consulta Registros de Pre-Facturas en la Ufeg */
	public function DA_ConsultarPrefacturaUfeg();
	
	/* Consulta la cantidad de pre-Facturas informe en la Ufeg */
	public function DA_ConsultarInfoPrefacturaUfeg();
	
	/* Consulta la cantidad de registros Facturados en la Ufeg */
	public function DA_CantidadFacturadoUfeg();
	
	/* Consulta los registros Facturados en la Ufeg */
	public function DA_ConsultarFacturadoUfeg();
	
	/* Consulta la cantidad de registros Facturados en la Ufeg */
	public function DA_CantidadInconsistenciaUfeg();
	
	/* Consulta la cantidad de Inconsistencia en la Ufeg */
	public function DA_RegistrosInconsistenciaUfeg();
	
	/* Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEGCambio */
	public function DA_RegistrosInconsistenciaUfegxIDCtrlFactUFEGCambio($factufegcambiosObj);
	
	/* Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEG */
	public function DA_RInconsistenciaUfegxIDCtrlFactUFEG($factufegcambiosObj);
	
	/* Consulta los Registros con inconsistencia en la Ufeg */
	public function DA_CantidadCuotaFacturar();
	
	/* Consulta registros de Cuotas a facturar en la Ufeg */
	public function DA_RegistrosCuotaFacturar();
	
	/* Consulta la cantidad de Cuotas anuladas en la Ufeg */
	public function DA_CantidadCuotaAnulada();
	
	/* Consulta registros de Cuotas a anuladas en la Ufeg */
	public function DA_RegistrosCuotaAnulada();
	
	/* Consulta la cantidad Facturas Autorizadas en la Ufeg */
	public function DA_CantidadAutFacturaUfeg();
	
	/* Consulta la cantidad Facturas Autorizadas en la Npls */
	public function DA_CantidadAutFacturaNpls();
	
	/* Consulta la cantidad Facturas Autorizadas en la Npls */
	public function DA_CantidadAutFacturaAvales();
	
	/* Consulta registros Facturas No Autorizadas Ufeg */
	public function DA_RegistrosAutFacturaUfeg();
	
	/* Consulta registros Facturas No Autorizadas Npls */
	public function DA_RegistrosAutFacturaNpls();
	
	/* Consulta couta facturar x IDCtrlFactUFEG */
	public function DA_CuotaFacturarxIDCtrlFactUFEG($facturacionufegObj);
	
	/* Consulta pre-facturas ufeg x IdFacturaCliente_UfegPre */
	public function DA_PreFacturaUfegxIdFacturaClientePre($prefUfegObj);
	
	/* Consulta la cantidad de Pagos Facturados Npls */
	public function DA_CantidadFacturadoNpls();
	
	/* Consulta los registros de Pagos Facturados Npls */
	public function DA_RegistrosFacturadoNpls();
	
	/* Consulta la cantidad de Pagos Facturados Avales */
	public function DA_CantidadFacturadoAvales();
	
	/* Consulta los registros de Pagos Facturados Avales */
	public function DA_RegistrosFacturadoAvales();
	
	/* Consulta la cantidad de Facturas generadas Npls */
	public function DA_CantidadFacturasNpls();
	
	/* Consulta la cantidad de Facturas No facturadas Npls */
	public function DA_CantidadNoFacturasNpls();
	
	/* Consulta los registros de Facturas generadas Npls */
	public function DA_RegistrosFacturasNpls();
	
	/* Consulta la cantidad de Facturas generadas Avales */
	public function DA_CantidadFacturasAvales();
	
	/* Consulta los registros de Facturas generadas Avales */
	public function DA_RegistrosFacturasAvales();
	
	/* Consulta a la tabla tbCanal */
	public function DA_ConsultarNoFacturado();
	
	/* Consulta informacion de un Pago por IdPago */
	public function DA_ConsultarPagoxIdPago($pagosObj);
	
	/* Consulta informacion de un Pago por identificacion */
	public function DA_ConsultarPagoxIdentCliente($pagosObj);
	
	/* Consulta informacion de un Cliente por identificacion */
	public function DA_ConsultarClientexIdentCliente($clienteObj,$TipoBusqueda);
	
	// Consulta informacion de un Pago por IdPago
	public function DA_ConsultaPagoxIdPago($pagosObj);
	
	// Consulta Portafolio x id_pago
	public function DA_ConsultaPortafolioxIdPago($pagosObj);
	
	// Consulta cliente x identificacion
	public function DA_ConsultaClientexIdentificacion($clienteObj);
	
	// Consulta tabla tipo documento
	public function DA_ConsultarTablaTipoDocumento();
	
	// Consulta tabla tipo documento
	public function DA_ConsultarPortafolioxIDCliente($clienteObj);
	
	// Consulta Creditos por IDCliente
	public function DA_ConsultarCreditoXIDCliente($clienteObj,$pagosObj);
	
	// Consulta Acuerdo por IDCliente
	public function DA_ConsultarAcuerdoXIDCliente($clienteObj,$pagosObj);
	
	// Consulta a la tabla MotivoNotaxTipoDocumento
	public function DA_ConsultarTablaMotivoNotaxTipoDocumento();
	
	/* Consulta informacion de un Pago por IdPago */
	public function DA_ConsultarPagoChequexIdPago($pagosObj);
	
	// Consulta informacion de un Pago por Numero de Credito
	public function DA_ConsultarPagoxCredito($pagosObj);
	
	// Consulta informacion de un Pago por Numero de Acuerdo
	public function DA_ConsultarPagoxAcuerdo($pagosObj);
	
	// Consulta informacion de un Pago por Numero de Acuerdo
	public function DA_ConsultarChequexFechaPago($pagosObj);
	
	// Consulta informacion de Pagos sin Asociar por Fecha de Pago
	public function DA_ConsultarPagoSinAsociarxFechaPago($pagosObj);
	
	// Consulta informacion de Pagos x IDCliente
	public function DA_ConsultarPagoxIDCliente($pagosObj);
	
	// Consulta informacion de Pagos x Asociar
	public function DA_ConsultarPagoxAsociar($pagosObj);
	
	// Consulta informacion de Saldos a Favor
	public function DA_ConsultarSaldosaFavor($pagosObj);
	
	// Consulta informacion traslado Tesoreria
	public function DA_ConsultarTrasladoTesoreria($pagosObj);
	
	// Consulta informacion Cuotas a Facturar por IdPersonaLince
	public function DA_ConsultarCuotaFacturarxIdPersonaLince($fUfegObj);
	
	// Consulta informacion Cuotas a Facturar por Identificacion cliente
	public function DA_ConsultarCuotaFacturarxIdentificacioncliente($fUfegObj);
	
	// Consulta informacion Cuotas a Facturar por Identificacion comercio
	public function DA_ConsultarCuotaFacturarxIdentificacioncomercio($fUfegObj);
	
	// Consulta Opciones de Fraccionar un Pago
	public function DA_ConsultarOpcionFraccionarPago();
	
	// Consulta Tipo de Busqueda Pago
	public function DA_ConsultarOpcionBusqueda($IDAplicacion);
	
	// Consulta Tipo de Aplicacion
	public function DA_ConsultarOpcionAplicacion();
	
	// Consulta Tipo de Busqueda Cliente
	public function DA_ConsultarOpcionBusquedaCliente();
	
	// Consulta Tipo origen pago
	public function DA_ConsultarTipoOrigenPago();
	
	// Consulta Tipo origen pago Devolucion
	public function DA_ConsultarTipoOrigenPago_Devolucion();
	
	// Consulta Estado pago
	public function DA_ConsultarEstadoPago();
	
	// Consulta Moneda pago
	public function DA_ConsultarMonedaPago();
	
	// Consulta Banco pago
	public function DA_ConsultarBancoPago($pagosObj);
	
	// Combo Numero cuenta Banco pago
	public function DA_ConsultarNCuentaPago($pagosObj);
	
	// Consultar distribucion de pagos activas
	public function DA_ConsultaDistribucionPagos($IDTipoDistribucion);
	
	// Consultar distribucion activa x IDDistribucion
	public function DA_ConsultaDistribucionxIDDistribucion($DistribucionpagoObj);
	
	// Consultar tipo distribucion de pagos
	public function DA_ConsultarTipoDistribucionPago();
	
	// Consultar si un pago cuenta con Distribucion
	public function DA_ConsultaDistribucionxPago($IDPago);
	
	// Consultar tipo de Distribucion de un pago
	public function DA_ConsultaTipoDistribucionxPago($pagosObj);
	
	// Consultar tipo de Distribucion por IDDistribucion
	public function DA_ConsultaTipoDistribucionxIDDistribucion($IDDistribucion);
	
	// Consulta partidas pendientes por identificar
	public function DA_CantidadPPI();
	
	// Consulta total de pagos Npls
	public function DA_CantidadPagosNpls();
	
	// Consulta total de pagos Avales
	public function DA_CantidadPagosAvales();
	
	// Consulta Pago aplicados Avales
	public function DA_ConsultarPagosAplicadosAvales();
	
	// Consulta Pago no aplicados Avales
	public function DA_ConsultarPagosNoAplicadosAvales();
	
	// Consulta informacion Pagos avales
	public function DA_ConsultarPagosAvalesDistribucion();
	
	// Consulta ultima facturacion en Dashboard informacion especifica
	public function DA_ConsultarUltimaFacturacionDashboard($IDAplicacion);
	
	// Consulta ultima facturacion en Dashboard informacion general
	public function DA_ConsultaUInfoFactDashboard($IDAplicacion);
	
	// Consulta parametros en la tabla tbParametro
	public function DA_ConsultartbParametrosFact($IDAplicacion);
	
	// Ejecutar procesos Ini NPls
	public function DA_EjecutarProcesoFacturacionNpls();
	
	// Ejecutar procesos Ini Ufeg
	public function DA_EjecutarProcesoFacturacionUfeg();
	
	// Ejecutar procesos Ini Avales
	public function DA_EjecutarProcesoFacturacionAvales();
	
	// Consulta si existe una programacion
	public function DA_ConsultarProgramacion_Factura($pfacturaObject);
	
	// Consulta los Modulos por Perfil asociados a la aplicación
	public function DA_ConsultarModuloxId_Perfil($SessionObj);
	
	// Consulta las opciones por modulo
	public function DA_ConsultarOpcionesxId_Modulo($ModuloOpcionObj);
	/*------------------------------------------------ Funciones Especiales-----------------------------------------------------------*/
	
}
?>