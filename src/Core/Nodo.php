<?php
namespace Marve\CFDI4\Core;

abstract class Nodo
{    
    
    protected function cargaAtt(&$nodo, $attr):void
    {
        
        foreach ($attr as $key => $val)
        {
            $val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
            $val = trim($val);                           // Regla 5b
            if (strlen($val)>0)
            {   // Regla 6
                $val = $this->utf8decode(str_replace("|","/",$val)); // Regla 1
                $val = str_replace("|","/",$val); // Regla 1
                $nodo->setAttribute($key,$val);
            }
        }
    }
    
    /**
    * Formatea la fecha como la requiere el documento fiscal
    * @param string $fecha
    */
    protected function fechaFiscal(string $fecha):string
    {
        
        trim($fecha);
        $aux = str_replace(" ", "T", $fecha);
        return ($aux);
    }

    protected function utf8decode($string)
    {
        return mb_convert_encoding($string,"UTF-8",mb_detect_encoding($string));
    }
}