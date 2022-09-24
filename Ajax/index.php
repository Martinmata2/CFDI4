<?php

use Clases\Login\Acceso;
use Clases\Utilidades\Respuesta;
use Clases\Catalogos\Clientes;
use Clases\Catalogos\Productos;
use Clases\POV\Inventario;
use Clases\Catalogos\Impuestos;
use Clases\Catalogos\Datos\ClientesD;
use Clases\Catalogos\Datos\ProductosD;
use CFDI4\Clases\Factura;


/**
 *
 * Registro, actualizacion y reportes de
 * Productos, Ventas, ABonos, BOnificaciones, Comisiones y Cortes
 * @version v2022_1
 * @author Martin Mata
 */
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . "/../../"));

include_once 'autoload.php';
@session_start();


$LOGIN = new Acceso();
if (! $LOGIN->estaLogueado())
{
    echo Respuesta::resultado(LOGIN_ERROR, 0, "Acceso denegado login");
}
else
{
    switch ($_POST["accion"])
    {
                    
        case "buscarproductoFactura":
            if (isset($_POST["token"]) && $_POST["token"] == $_SESSION["CSRF"])
            {         
                $CLIENTE = new Clientes();
                $PRODUCTO = new Productos();
                $respuesta = $PRODUCTO->obtenerporcodigo($_POST["codigo"]);
                $cliente = $CLIENTE->obtener($_POST["cliente"]);
                if ($respuesta !== 0)
                    echo Respuesta::resultado(PRO_SUCCESS, productoatr($respuesta, $cliente, $_POST["cantidad"]), $PRODUCTO->mensaje);
                else
                    echo Respuesta::resultado(PRO_ERROR, $respuesta, $PRODUCTO->mensaje);
            }
            else
                echo Respuesta::resultado(LOGIN_ERROR, 0, "Acceso denegado");
            break;
         
        case "registrarventa":
            if (isset($_POST["token"]) && $_POST["token"] == $_SESSION["CSRF"])
            {
                $VENTA = new Factura();                
                $venta = $_POST["factura"];                                                                         
                $respuesta = $VENTA->agregar($venta);
                if ($respuesta !== 0)
                {                    
                    echo Respuesta::resultado(FAC_SUCCESS, $respuesta, $VENTA->mensaje);
                   
                }
                else
                    echo Respuesta::resultado(FAC_ERROR, $respuesta, $VENTA->mensaje);                
            }
            else
                echo Respuesta::resultado(LOGIN_ERROR, 0, "Acceso denegado");
            break;
            /*
        case "registrarcompra":
            if (isset($_POST["token"]) && $_POST["token"] == $_SESSION["CSRF"])
            {
                $COMPRA = new Compras();
                $compra = $_POST["compra"];
                $compra["FacFecha"] = date("Y-m-d H:i:s");
                $compra["FacUsuario"] = $_SESSION["USR_ID"];
                $respuesta = $COMPRA->agregar($compra);
                if ($respuesta !== 0)
                {
                    echo Respuesta::resultado(CMP_SUCCESS, $respuesta, $COMPRA->mensaje);
                    //StdOut::imprimir($COMPRA->Imprimir($respuesta));
                }
                else
                    echo Respuesta::resultado(CMP_ERROR, $respuesta, $COMPRA->mensaje);
            }
            else
                echo Respuesta::resultado(CMP_ERROR, 0, "Acceso denegado");
        break;        
        case "editarventa":
            if (isset($_POST["token"]) && $_POST["token"] == $_SESSION["CSRF"])
            {
                $VENTA = new Ventas();
                $venta = $_POST["venta"];
                $cambio = $venta["cambio"];
                unset($venta["cambio"]);
                $respuesta = $VENTA->editar($venta, $venta["FacID"]);
                if ($respuesta !== 0)
                {
                    echo Respuesta::resultado(VTA_SUCCESS, $respuesta, $VENTA->mensaje);
                    StdOut::imprimir($VENTA->Imprimir($respuesta));
                }
                else
                    echo Respuesta::resultado(VTA_ERROR, $respuesta, $VENTA->mensaje);
            }
            else
                echo Respuesta::resultado(VTA_ERROR, 0, "Acceso denegado");
                break;
         */
        default:
            echo Respuesta::resultado(LOGIN_ERROR, 0, "No datos");
            break;
    }
}



/**
 * 
 * @param ProductosD $producto
 * @param ClientesD $cliente
 * @return number
 */
function productoatr($producto, $cliente, $cantidad)
{
    $IMPUESTOS = new Impuestos();
    if (strlen($producto->ProCodigo) > 0)
    {
        if(strlen($producto->ProProSer) <= 0)
            $mensaje["venta_productos"] = "Todos los productos requieren Proser";
        $producto->ProIVA = $IMPUESTOS->obtener($producto->ProIVA)->ImpTasa;
        $producto->ProIEPS = $IMPUESTOS->obtener($producto->ProIEPS)->ImpTasa;
        //$producto->ProISR = $IMPUESTOS->obtener($producto->ProISR)->ImpTasa;
        //$producto->ProRet = $IMPUESTOS->obtener($producto->ProRet)->ImpTasa;
        $html = "<td width='5%' class='center'><span class='noenter ProProSer' contenteditable='true'>".$producto->ProProSer."</span></td>".
            "<td width='5%' class='center'><span class='noenter ProPresentacion' contenteditable='true'>".$producto->ProPresentacion."</span></td>".
            "<td width='15%'><span class='noenter ProDescripcion' contenteditable='true'>".$producto->ProDescripcion."</span></td>".
            "<td width='10%'><input class='lineonly noenter ProCantidad' type='number' value='$cantidad'/></td>".
            "<td width='10%'><input class='lineonly noenter ProPrecio' type='number' value='".$producto->ProPrecio."'/></td>".
            "<td width='10%'><input class='lineonly noenter ProImporte' type='number' value='".$producto->ProPrecio."' readonly /></td>".
            "<td width='5%' align='right'><button class='cut btn-primary' style='margin: 0;padding: 0.5em;'><i class='fa bi-eraser'></i></button></td>".
            "<td style='display:none;'><span style='display:none;' class='ProIVA'>".$producto->ProIVA."</span></td>".
            "<td style='display:none;'><span style='display:none; ' class='ProID'>".$producto->ProID."</span></td>".            
            "<td style='display:none;'><span style='display:none; ' class='ProISR'>0.00</span></td>".
            "<td style='display:none;'><span style='display:none; ' class='ProRet'>0.00</span></td>".
            "<td style='display:none;'><span style='display:none;' class='ProIEPS'>".$producto->ProIEPS."</span></td>";
        $mensaje["status"] = PRO_SUCCESS;
        $mensaje["html"] = $html;
        return $mensaje;               
    }
    else         
    {
        $mensaje["status"] = PRO_ERROR;
        $mensaje["codigo"] = "Producto no encontrado o incompleto";
        return $mensaje;
    }
}

?>