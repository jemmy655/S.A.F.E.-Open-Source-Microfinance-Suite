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
	$iduser = $_SESSION["log_id"];
//=====================================================================================================
include_once( "../core/entidad.datos.php");
include_once( "../core/core.deprecated.inc.php");
include_once( "../core/core.fechas.inc.php");
include_once( "../libs/sql.inc.php");
include_once( "../core/core.config.inc.php");
include_once( "../reports/PHPReportMaker.php");
//=====================================================================================================
$si_est 					= (isset($_GET["f70"])) ? $_GET["f70"] : false;
$si_freq 					= (isset($_GET["f71"])) ? $_GET["f71"] : false;
$si_conv 					= (isset($_GET["f72"])) ? $_GET["f72"] : false;

$estatus 					= (isset($_GET["f2"])) ? $_GET["f2"] : SYS_TODAS;
$frecuencia 				= (isset($_GET["f1"])) ? $_GET["f1"] : SYS_TODAS;
$convenio 					= (isset($_GET["f5"])) ? $_GET["f5"] : SYS_TODAS;

$estatus 					= (isset($_GET["estado"])) ? $_GET["estado"] : $estatus;
$frecuencia 				= (isset($_GET["periocidad"])) ? $_GET["periocidad"] : $frecuencia;
$frecuencia 				= (isset($_GET["frecuencia"])) ? $_GET["frecuencia"] : $frecuencia;
$convenio 					= (isset($_GET["convenio"])) ? $_GET["convenio"] : $convenio;

$es_por_estatus 			= "";
$es_por_frecuencia 		= "";
$es_por_convenio 			= "";

if($si_est==1){
	//$nest = eltipo("creditos_estatus", $estatus);
	$es_por_estatus 		= " AND creditos_solicitud.estatus_actual=$estatus ";
}
//
if($si_freq == 1){
	//$nfreq = eltipo("creditos_periocidadpagos", $frecuencia);
	$es_por_frecuencia = " AND creditos_solicitud.periocidad_de_pago=$frecuencia ";
	//AND creditos_solicitud.periocidad_de_pago=$f1
}
//
if($si_conv == 1){
	//$nconv = eltipo("creditos_tipoconvenio", $convenio);
	$es_por_convenio = " AND creditos_solicitud.tipo_convenio = $convenio ";
}
//AND operaciones_mvtos.fecha_afectacion>='$on_f' AND operaciones_mvtos.fecha_afectacion<='$off_f'
if ($fecha_inicial && $fecha_final){
	//$rango_de_fechas = " AND operaciones_mvtos.fecha_afectacion>='$on_f' AND operaciones_mvtos.fecha_afectacion<='$off_f' ";
	// AND captacion_cuentas.fecha_apertura>='$fecha_inicial' AND captacion_cuentas.fecha_apertura<='$fecha_final' ";
}
//=====================================================================================================

$on_f 		= (isset($_GET["on"])) ? $_GET["on"] : EACP_FECHA_DE_CONSTITUCION;
$off_f 		= (isset($_GET["off"])) ? $_GET["off"] : fechasys();
$f1			= (isset($_GET["f1"])) ? $_GET["f1"] : SYS_TODAS;
$input 		= (isset($_GET["out"])) ? $_GET["out"] : SYS_DEFAULT;

$setSql = "SELECT socios_general.codigo, CONCAT(socios_general.apellidopaterno, ' ', socios_general.apellidomaterno, ' ', socios_general.nombrecompleto) AS 'nombre_completo',  socios_tipoingreso.descripcion_tipoingreso AS 'tipo_de_ingreso',
			creditos_tipoconvenio.descripcion_tipoconvenio AS 'tipo_de_convenio', creditos_solicitud.numero_solicitud AS 'numero_de_solicitud', creditos_solicitud.fecha_ministracion AS 'fecha_de_ministracion',
			creditos_solicitud.monto_autorizado AS 'monto_ministrado',creditos_periocidadpagos.descripcion_periocidadpagos AS 'frecuencia_de_pagos',  creditos_solicitud.pagos_autorizados AS 'numero_de_pagos',
			creditos_solicitud.fecha_vencimiento AS 'fecha_de_vencimiento', socios_aeconomica_dependencias.descripcion_dependencia AS 'dependencia'
			FROM
	`creditos_periocidadpagos` `creditos_periocidadpagos`
		INNER JOIN `creditos_solicitud` `creditos_solicitud`
		ON `creditos_periocidadpagos`.
		`idcreditos_periocidadpagos` = `creditos_solicitud`.
		`periocidad_de_pago`
			INNER JOIN `socios_general` `socios_general`
			ON `creditos_solicitud`.`numero_socio` =
			`socios_general`.`codigo`
				INNER JOIN `socios_tipoingreso`
				`socios_tipoingreso`
				ON `socios_tipoingreso`.
				`idsocios_tipoingreso` = `socios_general`.
				`tipoingreso`
					INNER JOIN
					`socios_aeconomica_dependencias`
					`socios_aeconomica_dependencias`
					ON `socios_aeconomica_dependencias`.
					`idsocios_aeconomica_dependencias` =
					`socios_general`.`dependencia`
						INNER JOIN `creditos_tipoconvenio`
						`creditos_tipoconvenio`
						ON `creditos_tipoconvenio`.
						`idcreditos_tipoconvenio` =
						`creditos_solicitud`.`tipo_convenio`

			WHERE /** creditos_solicitud.numero_socio=socios_general.codigo  */
			/** AND socios_tipoingreso.idsocios_tipoingreso=socios_general.tipoingreso */
			/** AND creditos_solicitud.tipo_convenio=creditos_tipoconvenio.idcreditos_tipoconvenio  */
			/** AND creditos_periocidadpagos.idcreditos_periocidadpagos=creditos_solicitud.periocidad_de_pago  */
			/** AND socios_aeconomica_dependencias.idsocios_aeconomica_dependencias=socios_general.dependencia */
			/** AND creditos_solicitud.periocidad_de_pago='$f1'
			    AND  */
			creditos_solicitud.fecha_ministracion>='$on_f' AND  creditos_solicitud.fecha_ministracion<='$off_f'
			$es_por_convenio
			$es_por_estatus
			$es_por_frecuencia
			$rango_de_fechas";

//echo $setSql; exit;

if ($input!=OUT_EXCEL) {
		$oRpt = new PHPReportMaker();
	$oRpt->setDatabase(MY_DB_IN);
	$oRpt->setUser(RPT_USR_DB);
	$oRpt->setPassword(RPT_PWD_DB);
	$oRpt->setSQL($setSql);
	$oRpt->setXML("../repository/report14.xml");
	$oOut = $oRpt->createOutputPlugin($input);
	$oRpt->setOutputPlugin($oOut);
	$oRpt->run();		//	*/
} else {
  $filename = "export_from_" . date("YmdHi") . "_to_uid-" .  $iduser . ".xls";
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	sqltabla($setSql, "", "fieldnames");
}
?>