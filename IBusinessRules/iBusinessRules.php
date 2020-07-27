<?php

interface iBusinessRules {

	public function BRconexion();
	 
	/* Consulta a la tabla Funcionarios por los campos id_usuario, Retirado, Bloqueado, Rol */
	public function BR_ConsultarUsuarioSesion($SessionObj);
	
	/* Consulta a la tabla Funcionarios y tbUsuarioAplicacion por el campo id_usuario */
	public function BR_ConsultarUsuarioxIDUsuario($SessionObj);
	
	/* Consulta a la tabla Funcionarios por el campo id_usuario */
	public function BR_ConsultarFuncionarioxIDUsuario($SessionObj);
	
	/* Consulta en la tabla tbparametrosGeneral la URL de Portal Unificado */
	public function BR_ConsultarURLPortalUnificado($parametro);
	
	/* Consultar la sesion si existe */
	public function BR_ConsultarSesion($SessionObj);
	
	// Actualizar la tabla tbsesion
	public function BR_ActualizartablaSesion($SessionObj);
	
	// Consulta a la tabla tbparametros por el campo Parametro
	public function BR_ConsultartablaTbparametros($Parametro);
	
	// Consulta a la tabla tbparametros por el campo Parametro informacion general
	public function BR_ConsultartablaTbparametrosGeneral($Parametro);
	
	// Consulta a la tabla tbparametrosSAB por el campo IDParametro
	public function BR_ConsultartablaTbparametrosApl($IDParametro);
	
	// Consulta a la tabla tbparametrosSAB por el campo IDParametro
	public function BR_ConsultartbParametrosFact($Parametro);
	
	// Consulta a la tabla tbparametrosSAB por el campo IDParametro
	public function BR_EstadoSistema();
	
	// Consulta de Cantidad de Funcionarios
	public function BR_CantidadUsuario($SessionObj);
	
	// Consulta a la tabla tbparametros de facturacion Electronica por el campo IdParametro
	public function BR_ConsultatbParametro($IdParametro);
	
	// Consulta la cantidad de pagos Aplicados
	public function BR_CantidadPagosAplicados();
	
	// Consulta la cantidad de pagos Aplicados
	public function BR_ConsultarPagosAplicados($Rango);
	
	// Consulta Reporte Partidas Pendientes por identificar
	public function BR_ConsultarPPINpls($Rango);
	
	// Consulta vista Partidas Pendientes por identificar 
	public function BR_ConsultarPPIN_Vista();
	
	// Consulta la cantidad de pagos No Aplicados
	public function BR_CantidadPagosNoAplicados();
	
	// Consulta de Cantidad de Notas debitos
	public function BR_CantidadNotasDebito();
	
	// Consulta de Cantidad de Notas Credito
	public function BR_CantidadNotasCredito();
	
	// Consulta la cantidad de Ini-Facturas NPls
	public function BR_CantidadInifacturaNpls();
	
	// Consulta los Registros de Ini-Facturas NPls
	public function BR_RegistrosInifacturaNpls();
	
	// Consulta la cantidad de Ini-Facturas Avales
	public function BR_CantidadInifacturaAvales();
	
	// Consulta los Registros de Ini-Facturas Avales
	public function BR_RegistrosInifacturaAvales();
	
	// Consulta la cantidad de Pre-Facturas Npls
	public function BR_CantidadPrefacturaNpls();
	
	// Consulta los Registros de Pre-Facturas Npls
	public function BR_RegistrosPrefacturaNpls();
	
	// Consulta la cantidad de Pre-Facturas Avales
	public function BR_CantidadPrefacturaAvales();
	
	// Consulta los Registros de Pre-Facturas Avales
	public function BR_RegistrosPrefacturaAvales();
	
	// Consulta la cantidad de Ini-Facturas en la Ufeg
	public function BR_CantidadInifacturaUfeg();
	
	// Consulta la cantidad de Ini-Facturas en la Ufeg
	public function BR_RegistrosInifacturaUfeg();
	
	// Consulta de Cantidad de de Prefractura Ufeg
	public function BR_CantidadPrefacturaUfeg();
	
	// Consulta la cantidad de Pre-Facturas en la Ufeg
	public function BR_RegistrosPrefacturaUfeg();
	
	// Consulta la Registros de Pre-Facturas informe en la Ufeg
	public function BR_RegistrosInfoPrefacturaUfeg();
	
	// Consulta la cantidad de Facturas en la Ufeg
	public function BR_CantidadFacturadoUfeg();
	
