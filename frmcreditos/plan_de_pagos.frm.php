<?php
/**
 * @author Balam Gonzalez Luis Humberto
 * @version 0.0.01
 * @package
 */
//=====================================================================================================
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	include_once("../core/core.db.inc.php");
	$theFile			= __FILE__;
	$permiso			= getSIPAKALPermissions($theFile);
	if($permiso === false){	header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//=====================================================================================================
$xHP		= new cHPage("", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$msg		= "";
$jxc 		= new TinyAjax();

$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");

$jxc ->process();

$fecha		= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE); 
$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);


$xHP->init();

$xFRM		= new cHForm("frm", "plan_de_pagos");
if($credito > DEFAULT_CREDITO){
	$xCred		= new cCredito($credito);
	$xCred->init();
	$persona	= $xCred->getClaveDePersona();
	
	$xFRM->OHidden("idsolicitud", $credito, "");
	$xFRM->OHidden("idsocio", $persona, "");
	
	$xFRM->addHElem( $xCred->getFicha(true, "", false, true) );
	
	if($xCred->getTipoEnSistema() == CREDITO_PRODUCTO_NOMINA){
		
	}
	
} else {
	$xFRM->addJsBasico();
	$xFRM->addCreditBasico();
	$xFRM->addSubmit();
}

echo $xFRM->get();

$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>