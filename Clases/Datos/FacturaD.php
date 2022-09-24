<?php
namespace CFDI4\Clases\Datos;

class FacturaD
{

    /**
     *
     * @var int
     *
     */
    public $FacID;

    /**
     *
     * @var string
     *
     */
    public $FacTipoComprobante;

    /**
     *
     * @var int
     *
     */
    public $FacDatos;

    /**
     *
     * @var string
     *
     */
    public $FacSerie;

    /**
     *
     * @var string
     *
     */
    public $FacFolio;

    /**
     *
     * @var int
     *
     */
    public $FacReceptor;

    /**
     *
     * @var string
     *
     */
    public $FacFecha;

    /**
     *
     * @var string
     *
     */
    public $FacFormaPago;

    /**
     *
     * @var string
     *
     */
    public $FacMetodoPago;

    /**
     *
     * @var string
     *
     */
    public $FacSubtotal;

    /**
     *
     * @var string
     *
     */
    public $FacIva;

    /**
     *
     * @var string
     *
     */
    public $FacDescuento;

    /**
     *
     * @var string
     *
     */
    public $FacMotivoDescuento;

    /**
     *
     * @var string
     *
     */
    public $FacTotal;

    /**
     *
     * @var string
     *
     */
    public $FacEstado;

    /**
     *
     * @var string
     *
     */
    public $FacCancelada;

    /**
     *
     * @var string
     *
     */
    public $FacSelloCFD;

    /**
     *
     * @var string
     *
     */
    public $FacFechaTimbrado;

    /**
     *
     * @var string
     *
     */
    public $FacUUID;

    /**
     *
     * @var string
     *
     */
    public $FacNoCertificadoSat;

    /**
     *
     * @var string
     *
     */
    public $FacVersion;

    /**
     *
     * @var string
     *
     */
    public $FacSelloSAT;

    /**
     *
     * @var string
     *
     */
    public $FacCadenaOriginal;

    /**
     *
     * @var string
     *
     */
    public $FacXml;

    /**
     *
     * @var string
     *
     */
    public $FacCbb;

    /**
     *
     * @var string
     *
     */
    public $FacRet;

    /**
     *
     * @var string
     *
     */
    public $FacIsr;

    /**
     *
     * @var string
     *
     */
    public $FacNota;

    /**
     *
     * @var string
     *
     */
    public $FacCuenta;

    /**
     *
     * @var string
     *
     */
    public $FacSucursal;

    /**
     *
     * @var string
     *
     */
    public $FacMoneda;

    /**
     *
     * @var string
     *
     */
    public $FacParidad;

    /**
     *
     * @var string
     *
     */
    public $FacIEPS;

    /**
     *
     * @var string
     *
     */
    public $FacUsoCFDI;

    /**
     *
     * @var string
     *
     */
    public $FacMeses;

    /**
     *
     * @var string
     *
     */
    public $FacYear;

    /**
     *
     * @var string
     *
     */
    public $FacPeriodicidad;

    /**
     *
     * @var string
     *
     */
    public $RelUUID;

    /**
     *
     * @var string
     *
     */
    public $RelTipo;

    /**
     *
     * @var string
     *
     */
    public $FacLocal;

    /**
     *
     * @var int
     *
     */
    public $FacLocalID;

    public function __construct($datos = null)
    {
        try
        {
            if ($datos == null)
            {
                $this->FacTipoComprobante = "";
                $this->FacDatos = 0;
                $this->FacSerie = "";
                $this->FacFolio = "";
                $this->FacReceptor = 0;
                $this->FacFecha = "";
                $this->FacFormaPago = "";
                $this->FacMetodoPago = "";
                $this->FacSubtotal = "";
                $this->FacIva = "";
                $this->FacDescuento = "";
                $this->FacMotivoDescuento = "";
                $this->FacTotal = "";
                $this->FacEstado = "";
                $this->FacCancelada = "";
                $this->FacSelloCFD = "";
                $this->FacFechaTimbrado = "";
                $this->FacUUID = "";
                $this->FacNoCertificadoSat = "";
                $this->FacVersion = "";
                $this->FacSelloSAT = "";
                $this->FacCadenaOriginal = "";
                $this->FacXml = "";
                $this->FacCbb = "";
                $this->FacRet = "";
                $this->FacIsr = "";
                $this->FacNota = "";
                $this->FacCuenta = "";
                $this->FacSucursal = "";
                $this->FacMoneda = "";
                $this->FacParidad = "";
                $this->FacIEPS = "";
                $this->FacUsoCFDI = "";
                $this->FacMeses = "";
                $this->FacYear = "";
                $this->FacPeriodicidad = "";
                $this->RelUUID = "";
                $this->RelTipo = "";
                $this->FacLocal = "";
                $this->FacLocalID = 0;
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