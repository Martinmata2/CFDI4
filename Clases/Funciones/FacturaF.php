<?php
namespace CFDI4\Clases\Funciones;

use Clases\FuncionInterface;
use Clases\PermisosBD;
use CFDI4\Clases\Datos\FacturaD;
use CFDI4\Clases\DFactura;

class FacturaF extends PermisosBD implements FuncionInterface
{

    /**
     *
     * @var FacturaD
     */
    public $data;

    /**
     * 
     * @var DFactura
     */
    public $detalles;
    

    public function __construct($datos = null, $base_datos = BD_GENERAL)
    {
        $this->base_datos = $base_datos;
        parent::__construct($base_datos);        
        $this->data = new FacturaD($datos);
        $this->detalles = new DFactura();
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
        return "documentos";
    }

    /**
     * Codigo sql para Tabla
     *
     * @return string
     */
    private function sql()
    {
        //sql code para generar la tabla
        $sql = "CREATE TABLE IF NOT EXISTS documentos (
              FacID int(11) NOT NULL AUTO_INCREMENT,
              FacTipoComprobante tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacDatos tinyint(4) NOT NULL,
              FacSerie varchar(20) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacFolio mediumint(9) unsigned NOT NULL,
              FacReceptor int(11) NOT NULL,
              FacFecha datetime NOT NULL,
              FacFormaPago tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacMetodoPago tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
              FacSubtotal decimal(10,2) NOT NULL,
              FacIva decimal(10,2) NOT NULL,
              FacDescuento decimal(10,2) DEFAULT NULL,
              FacMotivoDescuento tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
              FacTotal decimal(10,2) NOT NULL,
              FacEstado tinyint(1) NOT NULL,
              FacCancelada tinyint(1) NOT NULL,
              FacSelloCFD text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacFechaTimbrado tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacUUID tinytext CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacNoCertificadoSat varchar(25) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacVersion varchar(5) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacSelloSAT text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacCadenaOriginal text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacXml text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacCbb text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacRet decimal(10,2) NOT NULL DEFAULT '0.00',
              FacIsr decimal(10,2) NOT NULL DEFAULT '0.00',
              FacNota text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacCuenta text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacSucursal varchar(200) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              FacMoneda varchar(20) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL DEFAULT 'MXN',
              FacParidad decimal(10,2) NOT NULL DEFAULT '1.00',
              FacIEPS tinytext NOT NULL,
              FacUsoCFDI VARCHAR(7) NOT NULL,
              FacLocal decimal (10,2) NOT NULL DEFAULT '0.00',
              FacLocalID int(11) NOT NULL,
              FacPeriodicidad varchar(10) CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
              FacMeses varchar(3) CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
              FacYear varchar(5) CHARACTER SET utf8 COLLATE utf8_spanish2_ci,
              RelUUID text CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              RelTipo varchar(4) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
              PRIMARY KEY (FacID)
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
        $update = "
        IF NOT EXISTS( SELECT NULL FROM INFORMATION_SCHEMA.COLUMNS
           WHERE table_name = 'clientes'
             AND table_schema = '".$this->base_datos."'
             AND column_name = 'CliRegimen')  THEN
         ALTER TABLE `clientes` ADD `CliRegimen` varchar(20) COLLATE utf8_spanish2_ci;
        END IF;
        ";
        return $update;  
    }
}

