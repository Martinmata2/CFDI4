<?php
namespace Marve\CFDI4\Core;

use XSLTProcessor;

abstract class Xml extends Nodo
{

    /**
     * 
     * @param \DOMDocument $xml
     * @param string $folder
     * @param string $cadena_original
     */
    protected function genera_cadena_original(\DOMDocument &$xml, string $folder, string &$cadena_original):void
    {        
        //libxml_disable_entity_loader(false);
        $paso = new \DOMDocument;
        $paso->loadXML($xml->saveXML());
        $xsl = new \DOMDocument;
        $ruta = "documentos/";
        $file= $ruta."cadenaoriginal_4_0_2.xslt";      // Ruta al archivo
        $xsl->load($file);        
        @$proc = new XSLTProcessor();
        @$proc->importStyleSheet($xsl);        
        @$cadena_original = $proc->transformToXML($paso);        
    }
    
    /**
     * 
     * @param \DOMDocument $xml
     * @param string $folder
     * @return bool
     */
    protected function valida(\DOMDocument &$xml, string $folder):bool
    {
        //$this->xml->formatOutput=true;
        $paso = new \DOMDocument;
        $texto = $xml->saveXML();
        $paso->loadXML($texto);
        libxml_use_internal_errors(false);
        $ruta = $folder."documentos/";
        $file= $ruta."cfdv40.xsd";
        $ok = @$paso->schemaValidate($file);
        return $ok;
    }
}