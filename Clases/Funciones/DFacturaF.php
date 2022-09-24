<?php
namespace CFDI4\Clases\Funciones;

use Clases\FuncionInterface;
use Clases\PermisosBD;
use CFDI4\Clases\Datos\DFacturaD;

class DFacturaF extends PermisosBD implements FuncionInterface
{

    /**
     *
     * @var DFacturaD
     */
    public $data;

    

    public function __construct($datos = null, $base_datos = BD_GENERAL)
    {
        $this->base_datos = $base_datos;
        parent::__construct($base_datos);        
        $this->data = new DFacturaD($datos);
    }

    public function truncate()
    {
        if ($this->canTruncate($_SESSION["USR_ROL"])) return $this->conexion->query("TRUNCATE TABLE " . $this->table());
    }

    public function create()
    {
        if ($resultado = $this->conexion->query("SHOW TABLES LIKE '" . $this->table() . "'"))
        {
            if ($resultado->num_rows == 0) $this->conexion->multi_query($this->sql());
        }
    }

    public function update()
    {
        if ($resultado = $this->conexion->query("SHOW TABLES LIKE '" . $this->table() . "'"))
        {
            if ($resultado->num_rows > 0)
            {
                if (strlen(trim($this->pendingupdates())) > 10) $this->conexion->multi_query($this->pendingupdates());
            }
        }
    }

    public function delete()
    {
        if ($this->canDrop($_SESSION["USR_ROL"])) return $this->conexion->query("DROP TABLE IF EXISTS " . $this->table());
    }

    public function getData()
    {
        return $this->data;
    }

    public function table()
    {
        return "documentosdetalles";
    }

    /**
     * Codigo sql para Tabla
     *
     * @return string
     */
    private function sql()
    {
        //sql code para generar la tabla
        $sql = "CREATE TABLE IF NOT EXISTS documentosdetalles (
              DdeID int(11) NOT NULL AUTO_INCREMENT,
              DdeDocumento int(11) NOT NULL,
              DdeUnidad varchar(20) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              DdeProducto int(11) NOT NULL,
              DdeIva decimal(16,6) NOT NULL,
              DdeIeps decimal(16,6) NOT NULL,
              DdeCantidad decimal(16,6) NOT NULL,
              DdeDescripcion tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              DdePrecio decimal(16,2) NOT NULL,
              DdeImporte decimal(16,2) NOT NULL,
              DdeAduanaFecha date NOT NULL,
              DdeAduanaNumero int(11) NOT NULL,
              DdeProProSer VARCHAR(10) NOT NULL,
              DdePredial VARCHAR(12) NOT NULL,
              DdeISR DECIMAL(16,6) NOT NULL,
              DdeRet DECIMAL(16,6) NOT NULL,
              DdeNoIdentificacion varchar(40) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              DdeArancelaria varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              DdeISH decimal(10,2) NOT NULL,
              ImpLocal tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              deleted int(1) not null,
              PRIMARY KEY (DdeID)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
        return $sql;
    }

    /**
     * Codigo sql para actualizaciones pendientes.
     *
     * @return string
     */
    private function pendingupdates()
    {
        //sql code para actualizar tabla
        $update = "";                
        return $update;
    }
}