	// Consulta la Registros Facturados en la Ufeg
	public function BR_RegistrosFacturadoUfeg();
	
	// Consulta la cantidad de Facturas en la Ufeg
	public function BR_CantidadInconsistenciaUfeg();
	
	// Consulta la Registros Facturados en la Ufeg
	public function BR_RegistrosInconsistenciaUfeg();
	
	// Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEGCambio
	public function BR_RInconsistenciaUfegxIDCtrlFactUFEGCambio($factufegcambiosObj);
	
	// Consulta Registros inconsistencia en la Ufeg x IDCtrlFactUFEG
	public function BR_RInconsistenciaUfegxIDCtrlFactUFEG($factufegcambiosObj);
	
	// Consulta la cantidad de Cuotas anuladas en la Ufeg
	public function BR_CantidadCuotaAnulada();
	
	// Consulta registros de Cuotas a anuladas en la Ufeg
	public function BR_RegistrosCuotaAnulada();
	
	// Consulta la cantidad de Cuotas a facturar en la Ufeg
	public function BR_CantidadCuotaFacturar();
	
	// Consulta registros de Cuotas a facturar en la Ufeg
	public function BR_RegistrosCuotaFacturar();
	
	// Consulta la cantidad Facturas Autorizadas en la Ufeg
	public function BR_CantidadAutFacturaUfeg();
	
	// Consulta la cantidad Facturas Autorizadas en la Npls
	public function BR_CantidadAutFacturaNpls();
	
	// Consulta la cantidad Facturas Autorizadas en la Avales
	public function BR_CantidadAutFacturaAvales();
	
	// Consulta registros Facturas No Autorizadas Ufeg
	public function BR_RegistrosAutFacturaUfeg();
	
	// Consulta registros Facturas No Autorizadas Npls
	public function BR_RegistrosAutFacturaNpls();
	
	// Consulta couta facturar x IDCtrlFactUFEG
	public function BR_CuotaFacturarxIDCtrlFactUFEG($facturacionufegObj);
	
	// Consulta pre-facturas ufeg x IdFacturaCliente_UfegPre
	public function BR_PreFacturaUfegxIdFacturaClientePre($prefUfegObj);
	
	// Consulta la cantidad de Pagos Facturados Npls
	public function BR_CantidadFacturadoNpls();
	
	// Consulta los registros de Pagos Facturados Npls
	public function BR_RegistrosFacturadoNpls();
	
	// Consulta la cantidad de Pagos Facturados Avales
	public function BR_CantidadFacturadoAvales();
	
	// Consulta los registros de Pagos Facturados Avales
	public function BR_RegistrosFacturadoAvales();
	
	// Consulta la cantidad de Facturas generadas Npls
	public function BR_CantidadFacturasNpls();
	
	// Consulta la cantidad de Facturas No facturadas Npls
	public function BR_CantidadNoFacturasNpls();
	
	// Consulta los registros de Facturas generadas Npls
	public function BR_RegistrosFacturasNpls();
	
	// Consulta los registros de Facturas No generadas Npls
	public function BR_RegistrosNoFacturasNpls();
	
	// Consulta la cantidad de Facturas generadas Avales
	public function BR_CantidadFacturasAvales();
	
	// Consulta los registros de Facturas generadas Avales
	public function BR_RegistrosFacturasAvales();
	
	// Consulta de Cantidad de Factura
	public function BR_CantidadNoFacturado();
	
	// Consulta informacion de un Pago por IdPago
	public function BR_ConsultarPagoxIdPago($pagosObj);
	
	// Consulta informacion de un Pago por Identificacion Cliente 
	public function BR_ConsultarPagoxIdentCliente($pagosObj);
	
	// Consulta informacion de un Pago por Identificacion Cliente 
	public function BR_ConsultarClientexIdentCliente($clienteObj,$TipoBusqueda);
	
	// Consulta informacion de un Pago por IdPago
	public function BR_ConsultaPagoxIdPago($pagosObj);
	
	// Consulta informacion de portafolio por IdPago
	public function BR_ConsultaPortafolioxIdPago($pagosObj);
	
	// Consulta informacion de un Pago por IdPago
	public function BR_Desasociar_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Asociar Pago de la  la tabla Pago
	public function BR_Asociar_Pago($pagosObj,$clienteObj);
	
	// Aplicar Pago en la  la tabla Pago
	public function BR_Aplicar_Pago($pagosObj,$SessionObj);
	
