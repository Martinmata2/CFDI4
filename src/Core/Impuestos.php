<?php

namespace Marve\CFDI4\Core;

class Impuestos extends Conceptos
{

    protected $total;

    public function __construct(string $base_datos, $documento)
    {
        parent::__construct($base_datos,$documento);
        $this->total = 0;
    }
    protected function impuestosXml()
    {
        $impuestoieps = 0.00;
        //if($this->documento->FacIva > 0.000 || $this->documento->FacRet > 0.000 || $this->documento->FacIsr > 0.000|| $this->documento->FacIEPS)
        if($this->documento->FacIva >= 0.000 || $this->documento->FacRet > 0.000 || $this->documento->FacIsr > 0.000)
        {
            $impuestos = $this->xml->createElement("cfdi:Impuestos");
            $impuestos = $this->root->appendChild($impuestos);
            /* Retenciones Agregadas en 08-10-2013   */
            if($this->documento->FacRet > 0.000000 || $this->documento->FacIsr > 0.000000)
            {
                $retenciones = $this->xml->createElement("cfdi:Retenciones");
                $retenciones = $impuestos->appendChild($retenciones);
                if($this->documento->FacRet > 0.000000)
                {
                    $retencion = $this->xml->createElement("cfdi:Retencion");
                    $retencion = $retenciones->appendChild($retencion);
                    $this->cargaAtt($retencion,
                        array(
                            //"Base"=>number_format(str_ireplace(',', '',$this->documento->FacIva),2,".",""),
                            "Impuesto"=>"002",
                            //"TipoFactor"=>"Tasa",
                            //"TasaOCuota"=>".106666",
                            "Importe"=>number_format(str_ireplace(',', '',$this->totalRet),2,".","")
                        )
                        );
                }
                if($this->documento->FacIsr > 0.000000)
                {
                    $retencion = $this->xml->createElement("cfdi:Retencion");
                    $retencion = $retenciones->appendChild($retencion);
                    $this->cargaAtt($retencion,
                        array(
                            //"Base"=>number_format(str_ireplace(',', '',$this->documento->FacSubtotal),2,".",""),
                            "Impuesto"=>"001",
                            //"TipoFactor"=>"Tasa",
                            //"TasaOCuota"=>".100000",
                            "Importe"=>number_format(str_ireplace(',', '',$this->totalISR),2,".","")
                        )
                        );
                }
            }
            /*Esto Fue Agregado para trabajar IEPS                                       */
            
            $ieps = array();            
            if(strpos($this->documento->FacIEPS,":") !== FALSE)
            {
                $temp = explode(":", $this->documento->FacIEPS);
                $tempieps = explode(";", $temp[1]);
                foreach ($tempieps as $value)
                {
                    $impuesto = array();
                    $key = explode("|", $value);
                    $impuesto["cantidad"] = number_format(((float)$key[0]*1.00),2,".","");
                    $impuesto["porciento"] = @((float)$key[1]* 1.00);
                    $ieps[] = $impuesto;
                }
                for ($i = 0; $i < count($ieps); $i++)
                {
                    $porcentaje = $ieps[$i]["porciento"];
                    for ($j = $i+1; $j < count($ieps); $j++)
                    {
                        if($porcentaje == $ieps[$j]["porciento"])
                        {
                            $ieps[$i]["cantidad"] += ($ieps[$j]["cantidad"] * 1.00);
                            $ieps[$j]["porciento"] = 0.00;
                            $ieps[$j]["cantidad"] = 0.00;
                            
                        }
                    }
                    
                    $impuestoieps +=  ($ieps[$i]["cantidad"]*1.00);
                }
                foreach ($ieps as $value)
                {
                    if($value["porciento"] == 0.000)
                        unset($value);
                }
            }
            
            //se agrego >= en vez de > para incluir iva 0
            if($this->documento->FacIva >= 0 || $impuestoieps > 0)
            {
                $iva = ($this->documento->FacIva == 0)?0.000000:0.160000;
                $traslados = $this->xml->createElement("cfdi:Traslados");
                $traslados = $impuestos->appendChild($traslados);
                
                if($this->documento->FacIva > 0)
                {
                    $traslado = $this->xml->createElement("cfdi:Traslado");
                    $traslado = $traslados->appendChild($traslado);
                    $this->cargaAtt($traslado,
                        
                        array(                           
                            "Impuesto"=>"002",
                            "TipoFactor"=>"Tasa",
                            "TasaOCuota"=>number_format(str_ireplace(',', '',$iva),6,".",""),
                            "Importe"=>number_format(str_ireplace(',', '',$this->totalIVA),2,".",""),
                            "Base"=>number_format(str_ireplace(',', '',$this->totalIVAImp),2,".","")
                        )
                        );
                }
                //se agrego para incluir facturas con productos con iva y sin iva 8/28/2018
                if($this->ivaZero)
                {
                                         
                     if($this->documento->FacTotal - $this->exento > 0)
                     {
                         $traslado = $this->xml->createElement("cfdi:Traslado");
                         $traslado = $traslados->appendChild($traslado);
                         $this->cargaAtt($traslado,
                             array(
                                 "Base"=>number_format(str_ireplace(',', '',$this->totalIvaZero),2,".",""),
                                 "Impuesto"=>"002",
                                 "TipoFactor"=>"Tasa",
                                 "TasaOCuota"=>"0.000000",
                                 "Importe"=>"0.00"
                             )
                             );
                     }
                     if($this->exento > 0)
                     {
                         $traslado = $this->xml->createElement("cfdi:Traslado");
                         $traslado = $traslados->appendChild($traslado);
                         $this->cargaAtt($traslado,                         
                         array(
                            "Base"=>number_format(str_ireplace(',', '',$this->exento),2,".",""),
                            "Impuesto"=>"002",
                            "TipoFactor"=>"Exento"                            
                         )
                         );
                     }
                     
                }
                if($impuestoieps > 0)
                {
                    foreach ($ieps as $value)
                    {
                        if($value["porciento"] > 0)
                        {
                            $traslado = $this->xml->createElement ( "cfdi:Traslado" );
                            $traslado = $traslados->appendChild ( $traslado );
                            $this->cargaAtt ( $traslado, array (
                                
                                "Impuesto" => "003",
                                "TipoFactor"=>"Tasa",
                                "TasaOCuota"=>number_format ( str_ireplace ( ',', '', ($value ["porciento"]/100) ), 6, ".", "" ),
                                "Importe" => number_format ( str_ireplace ( ',', '', $value ["cantidad"] ), 2, ".", "" ),
                                "Base" => number_format(str_ireplace ( ',', '', $this->totalIepsImp), 2, ".", "")
                            ) );
                        }
                    }
                }
                
                if($this->totalIVA >= 0)
                {
                    //se agrego para incluir IVA excento 29-10-2024
                    if($this->documento->FacTotal - $this->exento > 0)
                        $impuestos->SetAttribute("TotalImpuestosTrasladados",number_format((1.000*(str_ireplace(',', '',$this->totalIVA))+$impuestoieps),2,".",""));
                }
            }
            if($this->documento->FacRet > 0 || $this->documento->FacIsr > 0)
            {
                $tempret = number_format(str_ireplace(',', '',$this->totalRet),2,".","");
                $tempisr = number_format(str_ireplace(',', '',$this->totalISR),2,".","");
                $temptotal = $tempret + $tempisr;
                $impuestos->SetAttribute("TotalImpuestosRetenidos",number_format(str_ireplace(',', '',$temptotal),2,".",""));
            }
        }
        $this->subtotal = number_format(str_ireplace(',', '',$this->subtotal),2,".","");
        $this->total = $this->subtotal - $this->descuento + $this->totalIVA + $impuestoieps - ($this->totalISR + $this->totalRet);
        $this->total = number_format(str_ireplace(',', '',$this->total),2,".","");
        $this->descuento = number_format(str_ireplace(',', '',$this->descuento),2,".","");
        $this->root->setAttribute("Total",$this->total);
        $this->root->setAttribute("SubTotal",$this->subtotal);
        
        if($this->descuento > 0)
            $this->root->setAttribute("Descuento", $this->descuento);
            
    }
}