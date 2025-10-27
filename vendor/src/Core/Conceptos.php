<?php

namespace Marve\CFDI\Core;

use stdClass;

class Conceptos extends Ssl
{

    protected $root;
    protected $xml;
    protected $totalIVA;
    protected $totalIVAImp;
    protected $documento;
    protected $descuento;
    protected $subtotal;
    protected $iva16;
    protected $ivaZero;
    protected $totalIvaZero;
    protected $totalISR;
    protected $totalIepsImp;
    protected $totalRet;
    protected $totalRetImp;
    protected $totalIeps;
    protected $ObjetoImp;
    protected $exento;

    public function __construct(string $base_datos, stdClass $documento)
    {
        $this->descuento = 0;
        $this->totalIVA = 0;
        $this->totalIVAImp = 0;
        $this->totalISR = 0;
        $this->totalIepsImp = 0;
        $this->totalRet = 0;
        $this->totalRetImp = 0;
        $this->totalIeps = 0;
        $this->subtotal = 0;
        $this->iva16 = false;
        $this->ivaZero = false;
        $this->totalIvaZero = 0;
        $this->ObjetoImp = "02";
        $this->xml = new \DOMDocument();
        $this->documento = $documento;
        $this->exento = 0;
    }    
    protected function conceptosXml()
    {
      
        $conceptos = $this->xml->createElement("cfdi:Conceptos");
        $conceptos = $this->root->appendChild($conceptos);
        if($this->documento->Detalles !== 0)
        foreach ($this->documento->Detalles as $detalle)
        {
            if(!isset($detalle->DdeDescuento) || $detalle->DdeDescuento == "")
                $detalle->DdeDescuento = 0;
            if($detalle->DdeIva > 0.000000)
            {
                $this->totalIVA += number_format(str_ireplace(',', '',$detalle->DdeIva * $detalle->DdeImporte),2,".","");
                $this->totalIVAImp += $detalle->DdeImporte;
            }
            //if($detalle->DdeIva > 0.00 || $detalle->DdeIeps > 0.000000 ||$detalle->DdeRet > 0.000000 || $detalle->DdeISR > 0.000000)
            //    $ObjetoImp = "02";
            //    else $ObjetoImp = "01";
            $this->ObjetoImp = $detalle->DdeObjetoImpuesto;
            $this->subtotal += number_format(str_ireplace(',', '',$detalle->DdeCantidad * $detalle->DdePrecio),2,".","");
            //descuento
            $this->descuento += $detalle->DdeDescuento;
            $concepto = $this->xml->createElement("cfdi:Concepto");
            $concepto = $conceptos->appendChild($concepto);
                
                $this->cargaAtt($concepto,
                    array(
                        "ObjetoImp"=>$this->ObjetoImp,
                        "ClaveProdServ"=>$detalle->DdeProProSer,
                        "Cantidad"=>number_format(trim($detalle->DdeCantidad,','),6,".",""),
                        "ClaveUnidad"=>$detalle->DdeUnidad,
                        "Descripcion"=>$detalle->DdeDescripcion,
                        "ValorUnitario"=>number_format(str_ireplace(',', '',$detalle->DdePrecio),2,".",""),
                        "Importe"=>number_format(str_ireplace(',', '',$detalle->DdeCantidad * $detalle->DdePrecio),2,".","")                     
                    )
                    );
                if($detalle->DdeDescuento > 0)
                {
                $this->cargaAtt($concepto,
                    array(
                        "Descuento"=>number_format(str_ireplace(',', '',$detalle->DdeDescuento),2,".","")
                    )
                    );
                }
                
                //Impuestos
                if($this->documento->FacTipoComprobante != "T")                                   
                {
                    //se le agrego >= para incluir iva 0
                    if($detalle->DdeIva >= 0.000000 || $detalle->DdeIeps > 0.000000 ||$detalle->DdeRet > 0.000000 || $detalle->DdeISR > 0.000000)
                    {
                        $impuestos = $this->xml->createElement("cfdi:Impuestos");
                        $impuestos = $concepto->appendChild($impuestos);
                    }
                    //se le agrego >=  para incluir iva 0
                    if($detalle->DdeIva >= 0.000000 || $detalle->DdeIeps > 0.000000)
                    {
                        $traslados = $this->xml->createElement("cfdi:Traslados");
                        $traslados = $impuestos->appendChild($traslados);
                    }                
                    if($detalle->DdeIva == 0.00)
                    {
                         $this->ivaZero = true;                         
                                                
                         $traslado = $this->xml->createElement("cfdi:Traslado");
                         $traslado = $traslados->appendChild($traslado);
                         if(isset($detalle->exento) && $detalle->exento == 1)
                         {
                             $this->cargaAtt($traslado,
                                 array(
                                     "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                     "Impuesto"=>"002",
                                     "TipoFactor"=>"Exento"
                                 )
                                 );
                             $this->exento += $detalle->DdeImporte;
                         }
                         else 
                         {
                             $this->totalIvaZero += $detalle->DdeImporte;
                             $this->cargaAtt($traslado,
                             array(
                                    "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                    "Impuesto"=>"002",
                                    "TipoFactor"=>"Tasa",
                                    "TasaOCuota"=>"0.000000",
                                    "Importe"=>"0.00" 
                                    )
                             );
                         }
                         
                    }
                    elseif($detalle->DdeIva > 0.000000)
                    {
                        $this->iva16 = true;
                        $traslado = $this->xml->createElement("cfdi:Traslado");
                        $traslado = $traslados->appendChild($traslado);
                        $this->cargaAtt($traslado,
                            array(
                                "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                "Impuesto"=>"002",
                                "TipoFactor"=>"Tasa",
                                "TasaOCuota"=>"0.160000",
                                "Importe"=>number_format(str_ireplace(',', '',$detalle->DdeIva * $detalle->DdeImporte),2,".","")
                            )
                            );
                        
                    }
                    if($detalle->DdeIeps > 0.000000)
                    {
                        $this->totalIeps += $detalle->DdeImporte;
                        $this->totalIepsImp += $detalle->DdeImporte;
                        $traslado = $this->xml->createElement("cfdi:Traslado");
                        $traslado = $traslados->appendChild($traslado);
                        $this->cargaAtt($traslado,
                            array(
                                "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                "Impuesto"=>"003",
                                "TipoFactor"=>"Tasa",
                                "TasaOCuota"=>number_format(str_ireplace(',', '',$detalle->DdeIeps),6,".",""),
                                "Importe"=>number_format(str_ireplace(',', '',$detalle->DdeIeps * $detalle->DdeImporte),2,".","")
                            )
                            );
                    }
                    if($detalle->DdeRet > 0.000000 || $detalle->DdeISR > 0.000000)
                    {
                        $retenciones = $this->xml->createElement("cfdi:Retenciones");
                        $retenciones = $impuestos->appendChild($retenciones);
                    }
                    if($detalle->DdeRet > 0.000000)
                    {
                        $this->totalRet += number_format(str_ireplace(',', '',$detalle->DdeRet * $detalle->DdeImporte),2,".","");
                        $this->totalRetImp += $detalle->DdeImporte;
                        $retencion = $this->xml->createElement("cfdi:Retencion");
                        $retencion = $retenciones->appendChild($retencion);
                        $this->cargaAtt($retencion,
                            array(
                                "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                "Impuesto"=>"002",
                                "TipoFactor"=>"Tasa",
                                "TasaOCuota"=>number_format(str_ireplace(',', '',$detalle->DdeRet),6,".",""),
                                "Importe"=>number_format(str_ireplace(',', '',$detalle->DdeRet * $detalle->DdeImporte),2,".","")
                            )
                            );
                    }
                    if($detalle->DdeISR > 0.000000)
                    {
                        $this->totalISR += number_format(str_ireplace(',', '',$detalle->DdeISR * $detalle->DdeImporte),2,".","");
                        $retencion = $this->xml->createElement("cfdi:Retencion");
                        $retencion = $retenciones->appendChild($retencion);
                        $this->cargaAtt($retencion,
                            array(
                                "Base"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".",""),
                                "Impuesto"=>"001",
                                "TipoFactor"=>"Tasa",
                                "TasaOCuota"=>number_format(str_ireplace(',', '',$detalle->DdeISR),6,".",""),
                                "Importe"=>number_format(str_ireplace(',', '',$detalle->DdeISR * $detalle->DdeImporte),2,".","")
                            )
                            );
                    }
                    if(strlen($detalle->DdePredial)>3)
                    {
                        $predial = $this->xml->createElement("cfdi:CuentaPredial");
                        $predial = $concepto->appendChild($predial);
                        $this->cargaAtt($predial,
                            array(
                                "Numero"=>$detalle->DdePredial
                            )
                            );
                    }
                }
                /*
                 if($detalle->aduana_numero > 0)
                 {
                 $aduana = $this->xml->createElement("cfdi:InformacionAduanera");
                 $aduana = $concepto->appendChild($aduana);
                 $this->cargaAtt($aduana,
                 array(
                 "fecha"=>$detalle->DdeAduanaFecha,
                 "numero"=>$detalle->DdeAduanaNumero
                 )
                 );
                 }*/
        }
    }
    
}