	// Desasociar y asociar Pago de la  la tabla Pago
	public function BR_Desasociar_Asociar_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Consulta cliente x identificacion
	public function BR_ConsultaClientexIdentificacion($clienteObj);
	
	// Consulta a la tabla Tipo Documento
	public function BR_ComboOpcionTipoDocumento();
	
	// Consulta Portafolios del Cliente
	public function BR_ComboOpcionPortafolioxCliente($clienteObj);
	
	// Consulta Portafolios del Cliente x Pago
	public function BR_ComboOpcionPortafolioxClientePago($clienteObj,$pagosObj);
	
	// Combo Creditos vigentes del Cliente
	public function BR_ComboOpcionCredito($clienteObj,$pagosObj);
	
	// Combo Acuerdo vigentes del Cliente
	public function BR_ComboOpcionAcuerdo($clienteObj,$pagosObj);
	
	// Nota Debito de un Pago
	public function BR_NotaDebito_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Nota Credito de un Pago
	public function BR_NotaCredito_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Restituir una Cuota anulada
	public function BR_Restituir_Cuota($facturacionufegObj,$SessionObj);
	
	// Reliquidar una Cuota 
	public function BR_Reliquidar_Cuota($factufegcambiosObj,$SessionObj);
	
	// Notas Credito Cuota Ufeg Facturada
	public function BR_NotaCredito_Cuota_Ufeg($facturacionufegObj,$factufegcambiosObj,$SessionObj);
	
	// Consulta a la tabla MotivoNotaxTipoDocumento
	public function BR_ComboOpcionMotivoNotaxTipoDocumento();
	
	// Consulta si un Pago es de tipo Cheque
	public function BR_ConsultarPagoChequexIdPago($pagosObj);
	
	// Enlazar Cheques devueltos
	public function BR_Enlazar_Cheque($pagosObj);
	
	// Enlazar Cheques devueltos
	public function BR_Canje_Cheque($pagosObj);
	
	// Consulta informacion de un Pago por Numero de Credito
	public function BR_ConsultarPagoxCredito($pagosObj);
	
	// Consulta informacion de un Pago por Numero de Acuerdo
	public function BR_ConsultarPagoxAcuerdo($pagosObj);
	
	// Consulta informacion de cheques por Fecha de Pago
	public function BR_ConsultarChequexFechaPago($pagosObj);
	
	// Consulta informacion de Pagos sin Asociar por Fecha de Pago
	public function BR_ConsultarPagoSinAsociarxFechaPago($pagosObj);
	
	// Consulta informacion de Pagos por IDCliente
	public function BR_ConsultarPagoxIDCliente($pagosObj);
	
	// Consulta informacion de Pagos por IDCliente
	public function BR_ConsultarPagoxAsociar($pagosObj);
	
	// Consulta informacion de Saldos a Favor
	public function BR_ConsultarSaldosaFavor($pagosObj);
	
	// Consulta informacion traslado Tesoreria
	public function BR_ConsultarTrasladoTesoreria($pagosObj);
	
	// Consulta informacion Cuotas a Facturar por IdPersonaLince
	public function BR_ConsultarCuotaFacturarxIdPersonaLince($fUfegObj);
	
	// Consulta informacion Cuotas a Facturar por Identificacion Cliente
	public function BR_ConsultarCuotaFacturarxIdentificacioncliente($fUfegObj);
	
	// Consulta informacion Cuotas a Facturar por Identificacion Comercio
	public function BR_ConsultarCuotaFacturarxIdentificacioncomercio($fUfegObj);
	
	// Consulta Opciones de Fraccionar un Pago
	public function BR_ComboOpcionFraccionarPago();
	
	// Consulta Tipo de Busqueda Pago
	public function BR_ComboOpcionBusqueda($IDAplicacion);
	
	// Consulta Tipo de Aplicacion
	public function BR_ComboOpcionAplicacion();
	
	// Consulta Tipo origen pago
	public function BR_ComboOpcionTipoOrigenPago($pagosObj);
	
	// Consulta Tipo origen pago Devolucion
	public function BR_ComboOpcionTipoOrigenPago_Devolucion($pagosObj);
	
	// Consulta Estado pago
	public function BR_ComboOpcionTipoEstadoPago($pagosObj);
	
	// Consulta Moneda pago
	public function BR_ComboOpcionMonedaPago($pagosObj);
	
	// Consulta Banco pago
	public function BR_ComboOpcionBancoPago($pagosObj);
	
	// Combo Numero cuenta Banco pago
	public function BR_ComboOpcionNCuentaPago($pagosObj);
	
