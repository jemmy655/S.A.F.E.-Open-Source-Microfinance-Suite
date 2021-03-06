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
$xHP	     = new cHPage("TR.Consulta en Lista_Negra", HP_SERVICE);
include_once("../libs/aml.jaro-winkler.inc.php");

$txt		= "";
$nombre		= parametro("nombre"); $nombre	= parametro("n", $nombre);
$apaterno	= parametro("apaterno"); $apaterno	= parametro("p", $apaterno);
$amaterno	= parametro("amaterno"); $amaterno	= parametro("m", $amaterno);
//sanear la variable para obtener el primer id.
$nombre		= getPalabraDeFrase($nombre);
$apaterno	= getPalabraDeFrase($apaterno);
$amaterno	= getPalabraDeFrase($amaterno);
//
$UseMeta	= parametro("metaphone", false, MQL_BOOL);
$UseJW		= parametro("jarowinkler", false, MQL_BOOL);
$umbral		= parametro("umbral", 80, MQL_INT);
$getPDF		= parametro("report", false, MQL_BOOL);
$ret		= parametro("return", false, MQL_BOOL);
$mails		= getEmails($_REQUEST);
//st VARCHAR(55)) RETURNS varchar(128) CHARSET utf8
//jaro_winkler_similarity`(in1 VARCHAR(255),in2 VARCHAR(255)) RETURNS FLOAT

//agregar PDF
$xFMT				= new cFormato(8801);
$txtLst				= "";

//TODO: Cambiar

$action		= "LIST";

$ByNombre		= ($nombre == "") ? "" :  " AND (nombrecompleto SOUNDS LIKE '%$nombre%' OR nombrecompleto LIKE '%$nombre%') ";
$ByAPaterno		= ($apaterno == "") ? "" : " AND (apellidopaterno SOUNDS LIKE '%$apaterno%' OR apellidopaterno LIKE '%$apaterno%') ";
$ByAMaterno		= ($amaterno == "") ? "" : " AND (apellidomaterno SOUNDS LIKE '%$amaterno%' OR apellidomaterno LIKE '%$amaterno%') ";
//Metaphone
$ByMNombre		= ($nombre == "") ? "" :  " AND func_DoubleMetaphone(nombrecompleto) = func_DoubleMetaphone('$nombre') ";
$ByMAPaterno	= ($apaterno == "") ? "" : " AND func_DoubleMetaphone(apellidopaterno) = func_DoubleMetaphone('$apaterno') ";
$ByMAMaterno	= ($amaterno == "") ? "" : " AND func_DoubleMetaphone(apellidomaterno) = func_DoubleMetaphone('$amaterno') ";
//Jaro Winkler
$ByJWNombre		= ($nombre == "") ? "" :  " AND jaro_winkler_similarity(nombrecompleto, '$nombre') >= $umbral  ";
$ByJWAPaterno	= ($apaterno == "") ? "" : " AND jaro_winkler_similarity(apellidopaterno, '$apaterno' ) >= $umbral ";
$ByJWAMaterno	= ($amaterno == "") ? "" : " AND jaro_winkler_similarity(apellidomaterno, '$amaterno' ) >= $umbral ";

$OrderBy		= ($UseMeta == true OR $UseJW) ? "" : "ORDER BY	`socios_general`.`apellidopaterno`,	`socios_general`.`apellidomaterno`,	`socios_general`.`nombrecompleto` ";

if($ByAMaterno != "" AND $ByAPaterno != "" ){
	$ByAPaterno	= "AND ( (apellidopaterno SOUNDS LIKE '%$apaterno%' OR apellidopaterno LIKE '%$apaterno%') OR (apellidomaterno SOUNDS LIKE '%$amaterno%' OR apellidomaterno LIKE '%$amaterno%') ) ";
	$ByAMaterno	= "";
}

$sql 	= "SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'soundlike' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . " AND (`tipoingreso`= " . TIPO_INGRESO_SDN . " OR `nivel_de_riesgo_aml` = 100)
		$ByNombre $ByAPaterno $ByAMaterno $OrderBy LIMIT 0,10";
if($UseJW == true){
	$sql 	.= " UNION SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'jarowinkler' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . " AND (`tipoingreso`= " . TIPO_INGRESO_SDN . " OR `nivel_de_riesgo_aml` = 100)
			$ByJWNombre  $ByJWAPaterno $ByJWAMaterno $OrderBy
			LIMIT 0,10";	
}
if($UseMeta == true){
	$sql 	.= " UNION SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'metaphone' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . "
		AND (`tipoingreso`= " . TIPO_INGRESO_SDN . "	OR `nivel_de_riesgo_aml` = 100)
			$ByMNombre $ByMAPaterno $ByMAMaterno $OrderBy LIMIT 0,10
			";	
}

//exit($sql);
header('Content-type: application/json');

