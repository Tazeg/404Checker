<?php
//----------------------------------------------------------------------
//  AUTHOR	: Jean-Francois GAZET
//  WEB 	: http://www.jeffprod.com
//  TWITTER	: @JeffProd
//  MAIL	: jeffgazet at gmail dot com
//  LICENCE : GNU GENERAL PUBLIC LICENSE Version 2, June 1991
//----------------------------------------------------------------------

class URL
    {
    private $_url; // http or https://www.example.com/page/html
    private $_headers; // see http://php.net/manual/fr/function.get-headers.php
    private $_content=''; // file content if text-file
    
    public function __construct($url) {
        $this->_url=$url;
        $this->_headers=@get_headers($url, 1);
        //print_r($this->_headers);
        //Array
        //(
        //    [0] => HTTP/1.1 200 OK
        //    [Date] => Sat, 13 Dec 2014 09:59:01 GMT
        //    [Server] => Apache
        //    [Vary] => Accept-Encoding
        //    [Content-Length] => 2642
        //    [Connection] => close
        //    [Content-Type] => text/html;charset=UTF-8
        //)
        }
        
    public function getUrl() {
        return $this->_url;
        }
        
    public function getTaille() {
        return $this->_headers['Content-Length'];
        }
        
    public function getType() {
        
        // 404 ?
        if(!isset($this->_headers['Content-Type'])) {return '';}
        
        // $this->_headers['Content-Type'] is either :
        // a string : 'text/html; charset=UTF-8'
        // or an array :
        // [0] => text/html
        // [1] => text/html; charset=UTF-8
                
        if(is_array($this->_headers['Content-Type'])) {
            return $this->_headers['Content-Type'][0];
            }
        return $this->_headers['Content-Type'];
        }
        
    public function getHttpCode() {
        // $this->_headers['0'] is
        // 'HTTP/1.1 403 Forbidden' 
        // or
        // 'HTTP/1.1 200 OK'
        $s=explode(' ',$this->_headers['0']);
        if(isset($s[1])) {return $s[1];}
        return '';
        }
        
    public function getContent() {
        if($this->_content==''
            && preg_match('|text/html|',$this->getType()) // text file, not picture
            ) {
            $this->_content=@file_get_contents($this->_url);
            }
        return $this->_content; // mis à '' en définition
        }

    } // class URL
?>
