<?php
namespace CFDI4\Clases\Datos;

class DFacturaD
{

    /**
     *
     * @var int
     *
     */
    public $DdeID;

    /**
     *
     * @var int
     *
     */
    public $DdeDocumento;

    /**
     *
     * @var string
     *
     */
    public $DdeUnidad;

    /**
     *
     * @var int
     *
     */
    public $DdeProducto;

    /**
     *
     * @var string
     *
     */
    public $DdeIva;

    /**
     *
     * @var string
     *
     */
    public $DdeIeps;

    /**
     *
     * @var string
     *
     */
    public $DdeCantidad;

    /**
     *
     * @var string
     *
     */
    public $DdeDescripcion;

    /**
     *
     * @var string
     *
     */
    public $DdePrecio;

    /**
     *
     * @var string
     *
     */
    public $DdeImporte;

    /**
     *
     * @var string
     *
     */
    public $DdeAduanaFecha;

    /**
     *
     * @var int
     *
     */
    public $DdeAduanaNumero;

    /**
     *
     * @var string
     *
     */
    public $DdeProProSer;

    /**
     *
     * @var string
     *
     */
    public $DdePredial;

    /**
     *
     * @var string
     *
     */
    public $DdeISR;

    /**
     *
     * @var string
     *
     */
    public $DdeRet;

    /**
     *
     * @var string
     *
     */
    public $DdeDescuento;

    public function __construct($datos = null)
    {
        try
        {
            if ($datos == null)
            {                
                $this->DdeDocumento = 0;
                $this->DdeUnidad = "";
                $this->DdeProducto = 0;
                $this->DdeIva = "";
                $this->DdeIeps = "";
                $this->DdeCantidad = "";
                $this->DdeDescripcion = "";
                $this->DdePrecio = "";
                $this->DdeImporte = "";
                $this->DdeAduanaFecha = "";
                $this->DdeAduanaNumero = 0;
                $this->DdeProProSer = "";
                $this->DdePredial = "";
                $this->DdeISR = "";
                $this->DdeRet = "";
                $this->DdeDescuento = "";
            }
            else
            {
                foreach ($datos as $k => $v)
                {
                    $this->$k = $v;
                }
            }
        }
        catch (\Exception $e)
        {}
    }
}