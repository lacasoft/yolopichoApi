<?php

namespace Yolopicho\Utilities;

class AesEncryptor {

    public static function encryptAES(string $data) {
        $cipher = "aes-256-cbc";
        $options = 0;
        $aesKey = getenv('AES_KEY');
        $iv = getenv('AES_IV');
        return base64_encode(openssl_encrypt($data, $cipher, $aesKey, $options, $iv));
    }

    public static function decryptAES(string $data) {
        $cipher = "aes-256-cbc";
        $options = 0;
        $aesKey = getenv('AES_KEY');
        $iv = getenv('AES_IV');
        return openssl_decrypt(base64_decode($data), $cipher, $aesKey, $options, $iv);
    }
}