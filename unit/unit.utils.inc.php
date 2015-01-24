<?php

function unit_RestarFechas($el_restado, $por_restar){
	$fecha1 = strtotime($el_restado);
	$fecha2	= strtotime($por_restar);
	$s = ($fecha1 - $fecha2);
	//$d= intval($s/86400); // Dias
	$d = ($s / 86400); // Dias
	$d = round($d, 0);

	return $d;

}
// -------------------------------------------------------------------------
function unit_SumarDias($fecha, $ndias) {
    return date("Y-m-d", strtotime("$fecha+$ndias day"));
}
// Resta dias a una fecha dada y lo devuelve como una fecha Corta
function unit_RestarDias($mfecha, $ndias) {
    return date("Y-m-d", strtotime("$mfecha-$ndias day"));
}
?>