$json			= array();
$mql			= new MQL();
$rs				= $mql->getRecordset($sql);
$idx			= 0;
$xLng			= new cLang();
if($rs){
	while ($row = $rs->fetch_assoc()) {
		foreach($row as $campo => $valor){
			if ( is_string($valor) ){
				$valor		= htmlentities($valor); //htmlentities( (string) $valor, ENT_QUOTES, 'utf-8', FALSE);
			}
			$json["record_$idx"][$campo]	= $valor; //base64_encode($valor);//utf8_encode($valor);
		}
		$idx++;
		//Generar el Acuse
		if($getPDF == true){
			$id			= $row["codigo"];
			$tipo		= $row["tipo"];
			$app1		= $row["primerapellido"];
			$app2		= $row["segundoapellido"];
			$noms		= $row["nombres"];
			$flTitle	= "";
			//generar aka
			$xSoc		= new cSocio($id);
			$xSoc->init();
			$xTbl		= new cHTabla();
			$xTbl->initRow();
			//score
			$xTbl->addTH($xLng->getT("TR.SDN Numero"));
			switch($tipo){
				case "soundlike":
					$flTitle	= $xLng->getT("TR.Algoritmo") . " : SQL-SOUNDLIKE";
					//$xTbl->addTH("Algoritmo");
					//$xTbl->addTD("SOUNDLIKE");
					$xTbl->addRaw("<td /><td />");
					break;
				case "jarowinkler":
					$flTitle	= $xLng->getT("TR.Algoritmo") . " : JARO-WINKLER";
					$xTbl->addTH($xLng->getT("TR.Coincidencia"));
					
					$score1			= 0;
					$score2			= 0;
					$score3			= 0;
					
					if($nombre != ""){ $score1		= JaroWinkler($nombre, $noms);	}
					if($amaterno != ""){ $score3	= JaroWinkler($amaterno, $app2); }
					if($apaterno != ""){ $score2	= JaroWinkler($apaterno, $app1); }
					//JaroWinkler($string1, $string2)
					$xTbl->addTD("$score1 / $score2 / $score3");
					break;
				case "metaphone":
					$flTitle	= $xLng->getT("TR.Algoritmo") . " : METAPHONE";
					$xTbl->addTH($xLng->getT("TR.Coincidencia"));
					$score1			= "";
					$score2			= "";
					$score3			= "";
						
					if($nombre != ""){ $score1		= metaphone($nombre) . "/" . metaphone($noms);	}
					if($amaterno != ""){ $score3	= metaphone($amaterno) . "/" . metaphone($app2);; }
					if($apaterno != ""){ $score2	= metaphone($apaterno) . "/" . metaphone($app1);; }
					//JaroWinkler($string1, $string2)
					$xTbl->addTD("$score1 & $score2 & $score3");
										
					break;
			}
			//$xTbl->endRow();

			//sdn id
			//
			
			
			$xTbl->endRow();
			
			//
			
			
			//nombres
			$xTbl->initRow();
			$xTbl->addTD($id);
			$xTbl->addTH($xLng->getT("TR.Nombre"));
			$xTbl->addTD($xSoc->getNombreCompleto());
			//alias
			$xTbl->addTH($xLng->getT("TR.Alias"));
			$xTbl->addTD($xSoc->getAlias());
			$xTbl->endRow();
			//direcciones
			//marcadores
			$xTbl->addRaw("<tr><td colspan='3'>" . $xSoc->getObservaciones() . "</td></tr>");
			//$xTbl->initRow();
			$rsdr 		= $mql->getDataRecord("SELECT * FROM `socios_vivienda` WHERE `socio_numero`=$id");
			$adr		= "";
			foreach ($rsdr as $rwr){
				$xDom	= new cPersonasVivienda();
				$xDom->init(false, $rwr);
				$adr	.= $xDom->getFicha();
			}
			
			//$xTbl->addTH();
			//$xTbl->addTD($adr);
			$xTbl->addRaw("<tr><td colspan='3'><h1>"  . $xLng->getT("TR.Direcciones") . "<h1><hr />$adr</td></tr>");			
			//$xTbl->endRow()
			$xFld		= new cHFieldset($flTitle);
			$xFld->addHElem($xTbl->get());
			$txtLst 	.= $xFld->get(  );
			
		}
	}
	if($getPDF == true){
		//base64_encode($sql)
		$xFMT		= new cFormato(8801);
		$xFMT->setProcesarVars(array(
				"variable_listado_de_cedulas" => $txtLst,
				"variable_item_buscado" => "$nombre / $apaterno / $amaterno",
				"variable_cadena_consulta" => ""
		));
		$xRPT		= new cReportes($xHP->getTitle());
		$xRPT->getEncabezado($xHP->getTitle());

		if($ret == true){
			$xRPT->setOut(OUT_HTML);
			$xRPT->setFile("ofacs_list_");
			$xRPT->addContent($xFMT->get());			
		
			$xRPT->render(true);
			$dompdf 		= new DOMPDF();
			$dompdf->load_html($xRPT->render(true));
			$dompdf->set_paper("letter", "portrait" );
			$dompdf->render();
			$json["pdf"] 	= base64_encode($dompdf->output());
		} else {
			$xRPT->setSenders($mails);
			$xRPT->setOut(OUT_PDF);
			$xRPT->setFile("ofacs_list_");
			$xRPT->addContent($xFMT->get());
			$xRPT->render(true);
		}	
	}
} else {
	$json			= $json["error"] = $mql->getMessages(OUT_TXT);
}


echo json_encode($json);


?>