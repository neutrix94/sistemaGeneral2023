<?php
    class Encrypt{
        private $key;
        
        function __construct(){
            $this->key = '851f4bba6b2b1944ea3667cd361120ea';
        }
        function encryptText($plaintext, $key) {
            // Generar un vector de inicialización aleatorio
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        
            // Cifrar el texto plano usando AES-256-CBC con la clave y el IV generados
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $this->key, 0, $iv);
        
            // Combina el IV y el texto cifrado y codifícalo en base64
            $encrypted = base64_encode($iv . $ciphertext);
        
            return $encrypted;
        }
        
        function decryptText($encrypted, $key) {
            // Decodificar el texto cifrado en base64
            $data = base64_decode($encrypted);
            // Extraer el IV del inicio del texto cifrado
            $ivSize = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($data, 0, $ivSize);
            $ciphertext = substr($data, $ivSize);
            // Descifrar el texto usando AES-256-CBC con la clave y el IV extraídos
            $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $this->key, 0, $iv);
            return $plaintext;
        }
    }
    
?>