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
include_once "../core/entidad.datos.php";
include_once "../core/core.deprecated.inc.php";
include_once "../core/core.fechas.inc.php";
include_once "../libs/sql.inc.php";
include_once "../core/core.config.inc.php";
include_once "../core/core.common.inc.php";

	$id 		= $_GET["id"];
	$filter 	= "";
	$oficial 	= elusuario($iduser);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listado de Personas</title>
</head>
<link href="../css/reporte.css" rel="stylesheet" type="text/css">
<body>
<!-- -->
<?php
echo "$head_pagina
<p class='bigtitle'>LISTADO DE INTEGRANTES DE GRUPOS</p>
";
	$xG = new cGrupo($id);
	echo $xG->getFicha(true);

	$sqlids = "SELECT codigo FROM socios_general WHERE grupo_solidario=$id";
	$rs = mysql_query($sqlids);
		
		while($rws = mysql_fetch_array($rs)) {
			$socio		= $rws["codigo"];
			$xSoc = new cSocio($socio);
			$xSoc->init();
			echo $xSoc->getFicha();
		}

	
echo getRawFooter();
?>
</body>
</html>