	// Crear Nuevo Pago
	public function BR_Crear_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Anular un Pago
	public function BR_Anular_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Editar un Pago de la tabla Pago
	public function BR_Editar_Pago($pagosObj);
	
	// Devolucion Partidas
	public function BR_DevolucionPPI_Pago($pagosObj,$SessionObj);
	
	// Consulta Tipo de Busqueda Pago
	public function BR_ComboOpcionBusquedaCliente();
	
	// Consulta distribución de Pago
	public function BR_ConsultaDistribucionPagos($TipoDistribucion);
	
	// Consulta Tipo de Busqueda Pago
	public function BR_ComboTipoDistribucionPago($IDTDistribucionP);
	
	// Crear Nueva distribucion
	public function BR_Crear_Distribucion($PagoDistribucionObj,$SessionObj);
	
	// Consultar si un pago cuenta con Distribucion
	public function BR_ConsultaDistribucionxPago($IDPago);
	
	// Consultar distribucion activa x IDDistribucion
	public function BR_ConsultaDistribucionxIDDistribucion($DistribucionpagoObj);
	
	// Consultar tipo de distribucion por pago
	public function BR_ConsultaTipoDistribucionxPago($pagosObj);
	
	// Consultar tipo de Distribucion por IDDistribucion
	public function BR_ConsultaTipoDistribucionxIDDistribucion($IDDistribucion);
	
	// Consulta partidas pendientes por identificar
	public function BR_CantidadPPI();
	
	// Consulta total de pagos Npls
	public function BR_CantidadPagosNpls();
	
	// Consulta total de pagos Avales
	public function BR_CantidadPagosAvales();
	
	// Consulta Pago aplicados Avales
	public function BR_ConsultarPagosAplicadosAvales();
	
	// Consulta Pago no aplicados Avales
	public function BR_ConsultarPagosNoAplicadosAvales();
	
	// Consulta Pago no aplicados Avales
	public function BR_ConsultarPagosAvalesDistribucion();
	
	// Autorizar Pre-Factura Ufeg
	public function BR_Autorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
	
	// Autorizar Pre-Factura Ufeg
	public function BR_Autorizar_PreFactura_Npls($prefclienteObj,$SessionObj);
	
	// Autorizar Todo Pre-Factura Npls
	public function BR_Autorizar_todo_PreFactura_Npls($accion,$SessionObj);
	
	// Autorizar Todo Pre-Factura Ufeg
	public function BR_Autorizar_todo_PreFactura_Ufeg($accion,$SessionObj);
	
	// Autorizar Todo Pre-Factura Avales
	public function BR_Autorizar_todo_PreFactura_Avales($accion,$SessionObj);
	
	// Desautorizar Pre-Factura Ufeg
	public function BR_Desautorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
	
	// Consulta ultima facturacion en Dashboard informacion especifica
	public function BR_ConsultarUltimaFacturacionDashboard($IDAplicacion);
	
	// Consulta informacion de aplicacion en general
	public function BR_ConsultarOpcionAplicacion();
	
	// Consulta ultima facturacion en Dashboard informacion general
	public function BR_ConsultaUInfoFactDashboard($IDAplicacion);
	
	// Ejecutar procesos Ini NPls
	public function BR_EjecutarProcesoFacturacionNpls();
	
	// Ejecutar procesos Ini Ufeg
	public function BR_EjecutarProcesoFacturacionUfeg();
	
	// Ejecutar procesos Ini Avales
	public function BR_EjecutarProcesoFacturacionAvales();
	
	// Ejecutar procesos Ini Negocio
	public function BR_EjecutarProcesoFacturacion($IDAplicacion);
	
	// Ejecutar procesos Ini
	public function BR_Crear_Programacion_Factura($pfacturaObject);
	
	// Ejecutar procesos Ini Negocio
	public function BR_ConsultarProgramacion_Factura($pfacturaObject);
	
	// Consulta los Modulos por Perfil asociados a la aplicación
	public function BR_ConsultarModuloxId_Perfil($SessionObj);
	
	// Consulta Opciones por modulo 
	public function BR_ConsultarOpcionesxId_Modulo($ModuloOpcionObj);
	
	/*------------------------------------------------ Funciones Especiales-----------------------------------------------------------*/
	
	/* Generar Pantalla de Error General */
	public function BR_ErrorGeneral($ErrorObj);
	
	/* Guardar en la tabla tbLogEComercial */
	public function BR_GuardartbLog($LogObj);
	
	/* Fin IBusinessRules */
}
?>