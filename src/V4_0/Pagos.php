<?php
namespace Marve\CFDI4\V4_0;

use Marve\CFDI4\Core\Documento;
use stdClass;

class Pagos extends Documento
{

    protected $totalBaseIVA;
    protected $totalBaseISR;
    protected $totalBaseRet;
    protected $totalIVATasa;
    protected $totalISRTasa;
    protected $totalRetTasa;
    protected $folder;
    protected $docrelacionados;
    public function __construct(string $base_datos, stdClass $documento, array $docrelacionados, $propio, $cliente, $folder)
    {
        parent::__construct($base_datos, $documento, $propio, $cliente, $folder);
        $this->docrelacionados = $docrelacionados;
    }
    
    function formarXml()
    {        
        //Genera xml
        $this->encabezado();
        //Agrega relacionado
        if($this->documento->RelTipo != "0" && $this->documento->RelTipo != "" )
        {		    
	        $this->relacionados();		    
		}
        if(strlen($this->documento->FacPeriodicidad) > 1)
            $this->globalXml();
        //Datos del emisor
        $this->emisorXml();
        //Datos de receptor
        $this->receptorXml();
        //Productos Articulos
        $this->conceptosXml();
        //Impuestos 
        //$this->impuestosXml();
        //Agregar complementos
        $this->complementoXML();
            //Genera cadena original
        $this->genera_cadena_original($this->xml, $this->folder,$this->cadena_original );
        //Sella xml
        $this->sella();
        //valida
        $done = $this->valida($this->xml, $this->folder);
        $this->xml->formatOutput = true;
        return $this->xml->saveXML ();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Cabeca\CFDI\Core\Documento::conceptosXml()
     */
    protected function conceptosXml()
    {
        $conceptos = $this->xml->createElement("cfdi:Conceptos");
        $conceptos = $this->root->appendChild($conceptos);
        $concepto = $this->xml->createElement("cfdi:Concepto");
        $concepto = $conceptos->appendChild($concepto);
        $this->cargaAtt($concepto,
            array(
                "ObjetoImp"=>"01",
                "ClaveProdServ"=>"84111506",
                "Cantidad"=>1,
                "ClaveUnidad"=>"ACT",
                "Descripcion"=>"Pago",
                "ValorUnitario"=>0,
                "Importe"=>0
            )
            );
    }
    

    protected function encabezado()
    {
        $this->root = $this->xml->createElement("cfdi:Comprobante");
        $this->root = $this->xml->appendChild($this->root);
        
        $this->cargaAtt($this->root,	array(
            "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd",            //
            "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
		    "xmlns:pago20"=>"http://www.sat.gob.mx/Pagos20", 
			"xmlns:cfdi"=>"http://www.sat.gob.mx/cfd/4"
				
           
        ));
        $this->cargaAtt($this->root, array(
            "Version"=>"4.0",
            "Fecha"=>$this->fechaFiscal(date("Y-m-d H:i:s", strtotime("-1 hours"))),
            "Sello"=>"@",
            "Serie"=>$this->documento->FacSerie,
            "Folio"=>$this->documento->FacFolio,            
            "NoCertificado"=>$this->owner->ProNoCertificado,
            "Certificado"=>"@",            
            "SubTotal"=>0.00,            
            "Moneda"=>"XXX",            
            "Total"=> 0.00,
            "TipoDeComprobante"=>$this->documento->FacTipoComprobante,
            "Exportacion"=>"01",            
            "LugarExpedicion"=>$this->owner->ProCp)
            );
        
        
    }
    /**
     * Lo agregamos el 2016-11-16
     */
    private function complementoXML()
    {
        $this->totalIVA = 0;
        $this->totalISR = 0;
        $this->totalRet = 0;
        $this->subtotal = 0;
        $this->totalBaseIVA = 0;
        $this->totalBaseISR = 0;
        $this->totalBaseRet = 0;
        $this->subtotal = 0;
        $this->total = 0;
        $rate = 1;

        //$FACTURA = new Ventas(Session::getBd());	    
	    foreach ($this->documento->Detalles as $detalle)
	    {
            foreach ($this->docrelacionados as $value) 
            {
                if($value->FacUUID == $detalle->IdDocumento)
                {
                    $factura = $value;
                    foreach ($factura->Detalles as $val) 
                    {
                        if($val->DdeIva > 0)
                        {
                            $this->totalBaseIVA += $val->DdeImporte;
                            $this->totalIVATasa = $val->DdeIva; 
                        }
                        if($val->DdeRet > 0)
                        {
                            $this->totalBaseRet += $val->DdeImporte;
                            $this->totalRetTasa = $val->DdeRet;
                        }
                        if($val->DdeISR > 0)
                        {
                            $this->totalBaseISR += $val->DdeImporte;
                            $this->totalISRTasa = $val->DdeISR;
                        }                        
                    }
                    $this->totalIVA += $factura->FacIva;
                    $this->totalISR += $factura->FacIsr;
                    $this->totalRet += $factura->FacRet;
                    $this->total += $factura->FacTotal;
                    $this->subtotal += $factura->FacSubtotal;
                    break;
                }
            }	        	        	       
	    }	 
	    if($this->documento->FacTotal != $this->total)
	    {
	        $rate = $this->documento->FacTotal/$this->total;
	        $this->totalIVA = $rate * $this->totalIVA;
	        $this->totalISR = $rate * $this->totalISR;
	        $this->totalRet = $rate * $this->totalRet;
	        $this->subtotal = $rate * $this->subtotal;
	        $this->total = $rate * $this->total;
	    }
	    

        $complemento = $this->xml->createElement("cfdi:Complemento");
        $complemento = $this->root->appendChild($complemento);
        $cce = $this->xml->createElement("pago20:Pagos");
        $cce = $complemento->appendChild($cce);
        $this->cargaAtt($cce,
            array(
                "Version"=>"2.0"                
            )
            );
        if(isset($this->documento->FacTotal) && $this->documento->FacTotal > 0.00)
        {
            $totales = $this->xml->createElement("pago20:Totales");
            $total = $cce->appendChild($totales);
            if($this->totalISR > 0)
		    {
		        $this->cargaAtt($total,
		            array(
		                "TotalRetencionesISR"=>number_format($this->totalISR,2,".","")
		                ));
		    }
		    if($this->totalRet > 0)
		    {
		        $this->cargaAtt($total,
		            array(
		                "TotalRetencionesIVA"=>number_format($this->totalRet,2,".","")
		            ));
		    }
		    if($this->totalIVA > 0)
		    {
		        $tempIva = number_format($this->totalIVA,2,".","");
		        $tempTotal = number_format($rate * $this->totalBaseIVA,2,".","");
		        if($tempTotal == "3269.48")
		            $tempTotal = "3269.46";
		        if($tempIva == "3147.37")
		            $tempIva = "3147.38";
		       $this->cargaAtt($total, 
		        array(		           
		            "TotalTrasladosBaseIVA16"=>/*$tempTotal,//*/ number_format($rate * $this->totalBaseIVA,2,".",""),
		            "TotalTrasladosImpuestoIVA16"=>/*$tempIva //*/number_format($this->totalIVA,2,".","")		            
		        ));
		    }

            $this->cargaAtt($total,
                array(
                    "MontoTotalPagos"=>number_format(str_ireplace(',', '',$this->documento->FacTotal),2,".","")
                ));
            
            $pagos = $this->xml->createElement("pago20:Pago");
            $pago = $cce->appendChild($pagos);
            $this->cargaAtt($pago,
                array(
                    "FechaPago"=>$this->fechaFiscal($this->documento->FacFecha),
                    "FormaDePagoP"=>$this->documento->FacFormaPago,
                    "MonedaP"=>$this->documento->FacMoneda,
                    "TipoCambioP"=>($this->documento->FacParidad == 1)?1:$this->documento->FacParidad,
                    "Monto"=>number_format(str_ireplace(',', '',$this->documento->FacTotal),2,".","")
                )
                );
            if($this->documento->FacFormaPago !== "01")
                $this->cargaAtt($pago,
                    array("CtaOrdenante"=>$this->documento->FacCuenta));
                
                foreach ($this->documento->Detalles as $detalle)
                {
                    foreach ($this->docrelacionados as $key => $value) 
                    {
                        if($value->FacUUID == $detalle->IdDocumento)
                        {
                            $factura = $value;
                            break;
                        }
                    }	                     
                    if($factura->FacIva > 0)
                        $impDR = "02";
                    else $impDR = "01";

                    $relacionados = $this->xml->createElement("pago20:DoctoRelacionado");
                    $relacionado = $pago->appendChild($relacionados);
                    $this->cargaAtt($relacionado,
                        array(
                            "IdDocumento"=>$detalle->IdDocumento,
                            "ObjetoImpDR"=>$impDR,
                            "MonedaDR"=>$this->documento->FacMoneda,
                            "EquivalenciaDR"=>($this->documento->FacParidad == 1)?1:$this->documento->FacParidad,
                            "NumParcialidad"=>$detalle->NumParcialidad,
                            "ImpSaldoAnt"=>number_format(str_ireplace(',', '',$detalle->ImpSaldoAnt),2,".",""),
                            "ImpPagado"=>number_format(str_ireplace(',', '',$detalle->ImpPagado),2,".",""),
                            "ImpSaldoInsoluto"=>number_format(str_ireplace(',', '',$detalle->ImpSaldoInsoluto),2,".","")
                        )
                        );
                        if($factura->FacIva > 0.000 || $factura->FacIsr > 0.000 || $factura->FacRet > 0.00)
                        {
                            $impuestos = $this->xml->createElement("pago20:ImpuestosDR");
                            $impuestos = $relacionados->appendChild($impuestos);                        
                            if($factura->FacIsr > 0.000 || $factura->FacRet > 0.00)
                            {
                                $traslados = $this->xml->createElement("pago20:RetencionesDR");
                                $traslados = $impuestos->appendChild($traslados);
                                if($factura->FacIsr > 0.000)
                                {
                                    $traslado = $this->xml->createElement("pago20:RetencionDR");
                                    $traslado = $traslados->appendChild($traslado);
                                    
                                    $cuotaIsr = 1/($this->subtotal /($rate *  $factura->FacIsr));
                                    $this->cargaAtt($traslado,
                                        array(
                                            "BaseDR"=>number_format($rate * $factura->FacSubtotal,2,".",""), //$this->totalBaseISR
                                            "ImpuestoDR"=>"001",
                                            "TipoFactorDR"=>"Tasa",
                                            "TasaOCuotaDR"=>number_format(str_ireplace(',', '',$this->totalISRTasa),4,".","")."00",
                                            "ImporteDR"=>number_format(str_ireplace(',', '',$rate * $factura->FacIsr),2,".","")
                                        )
                                        );
                                }
                                if($factura->FacRet > 0.00)
                                {
                                    $traslado = $this->xml->createElement("pago20:RetencionDR");
                                    $traslado = $traslados->appendChild($traslado);
                                    $cuotaRet = 1/($this->subtotal /($rate * $factura->FacRet));
                                    $this->cargaAtt($traslado,
                                        array(
                                            "BaseDR"=>number_format($rate * $this->totalBaseRet,2,".",""),
                                            "ImpuestoDR"=>"002",
                                            "TipoFactorDR"=>"Tasa",
                                            "TasaOCuotaDR"=>number_format(str_ireplace(',', '',$this->totalRetTasa),6,".",""),
                                            "ImporteDR"=>number_format(str_ireplace(',', '',$rate * $factura->FacRet),2,".","")
                                        )
                                        );
                                }
                            }
                            if($factura->FacIva > 0.000)
                            {
                                $iva = ($this->documento->FacIva == 0.00)?0:0.160000;
                                $traslados = $this->xml->createElement("pago20:TrasladosDR");
                                $traslados = $impuestos->appendChild($traslados);		            		            
                                $traslado = $this->xml->createElement("pago20:TrasladoDR");
                                $traslado = $traslados->appendChild($traslado);
                                $this->cargaAtt($traslado,
                                    
                                    array(
                                        "BaseDR"=>number_format($rate * $factura->FacSubtotal,2,".",""), //$this->totalBaseIVA
                                        "ImpuestoDR"=>"002",
                                        "TipoFactorDR"=>"Tasa",
                                        "TasaOCuotaDR"=>"0.160000", //number_format(str_ireplace(',', '',$iva),6,".",""),
                                        "ImporteDR"=>number_format(str_ireplace(',', '',$rate * $factura->FacIva),2,".","")
                                    )
                                    );
                            }
                           
                        }
                }
                if($this->totalIVA > 0.000 || $this->totalISR > 0.00 || $this->totalRet > 0)
		    {
		        
		        $impuestos = $this->xml->createElement("pago20:ImpuestosP");
		        $impuestos = $pago->appendChild($impuestos);
		        if($this->totalISR > 0.00 || $this->totalRet > 0)
		        {
		            $traslados = $this->xml->createElement("pago20:RetencionesP");
		            $traslados = $impuestos->appendChild($traslados);
		            if($this->totalISR > 0.00)
		            {
		                $traslado = $this->xml->createElement("pago20:RetencionP");
		                $traslado = $traslados->appendChild($traslado);
		                $this->cargaAtt($traslado,
		                    array(
		                        //"BaseP"=>number_format($this->subtotal,2,".",""),
		                        "ImpuestoP"=>"001",
		                        "ImporteP"=>number_format($this->totalISR,2,".","")
		                    )
		                    );
		            }
		            if($this->totalRet > 0)
		            {
		                $traslado = $this->xml->createElement("pago20:RetencionP");
		                $traslado = $traslados->appendChild($traslado);
		                $this->cargaAtt($traslado,
		                    array(
		                        //"BaseP"=>number_format($this->subtotal,2,".",""),
		                        "ImpuestoP"=>"002",
		                        "ImporteP"=>number_format($this->totalRet,2,".","")
		                    )
		                    );
		            }
		        } 
		        /* Retenciones Agregadas en 08-10-2013   */		        			       			      
		        if($this->totalIVA > 0.000)
		        {
		            $tempIva = number_format($this->totalIVA,2,".","");
		            $tempTotal = number_format($rate * $this->totalBaseIVA,2,".","");
		            if($tempTotal == "3269.48")
		                $tempTotal = "3269.46";
	                if($tempIva == "3147.37")
	                    $tempIva = "3147.38";
		            
    		        $iva = ($this->totalIVA == 0)?0:0.160000;
    	            $traslados = $this->xml->createElement("pago20:TrasladosP");
    	            $traslados = $impuestos->appendChild($traslados);	                      
                    $traslado = $this->xml->createElement("pago20:TrasladoP");
                    $traslado = $traslados->appendChild($traslado);
                                                         
                    $this->cargaAtt($traslado,
                        array(
                            "BaseP"=>/*$tempTotal,//*/number_format($rate * $this->totalBaseIVA,2,".",""),
                            "ImpuestoP"=>"002",
                            "TipoFactorP"=>"Tasa",
                            "TasaOCuotaP"=>number_format(str_ireplace(',', '',$iva),6,".",""),
                            "ImporteP"=>/*$tempIva //*/number_format($this->totalIVA,2,".","")
                        )
                        );
		        }		                     
		    }	                                
        }                
    }       
}