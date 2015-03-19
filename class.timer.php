<?php
//----------------------------------------------------------------------
//  AUTHOR	: Jean-Francois GAZET
//  WEB 	: http://www.jeffprod.com
//  TWITTER	: @JeffProd
//  MAIL	: jeffgazet at gmail dot com
//  LICENCE : GNU GENERAL PUBLIC LICENSE Version 2, June 1991
//----------------------------------------------------------------------

class MyTimer
	{
	private $_tempsDepart;
	private $_tempsFin;

	public function __construct()
		{
        // Constructeur de l'objet
		date_default_timezone_set('Europe/Paris');
		}
				
	public function start()
		{
		// démarre le chrono
		$this->_tempsDepart=microtime(true);
		}
        
	public function get($format=1)
		{
        // retourne le temps du chrono en nombre ou chaine de texte
        // IN : (float) $format = choix du retour : 1 (float), 2 texte
        // OUT : selon format : 1=(float), 2 (string)
        if($format==1) {return microtime(true)-$this->_tempsDepart;}
        return self::formateDuree(microtime(true)-$this->_tempsDepart);
		}        
		
	public function stop()
		{
		// arrete le chrono et affiche le temps écoulé
		$this->_tempsFin=microtime(true);
		}

	private function formateDuree($temps)
		{
        // Retourne un temps formaté en "00:00:00" d'après un temps en secondes
        // ENTREE : temps en secondes
        // SORTIE : "00h00m00s"
                
        if($temps<1) {return '0s';}
        
		$h=intval($temps/3600);
		$m=intval($temps/60);
		$s=intval($temps-($h*3600)-($m*60));
		
		$r='';
		if($h) {$r.=$h.'h';}
		if($m) {$r.=$m.'m';}
		if($s) {$r.=$s.'s';}
		if($h==0 && $m==0 && $s==0) {$r=$temps;}		
		
		return $r;
		}
	}
?>
