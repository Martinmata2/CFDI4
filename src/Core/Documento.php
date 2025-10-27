<?php
namespace Marve\CFDI4\Core;

use stdClass;

class Documento extends Impuestos
{
    public $error = "Factura Mal Formada O RFC Incorrecto";
    public $cadena;
        
    //protected $root;
    //protected $xml;
    //protected $documento;
    protected $owner;
    protected $receptor;
    protected $base_datos;
    //protected $descuento;
    //protected $totalIVA;
    //protected $totalIVAImp;
    //protected $totalISR;
    //protected $totalIepsImp;
    //protected $totalRet;
    //protected $totalRetImp;
    //protected $totalIeps;
    //protected $subtotal;
    //protected $iva16;
    //protected $ivaZero;
    //protected $totalIvaZero;
    protected $cadena_original;

    protected $folder;
    protected $sello;
    //protected $total;

    
    public function __construct(string $base_datos, stdClass $documento, stdClass $propio, stdClass $cliente, string $folder)
    {        
        parent::__construct($base_datos, $documento);
        $this->base_datos = $base_datos;
        $this->folder = $folder;    
        //$PROPIO = new Propio($this->base_datos);
        //$CLIENTE = new Clientes($this->base_datos);
        $this->owner = $propio; // $PROPIO->obtener(1, "ProID");
        $this->receptor =  $cliente;//	$CLIENTE->obtener($documento->FacReceptor, "CliID");
        
        $this->cadena_original = "";
        //$this->total = 0;
    }
    
    
    protected function relacionados()
    {
        $relacionado = $this->xml->createElement("cfdi:CfdiRelacionados");
        $relacionado = $this->root->appendChild($relacionado);
        $this->cargaAtt($relacionado,
            array(
                "TipoRelacion"=>$this-> documento->RelTipo
            )
            );
        /* agregamos la posibilidad de relacionar varias facturas */
        $cfdirelacionados = explode("|", $this->documento->RelUUID);
        foreach ($cfdirelacionados as $value)
        {
            $relacion = $this->xml->createElement("cfdi:CfdiRelacionado");
            $relacion = $relacionado->appendChild($relacion);
            $this->cargaAtt($relacion,
                array(
                    "UUID"=>$value
                )
                );
        }
    }

    protected function emisorXml()
    {
        $emisor = $this->xml->createElement("cfdi:Emisor");
        $emisor = $this->root->appendChild($emisor);
        $this->cargaAtt($emisor,
            array(
                "Rfc"=>$this->owner->ProRfc,
                "Nombre"=> utf8_decode($this->owner->ProRazon),
                "RegimenFiscal"=>$this->owner->ProRegimen)
            );            
    }
    
    protected function receptorXml()
    {
        $receptor = $this->xml->createElement("cfdi:Receptor");
        $receptor = $this->root->appendChild($receptor);
        if($this->documento->FacTipoComprobante == "T")
        {
            $this->cargaAtt($receptor,
                array(
                    "Rfc"=>$this->owner->ProRfc,
                    "Nombre"=>$this->utf8decode($this->owner->ProRazon),
                    "DomicilioFiscalReceptor"=>$this->owner->ProCp,
                    "RegimenFiscalReceptor"=>$this->owner->ProRegimen,
                    "UsoCFDI"=>$this->documento->FacUsoCFDI
                )
                );
        }
        else
        {
            $this->cargaAtt($receptor,
                array(
                    "Rfc"=>$this->receptor->CliRfc,
                    "Nombre"=>utf8_decode($this->receptor->CliRazon),
                    "ResidenciaFiscal"=>($this->receptor->CliPais == "MEX")?"":$this->receptor->CliPais,
                    "DomicilioFiscalReceptor"=>$this->receptor->CliCp,
                    "RegimenFiscalReceptor"=>$this->receptor->CliRegimen,
                    "UsoCFDI"=>$this->documento->FacUsoCFDI
                )
                );
        }
    }
    
    protected function globalXml()
    {
        $anio = $this->utf8decode("Año");
        $global = $this->xml->createElement("cfdi:InformacionGlobal");
        $global = $this->root->appendChild($global);
        $this->cargaAtt($global,
            array(
                "Periodicidad"=>$this->documento->FacPeriodicidad,
                "Meses"=>$this->documento->FacMeses,
                "$anio"=>$this->documento->FacYear
            )
            );
    }
    
    
    
    /**
     * Sella el documento
     */
    protected function sella()
    {
        //TODO cambiar a folder del cliente
        $certificado = $this->owner->ProNoCertificado;
        $this->sello = $this->sellar($this->owner, $this->folder, $this->cadena_original);
        $this->root->setAttribute("Sello",$this->sello);
        $certificado = $this->Certificar($this->owner, $this->folder);
        $this->root->setAttribute("Certificado",$certificado);
    }
    
    
}