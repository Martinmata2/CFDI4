<?php
namespace CFDI4\Clases;

use Clases\MySql\Query;
use CFDI4\Clases\Funciones\FacturaF;
use Clases\Utilidades\Validar;
use CFDI4\Clases\Datos\FacturaD;
use Clases\Catalogos\BasedatosInterface;
use Clases\Catalogos\Clientes;

//Definiciones para estandarizar valores
define("FAC_ELIMINADO", 1);
define("FAC_ACTIVO", 1);
define("FAC_INACTIVO", 0);
define("FAC_SUCCESS", 200);
define("FAC_ERROR", 400);
define("FAC_DATOS_VALIDOS",200);
define("FAC_DATOS_INVALIDOS",400);
class Factura extends Query implements BasedatosInterface
{
   
    /**
     *
     * @var FacturaF
     */
    protected $Factura;
    /**
     *
     * @var string
     */
    protected $Tabla;
    /**
     *
     * @var string
     */
    public $mensaje;
    public function __construct( $base_datos = BD_GENERAL)
    {
        $this->Factura = new FacturaF(null, $base_datos);
        $this->base_datos = $base_datos;
        $this->mensaje = array();
        $this->Tabla = $this->Factura->table();
        
        parent::__construct($base_datos);
        
        //Inicar tabla
        $this->Factura->create();
        $this->Factura->update();
    }

    public function borrar($id,$campo="FacID",  $usuario=0)
    {
        if($this->Factura->isAdmin($_SESSION["USR_ROL"]))
            return $this->modificar($this->Tabla, array("deleted"=>FAC_ELIMINADO), $id, $campo, $usuario);
            else return 0;
    }

    public function validar()
    {
        $CLIENTE = new Clientes($this->conn, $this->base_datos);
        //((validacion de campos))
        $this->mensaje = array();
        //FacMetodoPago
        $validacion = ($this->Factura->data->FacMetodoPago == "PUE" && $this->Factura->data->FacFormaPago == "99");
        if($validacion == true)
        {
            $this->mensaje["FacFormaPago"] = "Pago en una sola exibicion no admite forma de pago 99";
        }
        //FacMetodoPago
        $validacion = ($this->Factura->data->FacMetodoPago == "PPD" && $this->Factura->data->FacFormaPago != "99");
        if($validacion == true)
        {
            $this->mensaje["FacFormaPago"] = "Pago en parcialidades solo admite forma de pago 99";
        }
        //FacCuenta
        $validacion = strlen($this->Factura->data->FacCuenta) > 0 && strlen($this->Factura->data->FacCuenta) < 10;
        if($validacion == true)
        {
            $this->mensaje["FacCuenta"] = "La cuenta debe tener al menos 10 digitos o ser omitida";
        }
        //FacReceptor
        $validacion = $CLIENTE->obtener($this->Factura->data->FacReceptor);
        if( strlen($validacion->CliRegimen) <= 1)
        {
            $this->mensaje["FacReceptor"] = "El Cliente no tiene regimen asignado";
        }
        if(strtoupper($validacion->CliRazon) == "PUBLICO EN GENERAL" || $validacion->CliRfc == "XAXX010101000" || $validacion->CliRfc == "xaxx010101000")
        {
            if($this->Factura->data->FacPeriodicidad == "0")
                $this->mensaje["FacPeriodicidad"] = "Seleccionae periodicidad de factura global";
                if($this->Factura->data->FacUsoCFDI != "S01")
                    $this->mensaje["FacUsoCFDI"] = "Selecciones Uso CFDI sin efectos fiscales S01";
        }
        if(count($this->mensaje) > 0)
        {
            $this->mensaje["status"] = FAC_DATOS_INVALIDOS;
            return false;
        }
        else
        {
            $this->mensaje["status"] = VTA_DATOS_VALIDOS;
            return true;
        }
    }

    public function obtener($id = 0, $campo = "FacID", $condicion = "0")
    {
        if($id <= 0)
        {
            $resultado = $this->consulta("*", $this->Tabla, "deleted <> ".FAC_ELIMINADO);
            if (\count($resultado) > 0)
                return $resultado;
                else return 0;
        }
        else
        {
            $resultado = $this->consulta("*", $this->Tabla, "$campo = '$id'", $condicion);
            if (\count($resultado) > 0)
                return $resultado[0];
                else
                    return 0;
        }
        return 0;
    }

    public function editar($datos, $id,$campo="FacID", $condicion="0", $usuario=0)
    {
        if($this->Factura->isUsuario($_SESSION["USR_ROL"]))
        {
            return $this->modificar($this->Tabla, $datos, "$id", $campo, $usuario);
        }
        else return 0;
    }

    public function agregar($datos)
    {
        if($this->Factura->isUsuario($_SESSION["USR_ROL"]))
        {
            $this->Factura->data = new FacturaD($datos);
            if($this->validar() === true)
            {               
                return $this->insertar($this->Tabla, $datos);
            }
            else return 0;
        }
        else return 0;
    }      
   
    public function Unico($campo, $valor)
    {
        $respuesta = $this->consulta("*", $this->Tabla,"$campo = '$valor'");
        if(count($respuesta) > 0)
            return false;
        else return true;
    }
    
    
    public function existe($id)
    {
        $respuesta = $this->consulta("*", $this->Tabla,"FacID = '$id'");
        if(count($respuesta) > 0)
            return true;
        else return false;
    }
}

