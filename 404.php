<?php
//----------------------------------------------------------------------
//  AUTHOR	: Jean-Francois GAZET
//  WEB 	: http://www.jeffprod.com
//  TWITTER	: @JeffProd
//  MAIL	: jeffgazet at gmail dot com
//  LICENCE : GNU GENERAL PUBLIC LICENSE Version 2, June 1991
//----------------------------------------------------------------------

//--------------------------------------------------------------------
//  CONFIG
//--------------------------------------------------------------------

// Max files to check
define('MAX_FILES',1000);

// Max time in seconds
define('MAX_EXECUTION_TIME',300);

// Url to not check
$listAvoid=array(
    'http://www.example.com/page1.html',
    'http://www.example.com/page2.html'
    );
    
// External domains on whose external links will be checked
// but check as root domain will be refused
$domainAvoid=array(
    'google',
    'yahoo',
    'bing',
    'baidu',
    'wikipedia',
    'facebook',
    'microsoft',
    'archive',
    'twitter',
    'instagram',
    'youtube'
    );    

// END CONFIG
//--------------------------------------------------------------------

define('VERSION',"404 v.20150319 by @JeffProd\n");
define('USAGE',"Usage : php 404.php [url]\n");

if(isset($argv[1])) {$START_URL = $argv[1];}

if(!isset($START_URL)) {
    error(VERSION.USAGE);
    exit;
    }
    
if(!filter_var($START_URL,FILTER_VALIDATE_URL)){
    error(VERSION."ERROR : Invalid URL\n".USAGE);
    exit;
    }

$liste=array($START_URL);

//--------------------------------------------------------------------

require 'url_to_absolute.php'; 
require 'class.url.php';
require 'class.simple_html_dom.php';
require 'class.timer.php';
require 'lang.php';
require 'inc.httpcodes.php';

// Check refused domains
foreach($domainAvoid as $d) {
    $tmp=parse_url($liste[0]);
    if(preg_match("|$d|",$tmp['host'])) {
        error('ERROR REFUSED DOMAIN : '.$tmp['host']);
        exit;
        }
    }

// Parent url array
$arrParent=array();

// Counter HTTP codes
$cpt=array();

// Chrono
$gMyTimer=new MyTimer();
$gMyTimer->start();

// Loop on each URL
$i=0;
while($i<count($liste))
    {
    if($i==MAX_FILES) {
        error("\n".LNG_LIMIT_MAX_ATTEINTE.' : '.MAX_FILES.' '.LNG_FILES);
        break;
        }
    if($gMyTimer->get()>=MAX_EXECUTION_TIME) {
        error("\n".LNG_LIMIT_MAX_TEMPS.' : '.MAX_EXECUTION_TIME.'s');
        break;
        }        
        
    $url=new URL($liste[$i]);
    $tmpcode=$url->getHttpCode();
    
    if(isset($cpt[$tmpcode])) {$cpt[$tmpcode]++;}
    else {$cpt[$tmpcode]=1;}
        
    // If 404, show parents
    if($tmpcode==404) {
        sendMsg("\n=> 404 ".$HTTP_CODES[404].' : '.$url->getUrl());
        reset($arrParent);
        foreach($arrParent as $tmp) {
            list($tmpParent,$tmpEnfant)=explode('|',$tmp);
            if($liste[$i]==$tmpEnfant) {
                sendMsg(LNG_PARENT.' : '.$tmpParent);
                }
            }
        }     

    $c=count($liste);
    showStats(($i+1),$c,round((($i+1)/$c)*100,0),$gMyTimer->get(2));
        
    // Search for Url children, 
    // - for root domain only
    // - no more than MAX_FILES
    $arrChilds=array();
    if($c<=MAX_FILES && 
        preg_match("@".$liste[0]."@",$url->getUrl())) {
        $arrChilds=findChilds($url);
        }
    
    // Adding children Url :
    // - to the urls list, if not present, and not excluded
    // - into the parents array
    foreach($arrChilds as $urltmp) {
        
        // Add to Url list
        if(!in_array($urltmp,$liste)
            && !in_array($urltmp,$listAvoid)
            ) {
            $liste[]=$urltmp;
            }
            
        // Add to parent/child list
        if(!in_array($liste[$i].'|'.$urltmp,$arrParent)) {
            $arrParent[]=$liste[$i].'|'.$urltmp; // 'parent|child'
            }
        } // foreach
        
    $i++;
    }

