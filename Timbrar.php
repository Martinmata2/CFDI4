<?php 


use CFDI4\Clases\JsonRPCClient;
use CFDI4\Clases\Factura;

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . "/../"));

include_once 'autoload.php';
$MOVIMIENTO = new JsonRPCClient("https://sicei.com.mx/CFDI4/Soap/rest/server.php");
$FACTURA = new Factura();

if(isset($_GET["factura"]))
{
    $factura = $FACTURA->obtener($_GET["factura"]);
    if($factura->FacEstado > 0)
    {
        echo "<h3>La factura con id ".$_GET["factura"]." ya fue timbrado</h3>";
    }
    else 
    {
        try
        {
           $cliente = $factura->receptor;
            unset($factura->receptor);
            unset($cliente->CliID);
            //print_r($cliente);
            unset($factura->FacID);
            foreach ($factura->detalles as $key => $value) 
            {
                unset($factura->detalles[$key]->DdeID);
            }
            
            if(isset($_SESSION["USR_USUARIO_PAC"]) &&  strlen($_SESSION["USR_USUARIO_PAC"]) == 0)
            {
                $_SESSION["USR_USUARIO_PAC"] = "7df4cc81e65d0c691370f104946786a6";
                $_SESSION["USR_CLAVE_PAC"] = "7df4cc81e65d0c691370f104946786a6";
            }
            $resultado = $MOVIMIENTO->__call(
                "subirCarta", 
                array(
                    "nombre" => $_SESSION["USR_USUARIO_PAC"],//TODO obtener de base de datos
                    "clave" => $_SESSION["USR_CLAVE_PAC"] ,//TODO obtener de base de datos
                "factura" => $factura,"cliente" => $cliente));    
            if(is_array($resultado))
            {
                if($resultado["status"] == 200 || isset($resultado["respuesta"]["xml"]))
                {
                    $FACTURA->editardirecto(
                        array("FacXml"=>$resultado["respuesta"]["xml"],
                            "FacFolio"=>$resultado["respuesta"]["folio"],
                            "FacUUID"=>$resultado["respuesta"]["uuid"],
                            "FacEstado"=>1
                        ),
                            $_GET["facura"], "FacID");
                   
                    header("Location:carta_pdf.php?id=".$_GET["carta"]);
                    exit();
                }
                else 
                {
                    print_r($resultado["respuesta"]);
                    print_r($resultado["mensaje"]);
                }
            }
            elseif (isJSON($resultado))
            {
                $resultado = json_decode($resultado, true);
                if($resultado["status"] == 200 || isset($resultado["respuesta"]["xml"]))
                {
                    $FACTURA->editarDirecto(
                        array("FacXml"=>$resultado["respuesta"]["xml"],
                            "FacFolio"=>$resultado["respuesta"]["folio"],
                            "FacUUID"=>$resultado["respuesta"]["uuid"],
                            "FacEstado"=>1
                        ),
                        $_GET["factura"], "FacID");
                         
                    header("Location:carta_pdf.php?id=".$_GET["carta"]);
                    exit();
                }
                else
                {
                    print_r($resultado["respuesta"]);
                    print_r($resultado["mensaje"]);
                }
            }
            else
            {
                print_r($resultado);
            }
        }
        catch (Exception $e)
        {
            echo "Hubo un error: ";
            print_r($e);
        }
    }
}

function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

?>