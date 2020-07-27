<?php
/*
Gestor Administrativo 0.1.0
Librería adodbv5.20.3
htmlpurifier-4.7.0
PHP Anti-XSS Library, GNU Public License v2.0
*/

// Nombre de la carpeta del Gestor
define("APP","GOperativo");

// Nombre extenso de la aplicacion
define("NOMBRE_APLICACION","Gestor Operativo");

// Nombre abreviado de la aplicacion
define("NOMBRE_ABREVIADO","G. Operativo");

// Segundos en tiempo de espera de los mensajes de error
define("TIEMPO_MSG_ERROR",10);

// Control del menu princial, 1: Extender, 0: Contraer
define("MENU_EXTENDIDO",1);

// Ruta raiz general
define("CORE",$_SERVER["DOCUMENT_ROOT"]);

// Ruta raiz especifica de la aplicacion
define("CORE_INSTANCE",CORE."/".APP);
?>
