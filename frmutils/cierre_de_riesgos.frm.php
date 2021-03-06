<?php
//=====================================================================================================
//=====>	INICIO_H
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	$theFile					= __FILE__;
	$permiso					= getSIPAKALPermissions($theFile);
	if($permiso === false){		header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//<=====	FIN_H

//=====================================================================================================
include_once("../core/entidad.datos.php");
include_once("../core/core.deprecated.inc.php");
include_once("../core/core.fechas.inc.php");
include_once("../core/core.config.inc.php");
include_once("../core/core.captacion.inc.php");
include_once("../core/core.riesgo.inc.php");
include_once("../core/core.seguimiento.inc.php");
include_once("../core/core.creditos.inc.php");
include_once("../core/core.operaciones.inc.php");
include_once("../core/core.common.inc.php");
include_once("../core/core.html.inc.php");
include_once("../core/core.db.inc.php");

include_once("../core/core.contable.inc.php");
include_once("../core/core.contable.utils.inc.php");

ini_set("display_errors", "off");
ini_set("max_execution_time", 900);
    
$key		 		= (isset($_GET["k"]) ) ? true : false;
$parser				= (!isset($_GET["s"]) ) ? false : $_GET["s"];

    //Obtiene la llave del
//if ($key == MY_KEY) {
	$messages		= "";
	$fechaop		= parametro("f", fechasys());

/**
 * Generar el Archivo HTMl del LOG
 * eventos-del-cierre + fecha_de_cierre + .html
 *
 */

	$aliasFil	= getSucursal() . "-eventos-al-cierre-de-riesgos-del-dia-$fechaop";
	$xLog		= new cFileLog($aliasFil);
	$idrecibo	= DEFAULT_RECIBO;
	$xL			= new cSQLListas();
	//$xRec		= new cReciboDeOperacion(12);
	//$xRec->setGenerarPoliza();
	//$xRec->setForceUpdateSaldos();
	//$idrecibo	=  $xRec->setNuevoRecibo(1,1,$fechaop, 1, 12, "CIERRE_DE_SEGUIMIENTO", "NA", "ninguno", "NA", DEFAULT_GRUPO);
	//$xRec->setNumeroDeRecibo($idrecibo);

$messages 		.= "=======================================================================================\r\n";
$messages 		.= "=========================		" . EACP_NAME . " \r\n";
$messages 		.= "=========================		" . getSucursal() . " \r\n";
$messages 		.= "=======================================================================================\r\n";
$messages 		.= "=========================		INICIANDO EL CIERRE DE RIESGOS ===================\r\n";
$messages 		.= "=========================		RECIBO: $idrecibo				   ====================\r\n";
$messages 		.= "=======================================================================================\r\n";

if (MODULO_AML_ACTIVADO == true){
	$mql		= new MQL();
	//crear arbol de relaciones
	$xUtils	= new cPersonas_utils();
	$xUtils->setCrearArbolRelaciones();
		
	//Validar perfiles transaccionales
	//Validar Documentos
	//TODO: Agregar cierre de riesgos
	//checar documentos de todos los socios
	$OSoc		= new cSocios_general();
	$rs			= $OSoc->query()->select()->exec();
	foreach ($rs as $data){
		$OSoc->setData($data);
		$xAml	= new cAMLPersonas($OSoc->codigo()->v());
		$xAml->init();
		//$xAml->setForceAlerts();
		$xAml->setVerificarDocumentosCompletos($fechaop);
		$xAml->setVerificarDocumentosVencidos($fechaop);
		$messages		.= $xAml->getMessages(OUT_TXT);	
		
		/*$xAml	= new cAML();
		$xAml->setForceAlerts(true);
		$xAml->sendAlerts(getUsuarioActual(), $PersonaDeDestino, $TipoDeAlerta);*/
		//envio de informes
		//TODO: Agregar envio de informes
		//checar perfil transaccional mensual
	}
	//verificar operaciones de 6 meses excedidas de maximo permitido
	$sql2 = "SELECT
	`operaciones_recibos`.`fecha_operacion`              AS `fecha`,
	`operaciones_recibos`.`numero_socio`                 AS `persona`,
	COUNT(`operaciones_recibos`.`idoperaciones_recibos`) AS `operaciones`,
	SUM(`operaciones_recibos`.`total_operacion`)         AS `monto`
	FROM
	`operaciones_recibos` `operaciones_recibos`
	WHERE
	(`operaciones_recibos`.`fecha_operacion` = '$fechaop')
	GROUP BY
	`operaciones_recibos`.`numero_socio`";
	$rs1			= $mql->getDataRecord($sql2);
	foreach($rs1 as $rw1){
		$xAml			= new cAMLPersonas($rw1["persona"]);
		$xF				= new cFecha();
		$fecha_inicial	= $xF->setRestarMeses(6, $fechaop);
		$obj			= $xAml->getOAcumuladoDeOperaciones($fecha_inicial, $fechaop);
		
	}
	//Relaciones Recursivas
	
	//$xUtils				= new cAMLUtils();
	$xCML				= new cAML();
	
} else {
	$messages		.= "=========================\tNO ACTIVADO\t====================\r\n";
}

$xLog->setWrite($messages);
$xLog->setClose();
if(ENVIAR_MAIL_LOGS == true){ $xLog->setSendToMail("TR.Eventos del Cierre de Riesgos"); }
	if ($parser != false){ 
		header("Location: ./cierre_de_sistema.frm.php?s=true&k=" . $key . "&f=$fechaop");
	}
	
//}

?>
