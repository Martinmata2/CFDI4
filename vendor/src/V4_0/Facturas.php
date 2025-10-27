<?php
namespace Marve\CFDI\V4_0;

use Marve\CFDI\Core\Documento;
use stdClass;

/**
 * Clase para formar y sellar el cfdi y dejarlo listo para enviar al proveedor de estampas
*
*/

class Facturas extends Documento
{
    

	public $folder;

	function __construct(string $base_datos, stdClass $documento, $propio, $cliente, $folder)
	{
	   parent::__construct($base_datos, $documento, $propio, $cliente, $folder);	   	   
	}
	/**
	 *
	 * @param string $folder
	 * @return string
	 */
	function formarXml()
	{		
		//Genera xml		
		$this->encabezado();
		//Agrega relacionado		
		if($this->documento->RelTipo != "0" && $this->documento->RelTipo != "" )
		{		    
		    $this->relacionados(); 
		}
		if(isset($this->documento->FacPeriodicidad) && strlen($this->documento->FacPeriodicidad) > 1)
		    $this->globalXml();
		//Datos del emisor		
	    $this->emisorXml();
	    //Datos de receptor		
		$this->receptorXml();
		//Productos Articulos
		$this->conceptosXml();
		//Impuestos		
		$this->impuestosXml();
		//Agregar complementos		
		if(isset($this->documento->Complementos))
			$this->complementoXML($this->documento->Complementos->nombre);
		//Genera cadena original		
		$this->genera_cadena_original($this->xml, $this->folder,$this->cadena_original );
		//Sella xml
		$this->sella();		
		//valida
		//$done = $this->valida($this->xml, $this->folder);
		$this->xml->formatOutput = true;
		return $this->xml->saveXML ();
		
	}

	protected function encabezado()
    {
        $this->root = $this->xml->createElement("cfdi:Comprobante");
        $this->root = $this->xml->appendChild($this->root);		
        
        $this->cargaAtt($this->root,	array(
            "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd",            //
            "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
            "xmlns:implocal"=>"http://www.sat.gob.mx/implocal",           
            "xmlns:cfdi"=>"http://www.sat.gob.mx/cfd/4"
           
        ));
        
        if($this->documento->FacTipoComprobante == "T")
        {
            $this->cargaAtt($this->root, array(
                "Version"=>"4.0",
                //"Fecha"=>$this->fechaFiscal($this->documento->FacFecha),
                "Fecha"=>$this->fechaFiscal($this->documento->FacFecha),
                "Sello"=>"@",
                "Serie"=>$this->documento->FacSerie,
                "Folio"=>$this->documento->FacFolio,
                //"NumCtaPago"=> $this->documento->FacCuenta,
                //"FormaPago"=>$this->documento->FacFormaPago,
                "NoCertificado"=>$this->owner->ProNoCertificado,
                "Certificado"=>"@",
                "SubTotal"=>0.00,
                "Moneda"=>"XXX", //$this->documento->FacMoneda,
                //"TipoCambio"=>($this->documento->FacMoneda == "MXN")?1:number_format(str_ireplace(',', '',$this->documento->FacParidad),6,".",""),
                "Total"=>0.00,
                "TipoDeComprobante"=>$this->documento->FacTipoComprobante,
                "Exportacion"=>"01",
                "LugarExpedicion"=>$this->owner->ProCp)
                );
        }
        else 
        {
            $this->cargaAtt($this->root, array(
                "Version"=>"4.0",
                "Fecha"=>$this->fechaFiscal($this->documento->FacFecha),
                "Sello"=>"@",
                "Serie"=>$this->documento->FacSerie,
                "Folio"=>$this->documento->FacFolio,
                //"NumCtaPago"=> $this->documento->FacCuenta,
                "FormaPago"=>$this->documento->FacFormaPago,
                "NoCertificado"=>$this->owner->ProNoCertificado,
                "Certificado"=>"@",
                //agregado para descuento
                //"Descuento"=>"@",
                "SubTotal"=>number_format(str_ireplace(',', '',$this->documento->FacSubtotal),2,".",""),
                //"Descuento"=>number_format(str_ireplace(',', '',$this->documento->FacDescuento),2,".",""),
                "Moneda"=>$this->documento->FacMoneda,
                "TipoCambio"=>($this->documento->FacMoneda == "MXN")?1:number_format(str_ireplace(',', '',$this->documento->FacParidad),6,".",""),
                "Total"=> number_format(str_ireplace(',', '', $this->documento->FacTotal),2,".",""),
                "TipoDeComprobante"=>$this->documento->FacTipoComprobante,
                "Exportacion"=>"01",
                "MetodoPago"=>$this->documento->FacMetodoPago,
                "LugarExpedicion"=>$this->owner->ProCp)
                );
        }        
    }

