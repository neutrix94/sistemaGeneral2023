<?php
    class Encrypt{
        /*private $key='851f4bba6b2b1944ea3667cd361120ea';
        private $iv='bfb568085e26cd25';*/
        
        function __construct(){
            
        }
        function encryptText($plaintext, $key) {
            $key='851f4bba6b2b1944ea3667cd361120ea';
            $iv='bfb568085e26cd25';
            // Generar un vector de inicialización aleatorio
            //$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        
            // Cifrar el texto plano usando AES-256-CBC con la clave y el IV generados
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
        
            // Combina el IV y el texto cifrado y codifícalo en base64
            $encrypted = base64_encode($iv . $ciphertext);
        
            return $encrypted;
        }
        
        function decryptText($encrypted, $key) {
            $key='851f4bba6b2b1944ea3667cd361120ea';
            $iv='bfb568085e26cd25';
            // Decodificar el texto cifrado en base64
            $data = base64_decode($encrypted);
            // Extraer el IV del inicio del texto cifrado
            $ivSize = openssl_cipher_iv_length('aes-256-cbc');
            //$iv = substr($data, 0, $ivSize);
            $ciphertext = substr($data, $ivSize);
            // Descifrar el texto usando AES-256-CBC con la clave y el IV extraídos
            $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
            return $plaintext;
        }
    }
    
?>