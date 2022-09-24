<?php
namespace CFDI4\Clases;

use Clases\MySql\Query;
use CFDI4\Clases\Funciones\DFacturaF;
use Clases\Utilidades\Validar;
use CFDI4\Clases\Datos\DFacturaD;
use Clases\Catalogos\BasedatosInterface;

//Definiciones para estandarizar valores
define("DDE_ELIMINADO", 1);
define("DDE_ACTIVO", 1);
define("DDE_INACTIVO", 0);
define("DDE_SUCCESS", 200);
define("DDE_ERROR", 400);
define("DDE_DATOS_VALIDOS",200);
define("DDE_DATOS_INVALIDOS",400);
class DFactura extends Query implements BasedatosInterface
{
   
    /**
     *
     * @var DFacturaF
     */
    protected $DFactura;
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
        $this->DFactura = new DFacturaF(null, $base_datos);
        $this->base_datos = $base_datos;
        $this->mensaje = array();
        $this->Tabla = $this->DFactura->table();
        
        parent::__construct($base_datos);
        
        //Inicar tabla
        $this->DFactura->create();
        $this->DFactura->update();
    }

    public function borrar($id,$campo="DdeID",  $usuario=0)
    {
        if($this->DFactura->isAdmin($_SESSION["USR_ROL"]))
            return $this->modificar($this->Tabla, array("deleted"=>DDE_ELIMINADO), $id, $campo, $usuario);
            else return 0;
    }

    public function validar()
    {
        //((validacion de campos))
        return true;       
    }

    public function obtener($id = 0, $campo = "DdeID", $condicion = "0")
    {
        if($id <= 0)
        {
            $resultado = $this->consulta("*", $this->Tabla, "deleted <> ".DDE_ELIMINADO);
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

    public function editar($datos, $id,$campo="DdeID", $condicion="0", $usuario=0)
    {
        if($this->DFactura->isAdmin($_SESSION["USR_ROL"]))
        {
            return $this->modificar($this->Tabla, $datos, "$id", $campo, $usuario);
        }
        else return 0;
    }

    public function agregar($datos)
    {
        if($this->DFactura->isAdmin($_SESSION["USR_ROL"]))
        {
            $this->DFactura->data = new DFacturaD($datos);
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
        $respuesta = $this->consulta("*", $this->Tabla,"DdeID = '$id'");
        if(count($respuesta) > 0)
            return true;
        else return false;
    }
}

