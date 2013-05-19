<?php

 class Postnl {
	
	private $TrackNumber = false;
	private $Postcode = false;
	
	private $BaseUrl = 'https://mijnpakket.postnl.nl';
	private $Cookie = false;
	
	public function __construct($TrackNumber, $Postcode)
	{
		$this->Cookie = tempnam("/tmp", "CURLCOOKIE");
		
		$Data = $this->_doGet('/Claim?barcode='.$TrackNumber.'&postalcode='.$Postcode.'&Foreign=false');
		echo 'From Adress: '.$this->getFromAdress($Data)."<br />\n";
		echo 'To Adress: '.$this->getToAdress($Data)."<br />\n";
		echo 'Deliverydate: '.$this->getDeliveryDate($Data)."<br />\n";
		echo 'Status: '.$this->getStatus($Data)."<br />\n";
		
		//var_dump($Data);
	}
	
	private function getFromAdress($Data)
	{
		$value = preg_match_all('/<div class=\"block\-afzender\ pointer\" (.*?)>(.*?)<\/div>/s',$Data,$estimates);
		$fromHtml = $estimates[2];
		$Adress = trim(strip_tags($fromHtml[0]));
		
		return $Adress;
	}
	
	private function getToAdress($Data)
	{
		$value = preg_match_all('/<div class=\"block-bezorgadres pointer\" (.*?)>(.*?)<\/div>/s',$Data,$estimates);
		$fromHtml = $estimates[2];
		$Adress = trim(strip_tags($fromHtml[0]));
		
		return $Adress;
	}
	
	private function getDeliveryDate($Data)
	{
		$value = preg_match_all('/<div class=\"block-verwacht pointer\" (.*?)>(.*?)<\/div>/s',$Data,$estimates);
		$fromHtml = $estimates[2];
		$Adress = trim(strip_tags($fromHtml[0]));
		
		return $Adress;
	}
	
	private function getStatus($Data)
	{
		$value = preg_match_all('/<div class=\"block-status\">(.*?)<\/div>/s',$Data,$estimates);
		$fromHtml = $estimates[1];
		$value = strpos($fromHtml[0], 'status');
		$Status = substr($fromHtml[0], $value+6, 1);
		
		return $Status;
	}
	 
	private function _doGet($Url)
	{
		$qry_str = "?";
		$ch = curl_init();
		
		// Set query data here with the URL
		if(substr($Url, 0, 4) != 'http')
			curl_setopt($ch, CURLOPT_URL, $this->BaseUrl.$Url); 
		else
			curl_setopt($ch, CURLOPT_URL, $Url . $qry_str); 
		
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.29.13 (KHTML, like Gecko) Version/6.0.4 Safari/536.29.13');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, '3');
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->Cookie);
		
		if($this->_lastPage !== false)
			curl_setopt($ch, CURLOPT_REFERER, $this->_lastPage);
		
		$content = trim(curl_exec($ch));
		$this->_lastPage = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		
		if(trim($content) == '')
			return $this->_doGet($Url);
		else
			return $content;
	}
	
	private function _doPost($Url, $Query)
	{
		$qry_str = '';
		
		foreach($Query AS $Key => $Value)
		{
			$qry_str .= $Key.'='.$Value.'&';
		}
		
		$ch = curl_init();
		
		// Set query data here with the URL
		if(substr($Url, 0, 4) != 'http')
			curl_setopt($ch, CURLOPT_URL, $this->BaseUrl.$Url . $qry_str); 
		else
			curl_setopt($ch, CURLOPT_URL, $Url . $qry_str); 
		
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/536.29.13 (KHTML, like Gecko) Version/6.0.4 Safari/536.29.13');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);
		
		curl_setopt($ch, CURLOPT_TIMEOUT, '3');
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->Cookie);
		
		if($this->_lastPage !== false)
			curl_setopt($ch, CURLOPT_REFERER, $this->_lastPage);
		
		$content = trim(curl_exec($ch));
		$this->_lastPage = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		
		if(trim($content) == '')
			return $this->_doPost($Url, $Query);
		else
			return $content;
	}
 }