	/**
	 * Lo agregamos el 2016-11-16
	 */
	private function complementoXML($tipo = "ninguno")
	{

		if($tipo == "ninguno")
		{
		}
		elseif($tipo == "impuestoLocal")
		{
			$complemento = $this->xml->createElement("cfdi:Complemento");
			$complemento = $this->root->appendChild($complemento);
			$cce = $this->xml->createElement("implocal:ImpuestosLocales");
			$cce = $complemento->appendChild($cce);
			$this->cargaAtt($cce,
					array(
							"xsi:schemaLocation"=>"http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd",
							"xmlns:implocal"=>"http://www.sat.gob.mx/implocal",
							"TotaldeTraslados"=>$this->documento->Complementos->TotaldeTraslados,
							"TotaldeRetenciones"=>$this->documento->Complementos->TotaldeRetenciones,
							"version"=>"1.0"
				
					)
					);
			if(isset($this->documento->Complementos->TotaldeTraslados) && $this->documento->Complementos->TotaldeTraslados > 0.00)
			{
			    $traslados = $this->xml->createElement("implocal:TrasladosLocales");
			    $traslado = $cce->appendChild($traslados);
			    $this->cargaAtt($traslado,
			        array(
			            "ImpLocTrasladado"=>$this->documento->Complementos->ImpLocTrasladado,
			            "TasadeTraslado"=>$this->documento->Complementos->TasadeTraslado,
			            "Importe"=>number_format(str_ireplace(',', '',$this->documento->Complementos->ImporteTraslado),2,".","")
			        )
			        );
			    $this->total += $this->documento->Complementos->ImporteTraslado;
			}
			if(isset($this->documento->Complementos->TotaldeRetenciones) && $this->documento->Complementos->TotaldeRetenciones > 0.00)
			{
			    $traslados = $this->xml->createElement("implocal:RetencionesLocales");
			    $traslado = $cce->appendChild($traslados);
			    $this->cargaAtt($traslado,
			        array(
			            "ImpLocRetenido"=>$this->documento->Complementos->ImpLocRetenido,
			            "TasadeRetencion"=>$this->documento->Complementos->TasadeRetencion,
			            "Importe"=>number_format(str_ireplace(',', '',$this->documento->Complementos->ImporteRetenido),2,".","")
			        )
			        );
			    $this->total -= $this->documento->Complementos->ImporteRetenido;
			}
			//Agregue esto para incluir impuestos locales en cfdi 3.3
			
			$this->root->setAttribute("Total",$this->total);
		}

		elseif ($tipo == "ComercioExterior")
		{

			$complemento = $this->xml->createElement("cfdi:Complemento");
			$complemento = $this->root->appendChild($complemento);
			$cce = $this->xml->createElement("cce:ComercioExterior");
				
			$cce = $complemento->appendChild($cce);
			$this->cargaAtt($cce,
					array(
							"TotalUSD"=>number_format(trim($this->documento->Complementos->TotalUSD,','),2,".",""),
							"TipoCambioUSD"=>$this->documento->Complementos->TipoCambioUSD,
							"Observaciones"=>$this->documento->Complementos->Observaciones,
							"Subdivision"=>$this->documento->Complementos->Subdivision,
							"Incoterm"=>$this->documento->Complementos->Incoterm,
							"NumeroExportadorConfiable"=>$this->documento->Complementos->NumeroExportadorConfiable,
							"CertificadoOrigen"=>$this->documento->Complementos->CertificadoOrigen,
							"ClaveDePedimento"=>$this->documento->Complementos->ClaveDePedimento,
							"TipoOperacion"=>$this->documento->Complementos->TipoOperacion,
							"Version"=>"1.0"
					)
					);
				
			$receptor = $this->xml->createElement("cce:Receptor");
			$receptor = $cce->appendChild($receptor);
			$this->cargaAtt($receptor,
					array(
							"NumRegIdTrib"=>$this->documento->Complementos->receptor->CliRegistro
					)
					);
			$destinatario = $this->xml->createElement("cce:Destinatario");
			$destinatario = $cce->appendChild($destinatario);
			$this->cargaAtt($destinatario,
					array(
							"NumRegIdTrib"=>$this->documento->Complementos->destinatario->CliRegistro
					)
					);
			$domicilio = $this->xml->createElement("cce:Domicilio");
			$domicilio = $destinatario->appendChild($domicilio);
			$this->cargaAtt($domicilio,
					array(
							"Calle"=>$this->documento->Complementos->destinatario->CliCalle,
							"Estado"=>$this->documento->Complementos->destinatario->CliEstado,
							"CodigoPostal"=>$this->documento->Complementos->destinatario->CliCp,
							"Pais"=>$this->documento->Complementos->destinatario->CliPais,
					)
					);
				
				
			$mercancias = $this->xml->createElement("cce:Mercancias");
			$mercancias = $cce->appendChild($mercancias);
			foreach ($this->documento->Detalles as $detalle)
			{
				$mercancia = $this->xml->createElement("cce:Mercancia");
				$mercancia = $mercancias->appendChild($mercancia);
				$this->cargaAtt($mercancia,
						array(
								"NoIdentificacion"=>$detalle->DdeNoIdentificacion,
								"FraccionArancelaria"=>$detalle->DdeArancelaria,
								"ValorDolares"=>number_format(str_ireplace(',', '',$detalle->DdeImporte),2,".","")
						)
						);
					
			}
		}
	}	
}