sendMsg(''); // carriage return

//---------------------------------------------------------------------
//  FUNCTIONS
//---------------------------------------------------------------------
    
function error($txt) {
    sendMsg($txt);
    exit;
    }   
    
function sendMsg($data) {
    echo "$data\n";
    }
    
function showStats($x,$y,$p,$timer) {  
    global $cpt; 
      
    $txt = LNG_TIME.' : '.$timer.' - '.
        LNG_FILE.' : '.$x.'/'.$y.' ('.$p."%) - ".
        LNG_HTTP_CODES.' : ';
    
    reset($cpt);
    while(list($key,$val)=each($cpt)) {
        $txt.=$key.' ('.$val.') ';
        }    
    
    echo "$txt\r";
    }
    
function findChilds($url) {
    // Looking for children urls of $url
    // IN : (object URL) $url
    // OUT : (array of URL) 
    
    $arrOut=array();
    
    // Example :
    // $url->getUrl() is 'http://php.net/manual/fr/function.parse-url.php?toto=2#ancre'
    // 'scheme' : http
    // 'host' : php.net
    // 'port'
    // 'user'
    // 'pass'
    // 'path' : /manual/fr/function.parse-url.php
    // 'query' : after ?
    // 'fragment' : after #
    $arrUrlInfos=parse_url($url->getUrl());

    // 404 or not text
    if($url->getContent()=='') {return $arrOut;}

    // cf. http://simplehtmldom.sourceforge.net/manual.htm
    $html = str_get_html($url->getContent());
    if(!$html) {return $arrOut;}

    // img src
    foreach($html->find('img') as $element) {
        $tmp=chemin($element->src,$arrUrlInfos,$url->getUrl());
        if($tmp!='') {$arrOut[]=$tmp;}
        }

    // a href
    foreach($html->find('a') as $element) {
        $tmp=chemin($element->href,$arrUrlInfos,$url->getUrl());
        if($tmp!='') {$arrOut[]=$tmp;}
        }
        
    // link href
    foreach($html->find('link') as $element) {
        $tmp=chemin($element->href,$arrUrlInfos,$url->getUrl());
        if($tmp!='') {$arrOut[]=$tmp;}
        }
        
    // script src
    foreach($html->find('script') as $element) {
        $tmp=chemin($element->src,$arrUrlInfos,$url->getUrl());
        if($tmp!='') {$arrOut[]=$tmp;}
        }          

    return $arrOut;
    } // function findChilds()
   
function chemin($fic,$urlInfos,$parent)
    {
    // Build full url from :
    // IN : $fic = relative or absolute path found in href, src...
    // IN : $urlInfos = url informations
    // OUT : (String) url or ''
    
    // href="//www.toto.com/image.png"
    if(preg_match('/^\/\//',$fic)) {
        return $urlInfos['scheme'].':'.$fic;        
        }
        
    if(substr($fic,0,1)=='/') {
        return  $urlInfos['scheme'].'://'.
                $urlInfos['host'].
                $fic;
        }
        
    if(substr($fic,0,1)=='.') { // relative parent ../ or ../../
        return url_to_absolute($parent,$fic);
        }
        
    if(preg_match('/^http\:\/\//',$fic)) {
        return $fic;
        }
        
    if(preg_match('/^https\:\/\//',$fic)) {
        return $fic;
        }        

    if(preg_match('/^javascript\:/',$fic)) {
        return '';
        }
        
    if(preg_match('/^mailto\:/',$fic)) {
        return '';
        }    
        
    if(preg_match('/^tel\:/',$fic)) {
        return '';
        }       
        
    if(preg_match('/^data\:/',$fic)) {
        return '';
        }
        
    if(preg_match('/^irc\:/',$fic)) {
        return '';
        }

    // default
    $r=$urlInfos['scheme'].'://'.
    $urlInfos['host'];
    
    // CASE 1
    // host=www.example.com
    // path=
    // fic=images/headerphoto.jpg
    if(!isset($urlInfos['path'])) {
        $r.='/'.$fic;
        return $r;
        }            
    
    // CAS 2    
    // host=www.example.com
    // path=/index.php, or path=/dir/file.php
    // fic=images/headerphoto.png    
    $last=strrpos($urlInfos['path'], '/'); // looking for last /
    $sub=substr($urlInfos['path'],0,$last); // take from first / to last /
    $r.=$sub.'/'.$fic;
    return $r;
    } // function chemin()
?>
