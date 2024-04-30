<?php
    class Encrypt{
        function __construct(){
            
        }
        function encryptText($plaintext, $key) {
            // Generar un vector de inicialización aleatorio
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        
            // Cifrar el texto plano usando AES-256-CBC con la clave y el IV generados
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
        
            // Combina el IV y el texto cifrado y codifícalo en base64
            $encrypted = base64_encode($iv . $ciphertext);
        
            return $encrypted;
        }
        
        /* Ejemplo de uso:
        $texto = "Hola, mundo!";
        $clave = "mi_clave_secreta";
        
        $textoEncriptado = encryptText($texto, $clave);
        echo "Texto encriptado: " . $textoEncriptado;*/

        function decryptText($encrypted, $key) {
            // Decodificar el texto cifrado en base64
            $data = base64_decode($encrypted);
            // Extraer el IV del inicio del texto cifrado
            $ivSize = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($data, 0, $ivSize);
            $ciphertext = substr($data, $ivSize);
            // Descifrar el texto usando AES-256-CBC con la clave y el IV extraídos
            $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
            return $plaintext;
        }
    }
    
    /* Ejemplo de uso:
    $textoDesencriptado = decryptText($textoEncriptado, $clave);
    echo "Texto desencriptado: " . $textoDesencriptado;*/

    
?>