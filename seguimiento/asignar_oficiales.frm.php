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
$xHP		= new cHPage("TR.Asignar oficiales_de_credito");
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$jxc 		= new TinyAjax();
$jsCampo	=  (CREDITO_USAR_OFICIAL_SEGUIMIENTO == true) ? "oficial_seguimiento" : "oficial_credito";

function jsaGetCreditos($convenio, $estatus, $periocidad, $oficial){
	$xLi		= new cSQLListas();
	$ByOficial		= (CREDITO_USAR_OFICIAL_SEGUIMIENTO == true) ? "	AND	(`creditos_solicitud`.`oficial_seguimiento` != $oficial) " : "	AND	(`creditos_solicitud`.`oficial_credito` != $oficial) ";

	//saveError( 22, $_SESSION["SN_b80bb7740288fda1f201890375a60c8f"], "$oficial Ingreso al Modulo de Asignacion de Oficiales");
	$sqlCred		= (CREDITO_USAR_OFICIAL_SEGUIMIENTO == true) ? $xLi->getListadoDeCreditosConOficialSeguimiento(false, $estatus, $periocidad, $convenio, $ByOficial) : $xLi->getListadoDeCreditosConOficial(false, $estatus, $periocidad, $convenio, $ByOficial);

	$xTbl = new cTabla($sqlCred, 2);
	$xChk			= new cHCheckBox();
	$xTbl->setTdClassByType();
	$xTbl->addEspTool($xChk->get("", "chk" . STD_LITERAL_DIVISOR . "_REPLACE_ID_") );
	$xTbl->setWidth();
	return $xTbl->Show();
	//return $sqlCred;

}
$jxc ->exportFunction('jsaGetCreditos', array('idproducto', 'idestado', 'idperiocidad', 'idoficial'), "#id-listado-de-creditos");
$jxc ->process();

$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);


$xHP->init();

$xFRM		= new cHForm("frmAsignarOficiales", "./");
$xSel		= new cHSelect();
$msg		= "";

$xFRM->addHElem( $xSel->getListaDeProductosDeCredito()->get(true) );
$xFRM->addHElem( $xSel->getListaDeEstadosDeCredito()->get(true) );
$xFRM->addHElem( $xSel->getListaDeOficiales()->get(true) );
$xFRM->addHElem( $xSel->getListaDePeriocidadDePago()->get(true));
$xFRM->addHTML("<div id='id-listado-de-creditos'></div>");
$xFRM->OButton("TR.Obtener", "jsaGetCreditos()", $xFRM->ic()->EJECUTAR);
$xFRM->OButton("TR.Guardar", "jsSetOficial()", $xFRM->ic()->GUARDAR);
//$xFRM->addJsBasico();
//$xFRM->addCreditBasico();
//$xFRM->addSubmit();
echo $xFRM->get();
$jxc ->drawJavaScript(false, true);
?>
<script>
var Frm 					= document.frmAsignarOficiales;
var divLiteral				= STD_LITERAL_DIVISOR;
var xGen					= new Gen();
var fld						= "<?php echo $jsCampo; ?>";
function jsSetOficial(){
	var vOficial		= $("#idoficial").val();
	$('.coolCheck input:checked').each(function() {
	    var mID			= $(this).attr('id');
		var aID			= mID.split(divLiteral);
		var cred		= entero(aID[1]);
		xGen.save({tabla: "creditos_solicitud", id : cred, content : fld + "=" +  vOficial});		    
	});		
  	//document.getElementById("PMsg").innerHTML = "";
}
function jsEchoMsg(msg){ xGen.alerta({msg:msg}); }
function jsMarkAll(){
	var isLims 			= Frm.elements.length - 1;
	var vOficial		= Frm.cOficial.value;
	for(i=0; i<=isLims; i++){
		var mTyp 	= Frm.elements[i].getAttribute("type");
		var mID 	= Frm.elements[i].getAttribute("id");
		//Verificar si es mayor a cero o no nulo
		if ( (mID!=null) && (mID.indexOf("chk@")!= -1) && (mTyp == "checkbox") ) {
			if ( document.getElementById(mID).checked) {
				document.getElementById(mID).checked = false;
			} else {
				document.getElementById(mID).checked = true;
			}
		}
	}
}
</script>
<?php
$xHP->fin();
?>