<?php

// Declarar la interfaz 'iDataAccesUpdate'
interface iDataAccesUpdate {
	
	/* Actualizar la tabla de tbsesion */
	public function DAU_ActualizartablaSesion($SessionObj);
	
	/* Insertar nuevo registros en la tabla tbLogEComercial */
	public function DAU_GuardartbLog($LogObj);
	
	// Anula un pago, si este pago es del periodo anterior o está facturado, en caso contrario no hace nada
	public function DAU_Anular_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Desasocia un Pagp, si este pago
	public function DAU_Desasociar_Pago($pagosObj);
	
	// Asociar Pago de la  la tabla Pago
	public function DAU_Asociar_Pago($pagosObj,$clienteObj);
	
	// Aplicar Pago en la  la tabla Pago
	public function DAU_Aplicar_Pago($pagosObj,$SessionObj);
	
	// Nota Debito de un Pago
	public function DAU_NotaDebito_Pago($pagosObj);
	
	// Nota Credito de un Pago
	public function DAU_NotaCredito_Pago($pagosObj);
	
	// Restituir una Cuota anulada
	public function DAU_Restituir_Cuota($facturacionufegObj,$SessionObj);
	
	// Restituir una Cuota anulada
	public function DAU_Reliquidar_Cuota($factufegcambiosObj,$SessionObj);
	
	// Restituir una Cuota anulada
	public function DAU_NotaCredito_Cuota_Ufeg($facturacionufegObj,$factufegcambiosObj,$SessionObj);
	
	// Enlazar Cheques devueltos
	public function DAU_Enlazar_Cheque($pagosObj);
	
	// Canjear un Cheque
	public function DAU_Canje_Cheque($pagosObj);
	
	// Crear Nuevo Pago
	public function DAU_Crear_Pago($pagosObj,$clienteObj,$SessionObj);
	
	// Editar un Pago de la tabla Pago
	public function DAU_Editar_Pago($pagosObj);
	
	// Devolucion, Permite cambiar el Tipo origen 
	public function DAU_DevolucionPPI_Pago($pagosObj,$SessionObj);
	
	// Crear Nueva distribucion
	public function DAU_Crear_Distribucion($PagoDistribucionObj,$SessionObj);
	
	// Autorizar Pre-Factura Ufeg
	public function DAU_Autorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
	
	// Autorizar todo Pre-Factura Npls
	public function DAU_Autorizar_todo_PreFactura_Npls($accion,$SessionObj);
	
	// Autorizar todo Pre-Factura Ufeg
	public function DAU_Autorizar_todo_PreFactura_Ufeg($accion,$SessionObj);
	
	// Autorizar todo Pre-Factura Avales
	public function DAU_Autorizar_todo_PreFactura_Avales($accion,$SessionObj);
	
	// Autorizar Pre-Factura Npls
	public function DAU_Autorizar_PreFactura_Npls($prefclienteObj,$SessionObj);
	
	// Desautorizar Pre-Factura Ufeg
	public function DAU_Desautorizar_PreFactura_Ufeg($prefUfegObj,$SessionObj);
	
	// Desautorizar Pre-Factura Ufeg
	public function DAU_Crear_Programacion_Factura($pfacturaObject);
}
?>