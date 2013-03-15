<?php
/**
 +------------------------------------------------------------------------------
 * Des 加密实现类
 * 兼容java c#等其他语言一致性
 * 
 +------------------------------------------------------------------------------
 * @author    lnczx <lnczx0915@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */

class DesCrypt {

    /**
     +----------------------------------------------------------
     * 加密字符串
     *
     +----------------------------------------------------------
     * @param string $text 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
        
    public static function encrypt($text, $key, $mode = MCRYPT_MODE_ECB) {
        if ($text == "") {
            return "";
        }
        
        $y = self::pkcs5_pad($text);
        $td = mcrypt_module_open(MCRYPT_DES, '', $mode, ''); //使用MCRYPT_DES算法,cbc模式
       /// $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        mcrypt_generic_init($td, $key, $key);       //初始处理
        $encrypted = mcrypt_generic($td, $y);       //加密
        mcrypt_generic_deinit($td);       //结束
        mcrypt_module_close($td);
        return base64_encode($encrypted);        
    }    

    /**
     +----------------------------------------------------------
     * 解密字符串
     *
     +----------------------------------------------------------
     * @param string $encrypted 字符串
     * @param string $key 加密key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public static function decrypt($key, $encrypted, $mode = MCRYPT_MODE_ECB) {
        
        if ($encrypted == "") {
            return "";
        }
                
        $encrypted = base64_decode($encrypted);
        $td = mcrypt_module_open(MCRYPT_DES,'',$mode,''); //使用MCRYPT_DES算法
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        mcrypt_generic_init($td, $key, $key);       //初始处理
        $decrypted = mdecrypt_generic($td, $encrypted);       //解密
        mcrypt_generic_deinit($td);       //结束
        mcrypt_module_close($td);
        $y = self::pkcs5_unpad($decrypted);
        return $y;
    }

    private static function pkcs5_pad($text,$block=8) {
        $pad = $block - (strlen($text) % $block);
        return $text . str_repeat(chr($pad), $pad);
    }
    
    private static function pkcs5_unpad($text) {
       $pad = ord($text{strlen($text)-1});
       if ($pad > strlen($text)) return $text;
       if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return $text;
       return substr($text, 0, -1 * $pad);
    }

}
