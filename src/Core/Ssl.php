<?php
namespace Marve\CFDI4\Core;


class Ssl extends Xml
{
    protected function Sellar($owner, string $folder, string &$cadena_original)
    {
        try 
        {                               
            $ruta = $folder;
            $file = $ruta."key.pem";      // Ruta al archivo        
            $pkeyid = openssl_get_privatekey(file_get_contents($file),$owner->ProClave);            
            $crypttext = "";
            openssl_sign($cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA256);            
            return  base64_encode($crypttext);      // lo codifica en formato base64
        } 
        catch (\Exception $e) 
        {            
            return "@";
        }
        
    }
    
    protected function Certificar($owner, string $folder)
    {
        try
        {
            $ruta = $folder;
            $file = $ruta."cer.pem";      // Ruta al archivo de Llave publica
            $datos = file($file);
            $certificado = "";
            $carga = false;
            for ($i=0; $i<sizeof($datos); $i++)
            {
                if (strstr($datos[$i],"END CERTIFICATE")) 
                    $carga = false;
                if ($carga) 
                    $certificado .= trim($datos[$i]);
                if (strstr($datos[$i],"BEGIN CERTIFICATE")) 
                    $carga=true;
            }
            return $certificado;
        }
        catch (\Exception $e)
        {            
            return "@";
        }
        
    }
    
}