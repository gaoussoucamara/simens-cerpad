<?php
namespace Laboratoire\View\Helper;

use Laboratoire\View\Helper\fpdf181\fpdf;
use Secretariat\View\Helper\DateHelper;
use Laboratoire\View\Helper\fpdf181\makefont\TTFParser;

class ImprimerResultatsAnalysesDemandees extends fpdf
{
	
	/**
	 * ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES
	 * ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES
	 */
	protected $B = 0;
	protected $I = 0;
	protected $U = 0;
	protected $HREF = '';
	
	function WriteHTML($html)
	{
		// Parseur HTML
		$html = str_replace("\n",' ',$html);
		$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				// Texte
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
				else
					$this->Write(5,$e);
			}
			else
			{
				// Balise
				if($e[0]=='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					// Extraction des attributs
					$a2 = explode(' ',$e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					}
					$this->OpenTag($tag,$attr);
				}
			}
		}
	}
	
	function OpenTag($tag, $attr)
	{
		// Balise ouvrante
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,true);
		if($tag=='A')
			$this->HREF = $attr['HREF'];
		if($tag=='BR')
			$this->Ln(5);
	}
	
	function CloseTag($tag)
	{
		// Balise fermante
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF = '';
	}
	
	function SetStyle($tag, $enable)
	{
		// Modifie le style et s�lectionne la police correspondante
		$this->$tag += ($enable ? 1 : -1);
		$style = '';
		foreach(array('B', 'I', 'U') as $s)
		{
			if($this->$s>0)
				$style .= $s;
		}
		$this->SetFont('',$style);
	}
	
	function PutLink($URL, $txt)
	{
		// Place un hyperlien
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}
	
	
	/**
	 * Draws text within a box defined by width = w, height = h, and aligns
	 * the text vertically within the box ($valign = M/B/T for middle, bottom, or top)
	 * Also, aligns the text horizontally ($align = L/C/R/J for left, centered, right or justified)
	 * drawTextBox uses drawRows
	 *
	 * This function is provided by TUFaT.com
	 */
	function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=true)
	{
		$xi=$this->GetX();
		$yi=$this->GetY();
	
		$hrow=$this->FontSize;
		$textrows=$this->drawRows($w, $hrow, $strText, 0, $align, 0, 0, 0);
		$maxrows=floor($h/$this->FontSize);
		$rows=min($textrows, $maxrows);
	
		$dy=0;
		if (strtoupper($valign)=='M')
			$dy=($h-$rows*$this->FontSize)/2;
		if (strtoupper($valign)=='B')
			$dy=$h-$rows*$this->FontSize;
	
		$this->SetY($yi+$dy);
		$this->SetX($xi);
	
		$this->drawRows($w, $hrow, $strText, 0, $align, false, $rows, 1);
	
		if ($border)
			$this->Rect($xi, $yi, $w, $h);
	}
	
	function drawRows($w, $h, $txt, $border=0, $align='J', $fill=false, $maxline=0, $prn=0)
	{
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r", '', $txt);
		$nb=strlen($s);
		if($nb>0 && $s[$nb-1]=="\n")
			$nb--;
		$b=0;
		if($border)
		{
			if($border==1)
			{
				$border='LTRB';
				$b='LRT';
				$b2='LR';
			}
			else
			{
				$b2='';
				if(is_int(strpos($border, 'L')))
					$b2.='L';
				if(is_int(strpos($border, 'R')))
					$b2.='R';
				$b=is_int(strpos($border, 'T')) ? $b2.'T' : $b2;
			}
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$ns=0;
		$nl=1;
		while($i<$nb)
		{
			//Get next character
			$c=$s[$i];
			if($c=="\n")
			{
				//Explicit line break
				if($this->ws>0)
				{
					$this->ws=0;
					if ($prn==1) $this->_out('0 Tw');
				}
				if ($prn==1) {
					$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
				}
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border && $nl==2)
					$b=$b2;
				if ( $maxline && $nl > $maxline )
					return substr($s, $i);
				continue;
			}
			if($c==' ')
			{
				$sep=$i;
				$ls=$l;
				$ns++;
			}
			$l+=$cw[$c];
			if($l>$wmax)
			{
				//Automatic line break
				if($sep==-1)
				{
					if($i==$j)
						$i++;
					if($this->ws>0)
					{
						$this->ws=0;
						if ($prn==1) $this->_out('0 Tw');
					}
					if ($prn==1) {
						$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
					}
				}
				else
				{
					if($align=='J')
					{
						$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
						if ($prn==1) $this->_out(sprintf('%.3F Tw', $this->ws*$this->k));
					}
					if ($prn==1){
						$this->Cell($w, $h, substr($s, $j, $sep-$j), $b, 2, $align, $fill);
					}
					$i=$sep+1;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border && $nl==2)
					$b=$b2;
				if ( $maxline && $nl > $maxline )
					return substr($s, $i);
			}
			else
				$i++;
		}
		//Last chunk
		if($this->ws>0)
		{
			$this->ws=0;
			if ($prn==1) $this->_out('0 Tw');
		}
		if($border && is_int(strpos($border, 'B')))
			$b.='B';
		if ($prn==1) {
			$this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
		}
		$this->x=$this->lMargin;
		return $nl;
	}
	
	
	/**
	 * ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES
	 * ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES --- ZONE DE FONCTIONS PREDEFINIES
	 */
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected function nbJours($debut, $fin) {
		//60 secondes X 60 minutes X 24 heures dans une journee
		$nbSecondes = 60*60*24;
	
		$debut_ts = strtotime($debut);
		$fin_ts = strtotime($fin);
		$diff = $fin_ts - $debut_ts;
		return ($diff / $nbSecondes);
	}
	
	public function moisEnLettre($mois){
		$lesMois = array('','Janvier','Fevrier','Mars','Avril',
				'Mais','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre');
		return $lesMois[$mois];
	}
	
	function EnTetePage()
	{
		$convertDate = new DateHelper();
		
		//Bordures -- Bordures -- Bordures
		$this->AddFont('Border','','BorderfontClassicalsPlain.php');
		$this->SetFont('Border','',22);
		
		$this->SetTextColor(191, 194, 191);
		
		$x = $this->GetX(); $y = $this->GetY();
		$this->Text($x-6, $y+2, 'I');
		$this->Text($x+183, $y+2, 'J');
		
		$this->Text($x-6, $y+280, 'K');
		$this->Text($x+183, $y+280, 'L');
		
		//=========================================
		
		$this->SetFont('Times','',10.3);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,4,"R�publique du S�n�gal");
		$this->SetFont('Times','',8.5);
		$this->Cell(0,4,"",0,0,'R');
		$this->SetFont('Times','',10.3);
		$this->Ln(5.4);
		$this->Cell(100,4,"Universit� Gaston Berger de Saint-Louis / UFR-2S");
	
		$this->AddFont('timesbi','','timesbi.php');
		$this->Ln(5.4);
		$this->Cell(100,4,"Centre de Recherche et de Prise en Charge -");
		$this->Ln(5.4);
		$this->SetFont('times','',10.3);
		$this->Cell(86,4,"Ambulatoire de la Dr�panocytose (CERPAD) ",0,0,'L');
		$this->SetFont('Times','',10.3);
		$this->Cell(14,4,'',0,0,'L');
	
		$this->Ln(11);
		$this->SetFont('Times','',15);
		$this->SetTextColor(0,128,0);
		$this->Cell(0,-3,"RESULTATS D'ANALYSES",0,0,'C');
		$this->Ln(1);
		$this->SetFont('Times','',12.3);
		$this->SetFillColor(0,128,0);
		$this->Cell(0,0.3,"",0,1,'C',true);
	
		// EMPLACEMENT DU LOGO
		// EMPLACEMENT DU LOGO
		$baseUrl = $_SERVER['SCRIPT_FILENAME'];
		$tabURI  = explode('public', $baseUrl);
		$this->Image($tabURI[0].'public/images_icons/CERPAD_UGB_LOGO_M.png', 162, 12, 35, 22);
	
		// EMPLACEMENT DES INFORMATIONS SUR LE PATIENT
		// EMPLACEMENT DES INFORMATIONS SUR LE PATIENT
		$infoPatients = $this->getInfosPatients();
		$patient = $this->getPatient();
		$this->SetFont('Times','',10);
		$this->SetTextColor(0,0,0);
		$this->Ln(1);
		
		$this->Cell(30,4,$patient->numero_dossier,0,0,'L',false);
		$this->SetFont('Times','B',9);
		$this->Cell(60,4,"PRENOM & NOM :",0,0,'R',false);
		$this->SetFont('Times','',11);
		if($infoPatients){ $this->Cell(92,4,iconv ('UTF-8' , 'windows-1252', $infoPatients->prenom).' '.iconv ('UTF-8' , 'windows-1252', $infoPatients->nom),0,0,'L'); }
	
		$this->SetFont('Times','B',9);
		$this->SetTextColor(0,0,0);
		$this->Ln(5);
		$this->Cell(90,4,"SEXE :",0,0,'R',false);
		$this->SetFont('Times','',11);
		if($infoPatients){ $this->Cell(92,4,iconv ('UTF-8' , 'windows-1252', $infoPatients->sexe),0,0,'L'); }
	
		//GESTION DES AGES
		//GESTION DES AGES
		$this->SetFont('Times','B',9);
		$this->SetTextColor(0,0,0);
		$this->Ln(5);
		$aujourdhui = (new \DateTime() ) ->format('Y-m-d');
		
		
		$date_naissance = $infoPatients->date_naissance;
		if($date_naissance){ $date_naissance = $convertDate->convertDate($date_naissance); } else {$date_naissance = null; }
		if($date_naissance){
			$this->Cell(90,4,"AGE  :",0,0,'R',false);
			$this->SetFont('Times','',11);
			
			$age = $infoPatients->age;
			if(!$age){
			
				$age_jours = $this->nbJours($infoPatients->date_naissance, $aujourdhui);
				if($age_jours < 31) {
					$age = $age_jours." jours";
				}
				else
				if($age_jours >= 31) {
					$nb_mois = (int)($age_jours/30);
					$nb_jours = $age_jours - ($nb_mois*30);
					$age = $nb_mois."m ".$nb_jours."j";
				}
			
			}else{
			
				$age = $age." ans";
			
			}
			
			$this->Cell(92,4,$age,0,0,'L');
		}else{
			
			$this->Cell(90,4,"AGE :",0,0,'R',false);
			$this->SetFont('Times','',11);
			$this->Cell(92,4,$infoPatients->age.' ans',0,0,'L');
		}

	
		
		$this->SetFont('Times','B',9);
		$this->SetTextColor(0,0,0);
		$this->Ln(5);
		$this->Cell(90,4,"TELEPHONE :",0,0,'R',false);
		$this->SetFont('Times','',11);
		if($infoPatients){ $this->Cell(72,4,$infoPatients->telephone,0,0,'L'); }
	
		$this->Ln(5);
		$this->SetFillColor(0,128,0);
		$this->Cell(0,0.3,"",0,1,'C',true);
	
		$this->Ln(2);
		$this->AddFont('timesi','','timesi.php');
		$this->SetFont('timesi','',8);
		$this->Cell(183,1,"Imprim� le : ".$convertDate->convertDate($aujourdhui),0,1,'R');
	}
	
	function Footer()
	{
		// Positionnement � 1,5 cm du bas
		$this->SetY(-15);
		
		$this->SetFillColor(0,128,0);
		$this->Cell(0,0.3,"",0,1,'C',true);
		$this->SetTextColor(0,0,0);
		$this->SetFont('Times','',9.5);
		$this->Cell(81,5,'T�l�phone: 77 680 69 69 ',0,0,'L',false);
		$this->SetTextColor(128);
		$this->SetFont('Times','I',9);
		$this->Cell(20,8,''.$this->PageNo(),0,0,'C',false);
		$this->SetTextColor(0,0,0);
		$this->SetFont('Times','',9.5);
		$this->Cell(81,5,'SIMENS+: www.simens.sn',0,0,'R',false);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected $infosPatients;
	protected $patient;
	protected $nomService;
	protected $depistage;
	protected $analysesDemandees;
	protected $resultatsAnalysesDemandees; 
	protected $anterioriteNfs;
	protected $resultatsAntiBioGrammePVDemande;
	protected $resultatsAntiBioGrammeECBUDemande;
	protected $commentaireBilan;
	
	public function getNomService()
	{
		return $this->nomService;
	}
	
	public function setNomService($nomService)
	{
		$this->nomService = $nomService;
	}
	
	public function getInfosPatients()
	{
		return $this->infosPatients;
	}
	
	public function setInfosPatients($infosPatients)
	{
		$this->infosPatients = $infosPatients;
	}
	
	public function getPatient()
	{
		return $this->patient;
	}
	
	public function setPatient($patient)
	{
		$this->patient = $patient;
	}
	
	public function getDepistage()
	{
		return $this->depistage;
	}
	
	public function setDepistage($depistage)
	{
		$this->depistage = $depistage;
	}
	
	public function getAnalysesDemandees()
	{
		return $this->analysesDemandees;
	}
	
	public function setAnalysesDemandees($analysesDemandees)
	{
		$this->analysesDemandees = $analysesDemandees;
	}
	
	public function getResultatsAntiBioGrammePVDemande()
	{
	    return $this->resultatsAntiBioGrammePVDemande;
	}
	
	public function setResultatsAntiBioGrammePVDemande($resultatsAntiBioGrammePVDemande)
	{
	    $this->resultatsAntiBioGrammePVDemande = $resultatsAntiBioGrammePVDemande;
	}
	
	public function getResultatsAntiBioGrammeECBUDemande()
	{
		return $this->resultatsAntiBioGrammeECBUDemande;
	}
	
	public function setResultatsAntiBioGrammeECBUDemande($resultatsAntiBioGrammeECBUDemande)
	{
		$this->resultatsAntiBioGrammeECBUDemande = $resultatsAntiBioGrammeECBUDemande;
	}
	
	public function getResultatsAnalysesDemandees()
	{
		return $this->resultatsAnalysesDemandees;
	}
	
	public function setResultatsAnalysesDemandees($resultatsAnalysesDemandees)
	{
		$this->resultatsAnalysesDemandees = $resultatsAnalysesDemandees;
	}
	
	public function getAnterioriteNfs()
	{
		return $this->anterioriteNfs;
	}
	
	public function setAnterioriteNfs($anterioriteNfs)
	{
		$this->anterioriteNfs = $anterioriteNfs;
	}
	
	public function getCommentaireBilan()
	{
	    return $this->commentaireBilan;
	}
	
	public function setCommentaireBilan($commentaireBilan)
	{
	    $this->commentaireBilan = $commentaireBilan;
	}
	
	
	protected $analysesImmunoHemato;
	protected $analysesCytologie;
	protected $analysesHemostase;
	protected $analysesTypageHemoProteine;
	protected $analysesMetabolismeGlucidique;
	protected $analysesBilanLipidique;
	protected $analysesBilanHepatique;
	protected $analysesBilanRenal;
	protected $analysesSerologie;
	protected $analysesMetabolismeFer;
	protected $analysesMetabolismeProtidique;
	protected $analysesBilanElectrolyte;
	protected $analysesTypageHemoglobine;
	protected $analysesSerologieHIV;
	protected $analysesSerologiePV;
	protected $analysesSerologieECBU;
	protected $resultatsCulotPositifECBU;
	protected $listeSouchesIdentif;
	protected $listeParasites;
	

	public function setAnalysesImmunoHemato($analysesImmunoHemato){
		$this->analysesImmunoHemato = $analysesImmunoHemato;
	}
	
	public function getAnalysesImmunoHemato(){
		return $this->analysesImmunoHemato;
	}
	
	public function setAnalysesCytologie($analysesCytologie){
		$this->analysesCytologie = $analysesCytologie;
	}
	
	public function getAnalysesCytologie(){
		return $this->analysesCytologie;
	}
	
	public function setAnalysesHemostase($analysesHemostase){
		$this->analysesHemostase = $analysesHemostase;
	}
	
	public function getAnalysesHemostase(){
		return $this->analysesHemostase;
	}
	
	public function setAnalysesTypageHemoProteine($analysesTypageHemoProteine){
		$this->analysesTypageHemoProteine = $analysesTypageHemoProteine;
	}
	
	public function getAnalysesTypageHemoProteine(){
		return $this->analysesTypageHemoProteine;
	}
	
	public function setAnalysesMetabolismeGlucidique($analysesMetabolismeGlucidique){
		$this->analysesMetabolismeGlucidique = $analysesMetabolismeGlucidique;
	}
	
	public function getAnalysesMetabolismeGlucidique(){
		return $this->analysesMetabolismeGlucidique;
	}
	
	public function setAnalysesBilanLipidique($analysesBilanLipidique){
		$this->analysesBilanLipidique = $analysesBilanLipidique;
	}
	
	public function getAnalysesBilanLipidique(){
		return $this->analysesBilanLipidique;
	}
	
	public function setAnalysesBilanHepatique($analysesBilanHepatique){
		$this->analysesBilanHepatique = $analysesBilanHepatique;
	}
	
	public function getAnalysesBilanHepatique(){
		return $this->analysesBilanHepatique;
	}
	
	public function setAnalysesBilanRenal($analysesBilanRenal){
		$this->analysesBilanRenal = $analysesBilanRenal;
	}
	
	public function getAnalysesBilanRenal(){
		return $this->analysesBilanRenal;
	}
	
	public function setAnalysesSerologie($analysesSerologie){
		$this->analysesSerologie = $analysesSerologie;
	}
	
	public function getAnalysesSerologie(){
		return $this->analysesSerologie;
	}
	
	public function setAnalysesMetabolismeFer($analysesMetabolismeFer){
		$this->analysesMetabolismeFer = $analysesMetabolismeFer;
	}
	
	public function getAnalysesMetabolismeFer(){
		return $this->analysesMetabolismeFer;
	}
	
	public function setAnalysesMetabolismeProtidique($analysesMetabolismeProtidique){
		$this->analysesMetabolismeProtidique = $analysesMetabolismeProtidique;
	}
	
	public function getAnalysesMetabolismeProtidique(){
		return $this->analysesMetabolismeProtidique;
	}
	
	public function setAnalysesBilanElectrolyte($analysesBilanElectrolyte){
		$this->analysesBilanElectrolyte = $analysesBilanElectrolyte;
	}
	
	public function getAnalysesBilanElectrolyte(){
		return $this->analysesBilanElectrolyte;
	}
	
	public function setAnalysesTypageHemoglobine($analysesTypageHemoglobine){
		$this->analysesTypageHemoglobine = $analysesTypageHemoglobine;
	}
	
	public function getAnalysesTypageHemoglobine(){
		return $this->analysesTypageHemoglobine;
	}
	
	public function setAnalysesSerologieHIV($analysesSerologieHIV){
		$this->analysesSerologieHIV = $analysesSerologieHIV;
	}
	
	public function getAnalysesSerologieHIV(){
		return $this->analysesSerologieHIV;
	}
	
	public function setAnalysesSerologiePV($analysesSerologiePV){
	    $this->analysesSerologiePV = $analysesSerologiePV;
	}
	
	public function getAnalysesSerologiePV(){
	    return $this->analysesSerologiePV;
	}
	
	public function setAnalysesSerologieECBU($analysesSerologieECBU){
		$this->analysesSerologieECBU = $analysesSerologieECBU;
	}
	
	public function getAnalysesSerologieECBU(){
		return $this->analysesSerologieECBU;
	}
	
	public function setResultatCulotPositifECBU($resultatCulotPositifECBU){
		$this->resultatsCulotPositifECBU = $resultatCulotPositifECBU;
	}
	
	public function getResultatCulotPositifECBU(){
		return $this->resultatsCulotPositifECBU;
	}
	
	
	public function setListeSouchesIdentif($listeSouchesIdentif){
		$this->listeSouchesIdentif = $listeSouchesIdentif;
	}
	
	public function getListeSouchesIdentif(){
		return $this->listeSouchesIdentif;
	}
	
	public function setListeParasites($listeParasites){
		$this->listeParasites = $listeParasites;
	}
	
	public function getListeParasites(){
		return $this->listeParasites;
	}
	
	//Premiere page NFS --- NFS --- NFS
	//Premiere page NFS --- NFS --- NFS
	function affichageResultatAnalyseNFS()
	{
		$this->AddPage();
		$this->EnTetePage();
		$this->afficherResultatAnalyseNFS();
		
	}
	
	//Page S�rologie HIV
	//Page S�rologie HIV
	function affichageResultatsSerologieHIV()
	{
		$this->AddPage();
		$this->EnTetePage();
		$this->AfficherResultatsSerologieHIV();
	}
	
	//Page S�rologie PV
	//Page S�rologie PV
	function affichageResultatsPV()
	{
	    $this->AddPage();
	    $this->EnTetePage();
	    $this->AfficherResultatsPV();
	}
	
	//Page S�rologie Antibiogramme PV
	//Page S�rologie Antibiogramme PV
	function affichageResultatsAntibiogrammePV($resultatsABGPV)
	{
	    $this->AddPage();
	    $this->EnTetePage();
	    $this->AfficherResultatsAntibiogrammePV($resultatsABGPV);
	}
	
	//Page S�rologie ECBU
	//Page S�rologie ECBU
	function affichageResultatsECBU()
	{
		$this->AddPage();
		$this->EnTetePage();
		$this->AfficherResultatsECBU();
	}
	
	//Page S�rologie Antibiogramme ECBU
	//Page S�rologie Antibiogramme ECBU
	function affichageResultatsAntibiogrammeECBU($resultatsABGPV)
	{
		$this->AddPage();
		$this->EnTetePage();
		$this->AfficherResultatsAntibiogrammeECBU($resultatsABGPV);
	}
	
	//Page Typage h�moglobine (Profil du patient au d�pistage)
	//Page Typage h�moglobine (Profil du patient au d�pistage)
	function affichageResultatsTypageHemoglobine()
	{
		$this->AddPage();
		$this->EnTetePage();
		$this->AfficherResultatsTypageHemoglobine();
	}
	
	//Autres pages des autres analyses demand�es
	//Autres pages des autres analyses demand�es
	function affichageResultatsAnalysesDemandees()
	{
	    $this->AddPage();
	    $this->EnTetePage();
	    $this->AfficherAutreResultatAnalyse();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function getLesAnterioritesNFS(){
		
		$tabInfosAnteriorite = array();
		
		//------- GESTION DES ANTERIORITES -------- GESTION DES ANTERIORITES -------
		//------- GESTION DES ANTERIORITES -------- GESTION DES ANTERIORITES -------
		//------- GESTION DES ANTERIORITES -------- GESTION DES ANTERIORITES -------
		
		$anterioriteNfs = $this->getAnterioriteNfs();
		 
		if($anterioriteNfs){
			$controle = new DateHelper();
			$tabInfosAnteriorite['date_anteriorite'] = $controle->convertDate($anterioriteNfs['demande']['date']);
				 
			//Champ1  --- Champ1  --- Champ1
			$tabInfosAnteriorite['leucocytes'] = $anterioriteNfs['resultat']['champ1'];
			 
			//Champ2  --- Champ2  --- Champ2
			$tabInfosAnteriorite['p_neutrophiles'] = $anterioriteNfs['resultat']['champ2'];
			 
			//Champ3  --- Champ3  --- Champ3
			$tabInfosAnteriorite['p_eosinophiles'] = $anterioriteNfs['resultat']['champ3'];
		
			//Champ4  --- Champ4  --- Champ4
			$tabInfosAnteriorite['p_basophiles'] = $anterioriteNfs['resultat']['champ4'];
			 
			//Champ5  --- Champ5  --- Champ5
			$tabInfosAnteriorite['lymphocytes'] = $anterioriteNfs['resultat']['champ5'];
			 
			//Champ6  --- Champ6  --- Champ6
			$tabInfosAnteriorite['monocytes'] = $anterioriteNfs['resultat']['champ6'];
			 
			//-----------------------------------------------------------
			//-----------------------------------------------------------
			 
			//Champ12  --- Champ12  --- Champ12
			$tabInfosAnteriorite['hematies'] = $anterioriteNfs['resultat']['champ12'];
			 
			//Champ13  --- Champ13  --- Champ13
			$tabInfosAnteriorite['hemoglobines'] = $anterioriteNfs['resultat']['champ13'];
			 
			//Champ14  --- Champ14  --- Champ14
			$tabInfosAnteriorite['hematocrites'] = $anterioriteNfs['resultat']['champ14'];
			 
			//Champ15 --- Champ15 --- Champ15
			$tabInfosAnteriorite['vgm'] = $anterioriteNfs['resultat']['champ15'];
			 
			//Champ16  --- Champ16  --- Champ16
			$tabInfosAnteriorite['tcmh'] = $anterioriteNfs['resultat']['champ16'];
			 
			//Champ17  --- Champ17  --- Champ17
			$tabInfosAnteriorite['ccmh'] = $anterioriteNfs['resultat']['champ17'];
			 
			//Champ18  --- Champ18  --- Champ18
			$tabInfosAnteriorite['idr_cv'] = $anterioriteNfs['resultat']['champ18'];
			 
			//Champ19  --- Champ19  --- Champ19
			$tabInfosAnteriorite['idr_ds'] = $anterioriteNfs['resultat']['champ19'];
			 
			//--------------------------------------------------------
			//--------------------------------------------------------
			 
			//Champ20 --- Champ20 --- Champ20
			$tabInfosAnteriorite['plaquettes'] = $anterioriteNfs['resultat']['champ20'];
			 
			//Champ21 --- Champ21 --- Champ21
			$tabInfosAnteriorite['vmp'] = $anterioriteNfs['resultat']['champ21'];
			 
			//Champ22 --- Champ22 --- Champ22
			$tabInfosAnteriorite['idp'] = $anterioriteNfs['resultat']['champ22'];
			 
			//Champ23 --- Champ23 --- Champ23
			$tabInfosAnteriorite['pct'] = $anterioriteNfs['resultat']['champ23'];
			 
			//Champ24 --- Champ24 --- Champ24
			$tabInfosAnteriorite['reticulocytes'] = $anterioriteNfs['resultat']['champ24'];
			 
			 return $tabInfosAnteriorite;
		}else{
			
			return null;
		}
		 
		//GESTION DES RESULTATS DE L'ANALYSE
		//GESTION DES RESULTATS DE L'ANALYSE
		//GESTION DES RESULTATS DE L'ANALYSE
	}
	
	function afficherResultatAnalyseNFS()
	{
		$controle = new DateHelper();
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
		$this->AddFont('bordure','','borderpi1515-9.php');
	
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
		$datePrelevement = "";
		$idanalyseAff = "";
		
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];

	 	    if($idanalyse == 1){
	 	    	
	 	    	$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
	 	    	$idAnalyses[$idanalyse]          = $idanalyse;
	 	    	$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
	 	    	$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
	 	    	$datePrelevement = $infosAnalyseDemande[1]['DateHeurePrelevement'];
	 	    	$idanalyseAff = $idanalyse;
	 	    	
	 	    }else if($idanalyse == 71){
	 	    	
	 	    	$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
	 	    	$idAnalyses[$idanalyse]          = $idanalyse;
	 	    	$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
	 	    	$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
	 	    	$datePrelevement = $infosAnalyseDemande[71]['DateHeurePrelevement'];
	 	    	$idanalyseAff = $idanalyse;
	 	    	
	 	    }
		}
		
		
		//Affichage des infos sur le biologiste et le technicien
		//$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[1]['DateEnregistrementResultat']);
		//$prenomNomTechnicien = $infosAnalyseDemande[1]['Prenom'].' '.$infosAnalyseDemande[1]['Nom'];
		//$prenomNomBiologiste = $infosAnalyseDemande[1]['PrenomValidateur'].' '.$infosAnalyseDemande[1]['NomValidateur'];
		
		$this->SetFont('times','',8);
		//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
		//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
		$this->Cell(90,-1,'','',1,'L',0);
		$this->Ln(5);
		
		//AFFICHAGE DE L'EN TETE DU TEXTE
		//AFFICHAGE DE L'EN TETE DU TEXTE
		$this->SetFillColor(249,249,249);
		$this->SetDrawColor(220,220,220);
	
		$this->SetFont('times','I',9);
		$this->Cell(35,7,'H�matologie','',0,'L',0);
		$this->SetFont('times','U',10);
		if($idanalyseAff == 1){
		    $this->SetTextColor(123, 125, 125);
		     
		    $this->SetFont('bordure','',12);
		    $x = 87.5; 
		    $y = 68.3;
		    
		    $this->Text($x-1, $y+3, 'GKKKKKKK');
		    $this->Text($x+32, $y+3, 'H');
		    $this->Text($x-1, $y+7, 'JMMMMMMM');
		    $this->Text($x+32, $y+7, 'I');
		    
		    $this->SetTextColor(0,0,0);
		    
		    $this->SetFont('times','',11);
			$this->Cell(115,6,'HEMOGRAMME','',0,'C',0);
		}else 
			if($idanalyseAff == 71){
			    $this->SetTextColor(123, 125, 125);
		    
			    $this->SetFont('bordure','',12);
			    $x = 87.5;
			    $y = 68.3;
			    
			    $this->Text($x-27, $y+3, 'GKKKKKKKKKKKKKKKKKKK');
			    $this->Text($x+57.5, $y+3, 'H');
			    $this->Text($x-27, $y+7, 'JMMMMMMMMMMMMMMMMMMM');
			    $this->Text($x+57.5, $y+7, 'I');
			    
			    $this->SetTextColor(0,0,0);
			    
			    $this->SetFont('times','',11);
			    
				$this->Cell(115,6,'HEMOGRAMME & TAUX DE RETICULOCYTES','',0,'C',0);
			}

		$this->Cell(35,7,'','',1,'C',0);
		
		$this->Ln(3);
		
		//mat�riel utilis� --- mat�riel utilis� --- mat�riel utilis�
		$this->SetFont('zap','',11.3);
		$this->Cell(4,6,' ^','BT',0,'C',1);
		$this->SetFont('times','',11);
		$this->Cell(181,6,'Type de mat�riel utilis� : '.$resultats[1]['type_materiel'],'BT',1,'L',1);
		
		$this->Ln(4);
		
		/**
		 * ===================================================================
		 */
		//EN TETE DES DU TABLEAU DES INFORMATIONS A AFFICHER SUR LES RESULTATS
		//EN TETE DES DU TABLEAU DES INFORMATIONS A AFFICHER SUR LES RESULTATS
		/**
		 * ===================================================================
		 */
		
		$infoAnteriorite = $this->getLesAnterioritesNFS();
		if($infoAnteriorite){ $dateAnteriorite = $infoAnteriorite['date_anteriorite']; }else{ $dateAnteriorite = "N�ant"; }
		
		$this->SetFont('times','B',8);
		$this->Cell(5,6,'','',0,'L',0);
		$this->Cell(35,6,'LIBELLE','',0,'L',0);
		$this->Cell(55,6,'VALEUR ACTUELLE','L',0,'L',0);
		$this->Cell(50,6,'VALEUR DE REFERENCE','L',0,'L',0);
		$this->Cell(22,6,'ANTERIORITE','L',0,'L',0);
		$this->SetFont('times','I',9);
		$this->Cell(18,6,'('.$dateAnteriorite.')','',1,'L',0);
		
		/**
		 * ===================================================================
		 */
		// FIN EN TETE DES DU TABLEAU DES INFORMATIONS A AFFICHER SUR LES RESULTATS
		// FIN EN TETE DES DU TABLEAU DES INFORMATIONS A AFFICHER SUR LES RESULTATS
		/**
		 * ===================================================================
		 */
		
		//GESTION DES RESULTATS DE L'ANALYSE
		//GESTION DES RESULTATS DE L'ANALYSE
		//GESTION DES RESULTATS DE L'ANALYSE
		/**
		 * CHAMP 1 -- Leucocytes ------ CHAMP 1 -- Leucocytes ------ CHAMP 1
		 */
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->SetFont('zap','',10.5);
		$this->Cell(5,6,'+','',0,'L',1);
		
		$this->SetFont('times','B',10.5);
		$this->Cell(35,6,'Leucocytes','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ1'],0,',', ' '),'L',0,'R',1);
		$this->Cell(8.3,6,'/mm','L',0,'L',1);
		$this->SetFont('times','B',15.5);
		$this->Cell(6.7,6,'�','L',0,'L',1); 
		$this->SetFont('times','B',10.5);
		$this->Cell(10,6,'','L',0,'R',1);
		$this->Cell(10,6,'','L',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(50,6,'(4 000 - 10 000)','L',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){ 
			$this->Cell(24.2,6,number_format($infoAnteriorite['leucocytes'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','L',0,'L',1); //$this->Cell(8,6,'/mm','L',0,'L',1);
			$this->SetFont('times','B',15.5);
			$this->Cell(7,6,'','L',1,'L',1); //$this->Cell(7,6,'�','L',1,'L',1);
		}else{ 
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 2 -- Polynucl�aires neutrophiles ------ CHAMP 2 -- Polynucl�aires neutrophiles ------ CHAMP 2
		 */
			  
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'P. Neutrophiles','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ2'],0,',', ' '),'',0,'R',1);
		$this->Cell(8.3,6,'/mm','',0,'L',1);
		$this->SetFont('times','',15.5);
		$this->Cell(6.7,6,'�','',0,'L',1);
		$this->SetFont('times','',10.5);
		$this->Cell(10,6,number_format($resultats[1]['champ7'],1,',', ' '),'',0,'R',1);
		$this->Cell(10,6,'%','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(33,6,'(2 000 - 7 000)','',0,'L',1);
		$this->Cell(17,6,'(45 - 70)','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['p_neutrophiles'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 3 -- Polynucl�aire �osinophiles ------ CHAMP 3 -- Polynucl�aires �osinophiles ------ CHAMP 3
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'P. Eosinophiles','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ3'],0,',', ' '),'',0,'R',1);
		$this->Cell(8.3,6,'/mm','',0,'L',1);
		$this->SetFont('times','',15.5);
		$this->Cell(6.7,6,'�','',0,'L',1);
		$this->SetFont('times','',10.5);
		$this->Cell(10,6,number_format($resultats[1]['champ8'],1,',', ' '),'',0,'R',1);
		$this->Cell(10,6,'%','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(33,6,'(20 - 500)','',0,'L',1);
		$this->Cell(17,6,'(0 - 5)','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['p_eosinophiles'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 4 -- Polynucl�aires basophiles ------ CHAMP 4 -- Polynucl�aire basophiles ------ CHAMP 4
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'P. Basophiles','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ4'],0,',', ' '),'',0,'R',1);
		$this->Cell(8.3,6,'/mm','',0,'L',1);
		$this->SetFont('times','',15.5);
		$this->Cell(6.7,6,'�','',0,'L',1);
		$this->SetFont('times','',10.5);
		$this->Cell(10,6,number_format($resultats[1]['champ9'],1,',', ' '),'',0,'R',1);
		$this->Cell(10,6,'%','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(33,6,'(0 - 100)','',0,'L',1);
		$this->Cell(17,6,'(0 - 3)','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['p_basophiles'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 5 -- Lymphocytes ------ CHAMP 5 -- Lymphocytes ------ CHAMP 5
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'Lymphocytes','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ5'],0,',', ' '),'',0,'R',1);
		$this->Cell(8.3,6,'/mm','',0,'L',1);
		$this->SetFont('times','',15.5);
		$this->Cell(6.7,6,'�','',0,'L',1);
		$this->SetFont('times','',10.5);
		$this->Cell(10,6,number_format($resultats[1]['champ10'],1,',', ' '),'',0,'R',1);
		$this->Cell(10,6,'%','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(33,6,'(800 - 4 000)','',0,'L',1);
		$this->Cell(17,6,'(20 - 40)','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['lymphocytes'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		
		/**
		 * CHAMP 6 -- Monocytes ------ CHAMP 6 -- Monocytes ------ CHAMP 6
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'Monocytes','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ6'],0,',', ' '),'',0,'R',1);
		$this->Cell(8.3,6,'','',0,'L',1);
		$this->SetFont('times','',15.5);
		$this->Cell(6.7,6,'','',0,'L',1);
		$this->SetFont('times','',10.5);
		$this->Cell(10,6,number_format($resultats[1]['champ11'],1,',', ' '),'',0,'R',1);
		$this->Cell(10,6,'%','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->Cell(33,6,'(120 - 1 200)','',0,'L',1);
		$this->Cell(17,6,'(3 - 15)','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['monocytes'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		
		/**
		 * CHAMP 12 -- Hematies ------ CHAMP 12 -- Hematies ------ CHAMP 12
		 * ON DESCEND *** ON DESCEND *** ON DESCEND
		 */
		
		$this->Ln(6);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->SetFont('zap','',10.5);
		$this->Cell(5,6,'+','',0,'L',1);
		
		$this->SetFont('times','B',10.5);
		$this->Cell(35,6,'H�maties','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ12'],2,',', ' '),'',0,'R',1);
		
		$this->Cell(5.3,6,'10','',0,'L',1);
		$this->SetFont('times','B',9.5);
		$this->Cell(2.7,6,'','',0,'T',1);
		$x = $this->GetX(); $y = $this->GetY();
		$this->Text($x-2.5, $y+3, '6');
		
		$this->SetFont('times','B',10.5);
		$this->Cell(8,6,'/mm','',0,'L',1);
		$this->SetFont('times','B',15.5);
		$this->Cell(3,6,'�','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','B',10.5);
		$this->Cell(33,6,'(3,5 - 5,0)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['hematies'],2,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','B',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 13 -- H�moglobine  ------ CHAMP 13 -- H�moglobine  ------ CHAMP 13
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','B',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'H�moglobine','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ13'],1,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'g/dl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','B',10.5);
		$this->Cell(33,6,'(11 - 15)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['hemoglobines'],1,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','B',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 14 -- H�matocrite ------ CHAMP 14 -- H�matocrite ------ CHAMP 14
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'H�matocrite','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ14'],1,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'%','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(37 - 47)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['hematocrites'],1,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 15 -- V.G.M ------ CHAMP 15 -- V.G.M ------ CHAMP 15
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'V.G.M','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ15'],0,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'fl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(80 - 100)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['vgm'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 16 -- T.C.M.H ------ CHAMP 16 -- T.C.M.H ------ CHAMP 16
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'T.C.M.H','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ16'],0,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'pg','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(27 - 34)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['tcmh'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 17 -- C.C.M.H ------ CHAMP 17 -- C.C.M.H ------ CHAMP 17
		 */
		

		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'C.C.M.H','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ17'],0,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'g/dl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(32 - 36)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['ccmh'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 18 -- IDR-CV ------ CHAMP 18 -- IDR-CV ------ CHAMP 18
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'IDR-CV','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ18'],1,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'%','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(11 - 16)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['idr_cv'],1,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 19 -- IDR-DS ------ CHAMP 19 -- IDR-DS ------ CHAMP 19
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'IDR-DS','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ19'],0,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'fl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(35 - 56)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['idr_ds'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		
		/**
		 * CHAMP 20 -- Plaquettes ------ CHAMP 20 -- Plaquettes ------ CHAMP 20
		 * ON DESCEND *** ON DESCEND *** ON DESCEND
		 */
		
		$this->Ln(6);
		
	    //Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->SetFont('zap','',10.5);
		$this->Cell(5,6,'+','',0,'L',1);
		
		$this->SetFont('times','B',10.5);
		$this->Cell(35,6,'Plaquettes','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ20'],0,',', ' '),'',0,'R',1);
		
		$this->Cell(5,6,'10','',0,'L',1);
		$this->SetFont('times','B',15.5);
		$this->Cell(3,6,'�','',0,'L',1);
		//$x = $this->GetX(); $y = $this->GetY();
		//$this->Text($x-2.5, $y+3, '');
		
		$this->SetFont('times','B',10.5);
		$this->Cell(8,6,'/mm','',0,'L',1);
		$this->SetFont('times','B',15.5);
		$this->Cell(3,6,'�','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','B',10.5);
		$this->Cell(33,6,'(150 - 450)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['plaquettes'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','B',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 21 -- VMP ------ CHAMP 21 -- VMP ------ CHAMP 21
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'VMP','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ21'],1,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'fl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(6,5 - 12,0)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['vmp'],1,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 22 -- IDP ------ CHAMP 22 -- IDP ------ CHAMP 22
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(225,225,225);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'IDP','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ22'],0,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'fl','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(9,0 - 17,0)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['idp'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		

		/**
		 * CHAMP 23 -- PCT ------ CHAMP 23 -- PCT ------ CHAMP 23
		 */
		
		$this->Ln(0.5);
		
		//Changer la couleur de la ligne
		$this->SetFillColor(246,246,246);
		$this->SetFont('times','',10.5);
		
		/*1) Premi�re colonne ==== Pour les libell�s*/
		$this->Cell(5,6,'','',0,'L',1);
		$this->Cell(35,6,'PCT','',0,'L',1);
		
		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
		$this->Cell(20,6,number_format($resultats[1]['champ23'],3,',', ' '),'',0,'R',1);
		$this->Cell(19,6,'%','',0,'L',1);
		
		$this->SetFont('times','',10.5);
		$this->Cell(16,6,'','',0,'L',1);
		
		/*3) Troisi�me colonne ===== pour les r�f�rences */
		$this->SetFont('times','',10.5);
		$this->Cell(33,6,'(0,108 - 0,282)','',0,'L',1);
		$this->Cell(17,6,'','',0,'L',1);
		
		/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
		$this->Cell(0.8,6,'','L',0,'R',0);
		if($infoAnteriorite){
			$this->Cell(24.2,6,number_format($infoAnteriorite['pct'],3,',', ' '),'L',0,'R',1);
			$this->Cell(8,6,'','',0,'L',1);
			$this->SetFont('times','',15.5);
			$this->Cell(7,6,'','',1,'L',1);
		}else{
			$this->Cell(39.2,6,'','L',1,'L',1);
		}
		
		
		/**
		 * CHAMP 24 & 25 -- Taux de r�ticulocytes ------ CHAMP 24 & 25 -- Taux de r�ticulocytes ------
		 */
		
		if($resultats[1]['champ24']){
			
			$this->ln(6);
			
			//Changer la couleur de la ligne
			$this->SetFillColor(225,225,225);
			$this->SetFont('times','B',10.5);
			
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->Cell(5,6,'','',0,'L',1);
			$this->Cell(35,6,'R�ticulocytes','',0,'L',1);
			
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$this->Cell(20,6,number_format($resultats[1]['champ24'],0,',', ' '),'L',0,'R',1);
			$this->Cell(8.3,6,'/mm','L',0,'L',1);
			$this->SetFont('times','B',15.5);
			$this->Cell(6.7,6,'�','L',0,'L',1);
			$this->SetFont('times','B',10.5);
			
			$valeurReticulo = $resultats[1]['champ25'];
			if(fmod($valeurReticulo, 1) !== 0.00){$valeurReticulo = number_format($valeurReticulo, 1, ',', ' ');}
			$this->Cell(10,6,$valeurReticulo,'L',0,'R',1);
			$this->Cell(10,6,'%','L',0,'L',1);
			
			/*3) Troisi�me colonne ===== pour les r�f�rences */
			$this->Cell(33,6,'(25 000 - 80 000)','L',0,'L',1);
			$this->Cell(17,6,'(0,5 - 1,5)','',0,'L',1);
			
			/*4) Quatri�me colonne ===== pour les ant�riorit�s*/
			$this->Cell(0.8,6,'','L',0,'R',0);
			if($infoAnteriorite){
				$this->Cell(24.2,6,number_format($infoAnteriorite['reticulocytes'],0,',', ' '),'L',0,'R',1);
				$this->Cell(8,6,'','',0,'L',1);
				$this->SetFont('times','B',15.5);
				$this->Cell(7,6,'','',1,'L',1);
			}else{
				$this->Cell(39.2,6,'','L',1,'L',1);
			}
		}

		
		/**
		 * ------ COMMENTAIRE ------ COMMENTAIRE ------ COMMENTAIRE -------
		 */
		$this->ln(3);
		
		if(str_replace(' ','', $resultats[1]['commentaire'])){
			$this->SetFont('times','U',10.5);
			$this->Cell(185,6,'Commentaire :','',1,'L',0);
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('times','',10.5);
			$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[1]['commentaire']),0,'J',1);
		}
		
		$this->Ln();
		$this->SetFont('timesi','U',9);
		$this->Cell(140,6,'','',0,'',0);
		$this->Cell(45,6,'Cachet et signature','',1,'C',0);
		
	}
	
	
	
	function AfficherAutreResultatAnalyse()
	{
		$controle = new DateHelper();
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
	
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
		$tabIdanalyse = array();
		
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
			$tabIdanalyse [] = $idanalyse;
			$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
		}
		
		//Date de prel�vement
		$datePrelevement = $infosAnalyseDemande[$tabIdanalyse[0]]['DateHeurePrelevement'];
		
		$this->SetFont('times','',8);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
		
		$this->Cell(90,-1,'','',1,'L',0);
		
		
		/**
		 * GESTION DES RESULTATS DANS 
		 * --- IMMUNO-HEMATOLOGIE --- IMMUNO-HEMATOLOGIE --- IMMUNO-HEMATOLOGIE
		 */
		$idAnalysesImmunoHemato = $this->getAnalysesImmunoHemato();
		if($idAnalysesImmunoHemato){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
			
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'IMMUNO-HEMATOLOGIE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
			
			//GSRH - GROUPAGE RESHUS ---- GSRH - GROUPAGE RESHUS
			if(in_array(2, $idAnalysesImmunoHemato)){
				
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
				
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'GSRH / GROUPAGE RESHUS : ','BT',0,'L',1);
				
				$this->SetFont('times','I',10);
				$this->Cell(15,6,'Groupe : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(25,6,' '.$resultats[2]['groupe'],'BT',0,'L',1);
				
				$this->SetFont('times','I',10);
				$this->Cell(15,6,'R�shus : ','BT',0,'L',1);
				
				$RhesusType = 'Positif';
				if($resultats[2]['rhesus'] == 'Rh-'){ $RhesusType = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(65,6,' '.$RhesusType,'BT',1,'L',1);
				
			}
			
			
			//RECHERCHE DE L'ANTIGENE D FAIBLE --- RECHERCHE DE L'ANTIGENE D FAIBLE
			if(in_array(3, $idAnalysesImmunoHemato)){

				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
				
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'RECHERCHE ANTIGENE D FAIBLE : ','BT',0,'L',1);
				
				$rechAntDFaible = $resultats[3]['antigene_d_faible'];
				if($rechAntDFaible == 'Negatif'){ $rechAntDFaible = 'N�gatif'; }
				
				$this->SetFont('times','B',11.5);
				$this->Cell(25,6,$rechAntDFaible,'BT',0,'L',1);
				
				if($resultats[3]['conclusion_antigene_d_faible']){
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'Conclusion : ','BT',0,'L',1);
					$this->SetFont('times','B',10);
					$this->Cell(75,6,iconv ('UTF-8' , 'windows-1252', $resultats[3]['conclusion_antigene_d_faible']),'BT',1,'L',1);
				}else{
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'','BT',0,'L',1);
					$this->SetFont('times','B',11);
					$this->Cell(75,6,'','BT',1,'L',1);
				}
				
			}
			
			
			//TEST DE COOMBS DIRECT --- TEST DE COOMBS DIRECT --- TEST DE COOMBS DIRECT
			if(in_array(4, $idAnalysesImmunoHemato)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'TEST DE COOMBS DIRECT : ','BT',0,'L',1);
			
				$testCoombsDirect = $resultats[4]['valeur'];
				if($testCoombsDirect == 'Negatif'){ $testCoombsDirect = 'N�gatif'; }
				
				$this->SetFont('times','B',11.5);
				$this->Cell(25,6,$testCoombsDirect,'BT',0,'L',1);
				
				if($testCoombsDirect == 'Positif'){
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'Titre : ','BT',0,'R',1);
					$this->SetFont('times','B',10);
					$this->Cell(75,6,iconv ('UTF-8' , 'windows-1252', $resultats[4]['titre']),'BT',1,'L',1);
				}else{
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'','BT',0,'L',1);
					$this->SetFont('times','B',11);
					$this->Cell(75,6,'','BT',1,'L',1);
				}
					
			}
			
			
			//TEST DE COOMBS INDIRECT --- TEST DE COOMBS INDIRECT --- TEST DE COOMBS INDIRECT
			if(in_array(5, $idAnalysesImmunoHemato)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(55,6,'TEST DE COOMBS INDIRECT (RAI) : ','BT',0,'L',1);

				$nbLignes = count($resultats[5]);
				
				if($nbLignes > 0){
						
					for($i = 0 ; $i < $nbLignes ; $i++){
							
						$valeur = $resultats[5][$i]['valeur'];
						$titre  = $resultats[5][$i]['titre'];
						$temperature = $resultats[5][$i]['temperature'];
							
						if($i == 0){
							$this->SetFont('times','',11);
							$this->Cell(15,6,' � ','T',0,'R',1);
								
							if($valeur == 'Negatif'){ $valeur = 'N�gatif';}
							$this->SetFont('timesb','',11);
							$this->Cell(20,6,$valeur,'T',0,'L',1);
							
							if($valeur == 'N�gatif'){
								$this->SetFont('timesi','',9);
								$this->Cell(35,6,'','T',0,'R',1);
							}else{
								$this->SetFont('timesi','',9);
								$this->Cell(15,6,' Titre :','T',0,'R',1);
								
								$this->SetFont('timesb','',11);
								$this->Cell(20,6,$titre,'T',0,'L',1);
							}
							
							$this->SetFont('timesi','',9);
							$this->Cell(30,6,' Temp�rature :','T',0,'R',1);
							
							$this->SetFont('timesb','',11);
							$this->Cell(12,6,$temperature.' �C','T',0,'R',1);
							
							$this->Cell(13,6,'','T',1,'L',1);
							
						}else{
							$this->Cell(60,6,"",'',0,'L',1);
								
							$this->SetFont('times','',11);
							$this->Cell(15,6,' � ','T',0,'R',1);
								
							if($valeur == 'Negatif'){ $valeur = 'N�gatif';}
							$this->SetFont('timesb','',11);
							$this->Cell(20,6,$valeur,'T',0,'L',1);
							
							if($valeur == 'N�gatif'){
								$this->SetFont('timesi','',9);
								$this->Cell(35,6,'','T',0,'R',1);
							}else{
								$this->SetFont('timesi','',9);
								$this->Cell(15,6,' Titre :','T',0,'R',1);
								
								$this->SetFont('timesb','',11);
								$this->Cell(20,6,$titre,'T',0,'L',1);
							}
							
							$this->SetFont('timesi','',9);
							$this->Cell(30,6,' Temp�rature :','T',0,'R',1);
							
							$this->SetFont('timesb','',11);
							$this->Cell(12,6,$temperature.' �C','T',0,'R',1);
							
							$this->Cell(13,6,'','T',1,'L',1);
							
						}
				
							
					}
						
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(130,6,'','T',1,'L',1);
						
				}
				
				
				/** Conclusion --- Conclusion --- Conclusion**/
				/** Conclusion --- Conclusion --- Conclusion**/
				if(!$resultats[5][0]['commentaire']){
					$this->Cell(185,6,'','B',0,'L',1);
				}else {
					$this->Cell(15,6,'','B',0,'L',1);
					
					$this->SetFont('timesi','U',11);
					$this->Cell(25,6,'Commentaire :','B',0,'R',1);
					
					$this->SetFont('timesb','',11);
					$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $resultats[5][0]['commentaire']),'B','J',1);
					
				}
					
			}
			
			
			//TEST DE COMPATIBILITE --- TEST DE COMPATIBILITE --- TEST DE COMPATIBILITE
			if(in_array(6, $idAnalysesImmunoHemato)){

				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'TEST DE COMPATIBILITE : ','BT',0,'L',1);
					
				$testCompatibilite = $resultats[6]['valeur'];
				
				$this->SetFont('times','B',11.5);
				$this->Cell(25,6,$testCompatibilite,'BT',0,'L',1);
					
				if($testCompatibilite == 'Compatible'){
					$this->SetFont('times','I',10);
					$this->Cell(33,6,'Poche num�ro : ','BT',0,'R',1);
					$this->SetFont('times','B',10);
					$this->Cell(62,6,iconv ('UTF-8' , 'windows-1252', $resultats[6]['poche']),'BT',1,'L',1);
				}else{
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'','BT',0,'L',1);
					$this->SetFont('times','B',11);
					$this->Cell(75,6,'','BT',1,'L',1);
				}
					
					
			}
			
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- CYTOLOGIE --- CYTOLOGIE --- CYTOLOGIE --- CYTOLOGIE --- CYTOLOGIE
		 */
		$idAnalysesCytologie = $this->getAnalysesCytologie();
		if($idAnalysesCytologie){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
				
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'CYTOLOGIE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
				
			//VITESSE DE SEDIMENTATION (VS) ---- VITESSE DE SEDIMENTATION (VS)
			if(in_array(7, $idAnalysesCytologie)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'VITESSE DE SEDIMENTATION (VS) : ','BT',0,'L',1);
		
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'1�re heure : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(22,6,$resultats[7]['valeur1'].'  mm','BT',0,'L',1);
		
				$this->SetFont('times','I',9);
				$this->Cell(20,6,'2�me heure : ','BT',0,'R',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(30,6,$resultats[7]['valeur2'].'  mm','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H<15 | H>20 ; +60ans < 30 )','BT',1,'R',1);
		
			}
			
			//TEST D'EMMEL (TE)  --- TEST D'EMMEL (TE)  --- TEST D'EMMEL (TE) 
			if(in_array(8, $idAnalysesCytologie)){

				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
				
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,"TEST D'EMMEL (TE) : ",'BT',0,'L',1);
				
				$testDemmel = $resultats[8]['valeur'];
				if($testDemmel == 'Negatif'){ $testDemmel = 'N�gatif'; }
					
				$this->SetFont('times','B',11);
				$this->Cell(40,6,$testDemmel,'BT',0,'L',1);
				
				$this->SetFont('times','I',9);
				$this->Cell(80,6,'','BT',1,'R',1);
				
			}
			
			//HLM (COMPTE D'ADDIS)  --- HLM (COMPTE D'ADDIS) --- HLM (COMPTE D'ADDIS)
			if(in_array(50, $idAnalysesCytologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(50,6,"HLM (COMPTE D'ADDIS) : ",'T',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'H�maties :','T',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[50]['hematies_hlm'], 0, ',', ' ').'  /min','T',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(20,6,'( < 2 000 )','T',0,'L',1);
					
				
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'Leucocytes :','T',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,6,number_format($resultats[50]['leucocytes_hlm'], 0, ',', ' ').'  /min','T',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(19,6,'( < 2 000 )','T',1,'L',1);
				
				/** Conclusion --- Conclusion --- Conclusion**/
				/** Conclusion --- Conclusion --- Conclusion**/
				$commentaireHLM = $resultats[50]['commentaire_hlm_compte_daddis'];
				if($commentaireHLM){
					$this->Cell(15,6,'','B',0,'L',1);
					
					$this->SetFont('timesbi','U',10);
					$this->Cell(25,6,'Commentaire :','B',0,'R',1);
					
					$this->SetFont('times','B',11);
					$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $commentaireHLM),'B','J',1);
						
				}
					
			}

			
			//CULOT URINAIRE --- CULOT URINAIRE --- CULOT URINAIRE --- CULOT URINAIRE
			if(in_array(58, $idAnalysesCytologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(50,6,"CULOT URINAIRE : ",'T',0,'L',1);
					
				
				$listeElementsA  = array(1=>'Leucocytes', 2=>'H�maties', 3=>'Cristaux', 4=>'Oeufs', 5=>'Parasites');
				$listeSousElemA3 = array(1=>'Oxalate de potassium | calcium', 2=>'Phosphate', 3=>'Cystine', 4=>'Acide Urique');
				$listeSousElemA4 = array(1=>'Schistoma hematobium');
				$listeSousElemA5 = array(1=>'Trichomonas vaginale', 2=>'Schistosoma hematobium');
					
				$nbElements = count($resultats[58]);
				
				if($nbElements > 0){
					
					for($i = 0 ; $i < $nbElements ; $i++){
					
						$valeur1 = $resultats[58][$i]['culot_urinaire_1'];
					
						if($i == 0){
							$this->SetFont('times','',11);
							$this->Cell(25,6,' � '.$listeElementsA[$valeur1].' ','T',0,'L',1);
							
							$valeur2Aff = "";
							
							if($valeur1 == 1){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = iconv ('UTF-8' , 'windows-1252', $valeur2);
							}else if($valeur1 == 2){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = iconv ('UTF-8' , 'windows-1252', $valeur2);
							}else if($valeur1 == 3){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA3[$valeur2];
							}else if($valeur1 == 4){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA4[$valeur2];
							}else if($valeur1 == 5){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA5[$valeur2];
							}
								
							if($valeur2Aff){
								$this->Cell(25,6,' ---------------- ','T',0,'L',1);
							}else{
								$this->Cell(25,6,' ','T',0,'L',1);
							}
							
							$this->SetFont('times','',11);
							$this->Cell(80,6,$valeur2Aff ,'T',1,'L',1);
							
						}else{
							$this->Cell(55,6,"",'',0,'L',1);
							
							$this->SetFont('times','',11);
							$this->Cell(25,6,' � '.$listeElementsA[$valeur1].' ','',0,'L',1);
							
							$valeur2Aff = "";
							
							if($valeur1 == 1){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = iconv ('UTF-8' , 'windows-1252', $valeur2);
							}else if($valeur1 == 2){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = iconv ('UTF-8' , 'windows-1252', $valeur2);
							}else if($valeur1 == 3){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA3[$valeur2];
							}else if($valeur1 == 4){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA4[$valeur2];
							}else if($valeur1 == 5){
								$valeur2 = $resultats[58][$i]['culot_urinaire_2'];
								$valeur2Aff = $listeSousElemA5[$valeur2];
							}
								
							if($valeur2Aff){
								$this->Cell(25,6,' ---------------- ','',0,'L',1);
							}else{
								$this->Cell(25,6,' ','',0,'L',1);
							}
							
							$this->SetFont('times','',11);
							$this->Cell(80,6,$valeur2Aff ,'',1,'L',1);
						}
						
					
					}
					
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(130,6,'','T',1,'L',1);
					
				}
				
				
				/** Conclusion --- Conclusion --- Conclusion**/
				/** Conclusion --- Conclusion --- Conclusion**/
				$this->Cell(15,6,'','B',0,'L',1);
				
				$this->SetFont('timesbi','U',10);
				$this->Cell(25,6,'Conclusion :','B',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $resultats[58][0]['conclusion']),'B','J',1);
					
				
				
					
			}
			
			
		}
		

		/**
		 * GESTION DES RESULTATS DANS
		 * --- HEMOSTASE --- HEMOSTASE --- HEMOSTASE --- HEMOSTASE --- HEMOSTASE
		 */
		
		$idAnalysesHemostase = $this->getAnalysesHemostase();
		if($idAnalysesHemostase){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'HEMOSTASE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//TP-INR  ---  TP-INR ---  TP-INR  ---  TP-INR --- 
			if(in_array(14, $idAnalysesHemostase)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(22,6,'TP - INR : ','T',0,'L',1);
		
				//Premi�re ligne --- Premi�re ligne --- Premi�re ligne
				$this->SetFont('times','I',8);
				$this->Cell(35,6,'Temps quick temoin : ','T',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(17,6,$resultats[14]['temps_quick_temoin'].'  s','T',0,'L',1);
		
				$this->SetFont('times','I',8);
				$this->Cell(20,6,'( 11 - 13 )','T',0,'R',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(13,6,'','T',0,'L',1);
		
				$this->SetFont('times','I',8);
				$this->Cell(31,6,'Temps quick patient : ','T',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultats[14]['temps_quick_patient'].'  s','T',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(8,6,'','T',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(16,6,'','T',1,'L',1);
				
				
				//Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
				$this->Cell(27,6,'','B',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(35,6,'Taux prothrombine patient :','B',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(17,6,$resultats[14]['taux_prothrombine_patient'].'  %','B',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(20,6,'( 70 - 100 )','B',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(13,6,'','B',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(31,6,'INR :','B',0,'R',1);
				
				$valeurINR = $resultats[14]['inr_patient'];
				if(fmod($valeurINR, 1) !== 0.00){$valeurINR = number_format($valeurINR, 1, ',', ' ');}
				
				
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$valeurINR,'B',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(8,6,'','B',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(16,6,'( < 1,2 )','B',1,'L',1);
				
				
				
				
				
				
				
				
				
				
				
				
			}
		
			//--- TCA  ---  TCA ---  TCA  ---  TCA  --- TCA
			if(in_array(15, $idAnalysesHemostase)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(22,6,'TCA : ','BT',0,'L',1);
			
				$this->SetFont('times','I',8);
				$this->Cell(13,6,'T�moin : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(17,6,$resultats[15]['temoin_patient'].' s','BT',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(25,6,'Patient: ','BT',0,'R',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(17,6,$resultats[15]['tca_patient'].' s','BT',0,'L',1);
				
				$this->SetFont('times','I',9);
				$this->Cell(13,6,'( 25 - 41 )','BT',0,'R',1);
			
				$this->SetFont('times','I',8);
				$this->Cell(31,6,'Ratio : ','BT',0,'R',1);
			
				$valeurRatio = $resultats[15]['tca_patient']/$resultats[15]['temoin_patient'];
				if(fmod($valeurRatio, 1) !== 0.00){$valeurRatio = number_format($valeurRatio, 2, ',', ' ');}
					
				$this->SetFont('times','B',11);
				$this->Cell(26,6,$valeurRatio,'BT',0,'L',1);
			
				$this->SetFont('times','I',8);
				$this->Cell(16,6,'( < 1,2 )','BT',1,'L',1);
			
			}
			
			//--- FIBRINEMIE  -&-  TEMPS DE SAIGNEMENT -&-  FIBRINEMIE  -&-  TEMPS DE SAIGNEMENT
			if(in_array(16, $idAnalysesHemostase) || in_array(17, $idAnalysesHemostase)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				
				if(in_array(16, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(22,6,'FIBRINEMIE : ','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[16]['fibrinemie'].'  g/l','BT',0,'R',1);
						
					$this->SetFont('times','I',8);
					$this->Cell(24,6,'','BT',0,'R',1);
					$this->Cell(23,6,'( 2 - 4 g/l )','BT',0,'R',1);
					
					/** S�parateur --- S�parateur --- S�parateur **/
					$this->Cell(1,6,' ','',0,'L',0);
				}
				
				if(in_array(17, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(42,6,'TEMPS DE SAIGNEMENT : ','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[17]['temps_saignement'].'  min','BT',0,'R',1);
						
					$this->SetFont('times','I',8);
					$this->Cell(27,6,'( 2 - 6 min )','BT',0,'R',1);
				}
				
				//Aller � la ligne
				$this->Cell(0,6,'','',1);
			}
			
			//FACTEUR 8  -&-  FACTEUR 9  -&-  FACTEUR 8  -&-  FACTEUR 9
			if(in_array(18, $idAnalysesHemostase) || in_array(19, $idAnalysesHemostase)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				
				if(in_array(18, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(22,6,'FACTEUR VIII : ','BT',0,'L',1);
			
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[18]['facteur_8'].' s','BT',0,'R',1);
			
					$this->SetFont('times','I',8);
					$this->Cell(24,6,'','BT',0,'R',1);
					$this->Cell(23,6,'','BT',0,'R',1);
						
					/** S�parateur --- S�parateur --- S�parateur **/
					$this->Cell(1,6,' ','',0,'L',0);
				}
			
				if(in_array(19, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(42,6,'FACTEUR IX : ','BT',0,'L',1);
			
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[19]['facteur_9'].' s','BT',0,'R',1);
			
					$this->SetFont('times','I',8);
					$this->Cell(27,6,'','BT',0,'R',1);
				}
			
				//Aller � la ligne
				$this->Cell(0,6,'','',1);
			}
			
			//D-DIMERES  ---  D-DIMERES  ---  D-DIMERES  ---  D-DIMERES
			if(in_array(20, $idAnalysesHemostase)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
			
				if(in_array(20, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(22,6,'D-DIMERES : ','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(24,6,$resultats[20]['d_dimeres'].' ug/ml','BT',0,'R',1);
						
					$this->SetFont('times','I',8);
					$this->Cell(18,6,'','BT',0,'R',1);
					$this->Cell(23,6,'','BT',0,'R',1);
			
					/** S�parateur --- S�parateur --- S�parateur **/
					$this->Cell(1,6,' ','',0,'L',0);
				}
					
				/*
				if(in_array(19, $idAnalysesHemostase)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(42,6,'FACTEUR 9 : ','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[19]['facteur_9'].'  ','BT',0,'R',1);
						
					$this->SetFont('times','I',8);
					$this->Cell(27,6,'','BT',0,'R',1);
				}
				*/
					
				//Aller � la ligne
				$this->Cell(0,6,'','',1);
			}
			
			
			
			
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- SEROLOGIE --- SEROLOGIE --- SEROLOGIE --- SEROLOGIE --- SEROLOGIE
		 */
		
		$idAnalysesSerologie = $this->getAnalysesSerologie();
		if($idAnalysesSerologie){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'SEROLOGIE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//GOUTTE EPAISSE --- GOUTTE EPAISSE --- GOUTTE EPAISSE --- GOUTTE EPAISSE
			if(in_array(10, $idAnalysesSerologie)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'GOUTTE EPAISSE : ','T',0,'L',1);
					
				$goutteEpaisse = 'Positif';
				if($resultats[10]['goutte_epaisse'] == 'Negatif'){ $goutteEpaisse = 'N�gatif'; }
				
				$this->SetFont('times','B',11.5);
				$this->Cell(25,6,$goutteEpaisse,'T',0,'L',1);
					
				if($resultats[10]['densite_parasitaire']){
					$this->SetFont('times','I',10);
					$this->Cell(33,6,'Densit� parasitaire : ','T',0,'R',1);
					$this->SetFont('times','B',10);
					$this->Cell(62,6,$resultats[10]['densite_parasitaire'].' p/ul','T',1,'L',1);
				}else{
					$this->SetFont('times','I',10);
					$this->Cell(20,6,'','T',0,'L',1);
					$this->SetFont('times','B',11);
					$this->Cell(75,6,'','T',1,'L',1);
				}
				
				/** Commentaire --- Commentaire --- Commentaire**/
				/** Commentaire --- Commentaire --- Commentaire**/
				if(str_replace(' ','', $resultats[10]['commentaire_goutte_epaisse'])){
					$this->Cell(15,6,'','B',0,'L',1);
					
					$this->SetFont('timesbi','U',10);
					$this->Cell(25,6,'Commentaire :','B',0,'R',1);
					
					$this->SetFont('times','B',11);
					$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $resultats[10]['commentaire_goutte_epaisse']),'B','J',1);
				}
				

			}
		
			
			//PSA --- PSA --- PSA --- PSA --- PSA --- PSA
			if(in_array(52, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(50,6,"PSA QUALITATIF : ",'BT',0,'L',1);
					
				$psa_qualitatif = 'Positif';
				if($resultats[52]['psa_qualitatif'] == 'Negatif'){ $psa_qualitatif = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(35,6,$psa_qualitatif,'BT',0,'L',1);
				
                /*
				$this->SetFont('times','I',9);
				$this->Cell(13,6,'Titre :','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[52]['psa'], 0, ',', ' ').'  ng/ml','BT',0,'L',1);
			    */
                
				$this->Cell(38,6,'','BT',0,'L',1);

				$this->SetFont('times','I',9);
				$this->Cell(38,6,'','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(19,6,'','BT',1,'L',1);
					
			}
			
			
			//CRP ou C. Prot�ine R�active --- CRP ou C. Prot�ine R�active --- CRP ou C. Prot�ine R�active
			if(in_array(53, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(50,6,"CRP ou C. Prot�ine R�active : ",'BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'','BT',0,'L',1);
					
				$valeurCrp = 'Positif';
				if($resultats[53]['optionResultatCrp'] == 'negatif'){ $valeurCrp = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$valeurCrp,'BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(10,6,'','BT',0,'L',1);
					
				if($resultats[53]['crpValeurResultat']){
					$this->SetFont('times','B',11);
					$this->Cell(45,6,number_format($resultats[53]['crpValeurResultat'], 0, ',', ' ').' mg/l','BT',0,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(45,6,'','BT',0,'L',1);
				}

					
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'( < 6 mg/l )','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(19,6,'','BT',1,'L',1);

			}
			
			
			//FACTEURS RHUMATOIDES (RF LATEX) --- FACTEURS RHUMATOIDES (RF LATEX) ---
			if(in_array(54, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(63,6,"FACTEURS RHUMATOIDES (RF LATEX) : ",'BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(5,6,'','BT',0,'L',1);
					
				$valeurFacteursRhumatoides = 'Positif';
				if($resultats[54]['facteurs_rhumatoides'] == 'Negatif'){ $valeurFacteursRhumatoides = 'N�gatif'; }
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$valeurFacteursRhumatoides,'BT',0,'L',1);
					
				if($resultats[54]['facteurs_rhumatoides_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','BT',0,'L',1);
					
					$this->SetFont('times','B',11);
					$this->Cell(45,6,number_format($resultats[54]['facteurs_rhumatoides_titre'], 0, ',', ' ').' UI/ml','BT',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'','BT',0,'L',1);
					
					$this->SetFont('times','B',11);
					$this->Cell(45,6,'','BT',0,'L',1);
				}
			
					
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'( < 8 UI/ml )','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(19,6,'','BT',1,'L',1);
			
			}
			
			
			//RF WAALER ROSE --- RF WAALER ROSE --- RF WAALER ROSE --- RF WAALER ROSE
			if(in_array(55, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(63,6,"RF WAALER ROSE : ",'BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(5,6,'','BT',0,'L',1);
					
				$valeurWaalerRose = 'Positif';
				if($resultats[55]['rf_waaler_rose'] == 'Negatif'){ $valeurWaalerRose = 'N�gatif'; }
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$valeurWaalerRose,'BT',0,'L',1);
					
				if($resultats[55]['rf_waaler_rose_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(45,6,number_format($resultats[55]['rf_waaler_rose_titre'], 0, ',', ' ').'  UI/ml','BT',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'','BT',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(45,6,'','BT',0,'L',1);
				}
					
					
				$this->SetFont('times','I',9);
				$this->Cell(18,6,'( < 8 UI/ml )','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(19,6,'','BT',1,'L',1);
					
			}
			
			
			//TOXOPLASMOSE --- TOXOPLASMOSE --- TOXOPLASMOSE --- TOXOPLASMOSE
			if(in_array(56, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(35,6,"TOXOPLASMOSE : ",'T',0,'L',1);
					
				$this->SetFont('times','',10);
				$this->Cell(13,6,'� IgM :','T',0,'L',1);
					
			
				$resultatToxoIGM = 'Positif';
				if($resultats[56]['toxoplasmose_igm'] == 'Negatif'){ $resultatToxoIGM = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatToxoIGM,'T',0,'L',1);
					
				if($resultats[56]['toxoplasmose_igm_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(25,6,number_format($resultats[56]['toxoplasmose_igm_titre'], 0, ',', ' ').' UI/ml','T',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'','T',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(25,6,'','T',0,'L',1);
				}
					
				
				$this->SetFont('times','',10);
				$this->Cell(13,6,'� IgG :','T',0,'L',1);
				
				$resultatToxoIGG = 'Positif';
				if($resultats[56]['toxoplasmose_igg'] == 'Negatif'){ $resultatToxoIGG = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatToxoIGG,'T',0,'L',1);
				
				if($resultats[56]['toxoplasmose_igg_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
					
					$this->SetFont('times','B',11);
					$this->Cell(25,6,number_format($resultats[56]['toxoplasmose_igg_titre'], 0, ',', ' ').' UI/ml','T',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(35,6,'','T',0,'L',1);
				}
				
				$this->SetFont('times','I',7);
				$this->Cell(9,6,'','T',1,'L',1);
				
				
				/** Commentaire --- Commentaire --- Commentaire**/
				/** Commentaire --- Commentaire --- Commentaire**/
				$commentaireToxo = $resultats[56]['toxoplasmose_commentaire'];
				if($commentaireToxo){
					$this->Cell(15,6,'','B',0,'L',1);
						
					$this->SetFont('timesbi','U',10);
					$this->Cell(25,6,'Commentaire :','B',0,'R',1);
						
					$this->SetFont('times','B',11);
					$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $commentaireToxo),'B','J',1);
				
				}
					
			}
			
			
			//RUBEOLE --- RUBEOLE --- RUBEOLE --- RUBEOLE --- RUBEOLE --- RUBEOLE
			if(in_array(57, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(35,6,"RUBEOLE : ",'T',0,'L',1);
					
				$this->SetFont('times','',10);
				$this->Cell(13,6,'� IgM :','T',0,'L',1);
					
					
				$resultatToxoIGM = 'Positif';
				if($resultats[57]['rubeole_igm'] == 'Negatif'){ $resultatToxoIGM = 'N�gatif'; }
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatToxoIGM,'T',0,'L',1);
					
				if($resultats[57]['rubeole_igm_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
			
					$this->SetFont('times','B',11);
					$this->Cell(25,6,number_format($resultats[57]['rubeole_igm_titre'], 0, ',', ' ').' UI/ml','T',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'','T',0,'L',1);
			
					$this->SetFont('times','B',11);
					$this->Cell(25,6,'','T',0,'L',1);
				}
					
			
				$this->SetFont('times','',10);
				$this->Cell(13,6,'� IgG :','T',0,'L',1);
			
				$resultatToxoIGG = 'Positif';
				if($resultats[57]['rubeole_igg'] == 'Negatif'){ $resultatToxoIGG = 'N�gatif'; }
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatToxoIGG,'T',0,'L',1);
			
				if($resultats[57]['rubeole_igg_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(25,6,number_format($resultats[57]['rubeole_igg_titre'], 0, ',', ' ').' UI/ml','T',0,'L',1);
				}else{
					$this->SetFont('times','I',9);
					$this->Cell(35,6,'','T',0,'L',1);
				}
			
				$this->SetFont('times','I',7);
				$this->Cell(9,6,'','T',1,'L',1);
					
				
				/** Commentaire --- Commentaire --- Commentaire**/
				/** Commentaire --- Commentaire --- Commentaire**/
				$commentaireBubeole = $resultats[57]['rubeole_commentaire'];
				if($commentaireBubeole){
					$this->Cell(15,6,'','B',0,'L',1);
				
					$this->SetFont('timesbi','U',10);
					$this->Cell(25,6,'Commentaire :','B',0,'R',1);
				
					$this->SetFont('times','B',11);
					$this->MultiCell(145,6,iconv ('UTF-8' , 'windows-1252', $commentaireBubeole),'B','J',1);
				}
				
			}
			
			
			//SEROLOGIE SYPHILITIQUE BW (RPR/TPHA) --- SEROLOGIE SYPHILITIQUE BW (RPR/TPHA)
			if(in_array(60, $idAnalysesSerologie)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'SEROLOGIE SYPHILITIQUE BW : ','BT',0,'L',1);
			
				$this->SetFont('times','I',9);
				$this->Cell(10,6,'RPR : ','BT',0,'R',1);
			
				$resultatSerologieSyphilitiqueRpr = 'Positif';
				if($resultats[60]['serologie_syphilitique_rpr'] == 'Negatif'){ $resultatSerologieSyphilitiqueRpr = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatSerologieSyphilitiqueRpr,'BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(10,6,'','BT',0,'L',1);
			
				$this->SetFont('times','I',9);
				$this->Cell(15,6,'TPHA : ','BT',0,'R',1);
			
				$resultatSerologieSyphilitiqueTpha = 'Positif';
				if($resultats[60]['serologie_syphilitique_tpha'] == 'Negatif'){ $resultatSerologieSyphilitiqueTpha = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatSerologieSyphilitiqueTpha,'BT',0,'L',1);
			
				if($resultats[60]['serologie_syphilitique_tpha_titre']){
					$this->SetFont('times','I',9);
					$this->Cell(25,6,'TITRE : ','BT',0,'R',1);
					
					$this->SetFont('times','B',11);
					$this->Cell(20,6,$resultats[60]['serologie_syphilitique_tpha_titre'],'BT',1,'L',1);
				}else{
					$this->SetFont('times','I',7);
					$this->Cell(45,6,'','BT',1,'L',1);
				}
			
			}
			
			
			//ASLO --- ASLO --- ASLO --- ASLO --- ASLO --- ASLO --- ASLO --- ASLO	
			if(in_array(61, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'ASLO : ','BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(10,6,'','BT',0,'R',1);
					
				$resultatAslo = 'Positif';
				if($resultats[61]['aslo'] == 'Negatif'){ $resultatAslo = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatAslo,'BT',0,'L',1);
					
				if($resultats[61]['titre']){
					$this->SetFont('times','I',9);
					$this->Cell(25,6,'TITRE : ','BT',0,'R',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(35,6,$resultats[61]['titre'].' UI/ml','BT',0,'L',1);
				}else{
					$this->SetFont('times','I',7);
					$this->Cell(45,6,'','BT',0,'L',1);
				}
				
				$this->SetFont('times','I',8);
				$this->Cell(30,6,'( < 200 UI/mL )','BT',1,'L',1);
				
					
			}
			
			
			// Ag HbS --- Ag HbS --- Ag HbS --- Ag HbS --- Ag HbS --- Ag HbS ---
			if(in_array(63, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
					
				if(in_array(63, $idAnalysesSerologie)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(32,6,'ANTIGENE HbS : ','BT',0,'L',1);
			
					$valeurAgHbS = 'Positif';
					if($resultats[63]['ag_hbs'] == 'Negatif'){ $valeurAgHbS = 'N�gatif'; }
					$this->SetFont('times','B',11);
					$this->Cell(24,6,$valeurAgHbS,'BT',0,'R',1);
			
					$this->SetFont('times','I',8);
					$this->Cell(31,6,'( Qualitatif )','BT',0,'R',1);
						
					/** S�parateur --- S�parateur --- S�parateur **/
					$this->Cell(1,6,' ','',0,'L',1);
				}
					
				/*
				 if(in_array(19, $idAnalysesHemostase)){
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(42,6,'FACTEUR 9 : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultats[19]['facteur_9'].'  ','BT',0,'R',1);
			
				$this->SetFont('times','I',8);
				$this->Cell(27,6,'','BT',0,'R',1);
				}
				*/
				$this->Cell(92,6,'','BT',0,'R',1);
				//Aller � la ligne
				$this->Cell(0,6,'','',1);
			}
			
			
			
			//WIDAL --- WIDAL --- WIDAL--- WIDAL --- WIDAL --- WIDAL--- WIDAL ---
			if(in_array(62, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','T',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(25,6,"WIDAL : ",'T',0,'L',1);
					
				/*
				 * Typhi --- Typhi --- Typhi --- Typhi
				 */
				///**** Typhi TO ************************************************************
				///**** Typhi TO ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(28,6,'� Typhi TO :','T',0,'L',1);
			
				$resultatTyphiTO  = 'Positif';
				if($resultats[62]['widal_to'] == 'Negatif'){ $resultatTyphiTO = 'N�gatif'; }

				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultatTyphiTO,'T',0,'L',1);
				
			    if($resultats[62]['widal_titre_to']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
			
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[62]['widal_titre_to'].' ','T',0,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(28,6,'','T',0,'L',1);
				}
					
				///**** FIN Typhi TO ************************************************************
				///**** FIN Typhi TO ************************************************************
						
				
				///**** Typhi TH ************************************************************
				///**** Typhi TH ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(26,6,'� Typhi TH :','T',0,'L',1);
			
			    $resultatTyphiTH  = 'Positif';
			    if($resultats[62]['widal_th'] == 'Negatif'){ $resultatTyphiTH = 'N�gatif'; }
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatTyphiTH,'T',0,'L',1);
			
			    if($resultats[62]['widal_titre_th']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','T',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(25,6,$resultats[62]['widal_titre_th'].' ','T',1,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(35,6,'','T',1,'L',1);
				}
			
				///**** FIN Typhi TH ************************************************************
				///**** FIN Typhi TH ************************************************************
					
				/*
				 * FIN Typhi --- FIN Typhi --- FIN Typhi --- FIN Typhi
				*/
				
				
				
				/*
				 * Paratyphi AO&AH --- Paratyphi AO&AH --- Paratyphi AO&AH --- Paratyphi AO&AH
				*/
				$this->Cell(30,6,'','',0,'L',1);
				///**** Paratyphi AO ************************************************************
				///**** Paratyphi AO ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(28,6,'� Paratyphi AO :','',0,'L',1);
					
				$resultatParatyphiAO  = 'Positif';
				if($resultats[62]['widal_ao'] == 'Negatif'){ $resultatParatyphiAO = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultatParatyphiAO,'',0,'L',1);
				
				if($resultats[62]['widal_titre_ao']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','',0,'L',1);
						
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[62]['widal_titre_ao'].' ','',0,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(28,6,'','',0,'L',1);
				}
					
				///**** FIN Paratyphi AO ************************************************************
				///**** FIN Paratyphi TO ************************************************************
				
				
				///**** Paratyphi AH ************************************************************
				///**** Paratyphi AH ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(26,6,'� Paratyphi AH :','',0,'L',1);
					
				$resultatParatyphiAH  = 'Positif';
				if($resultats[62]['widal_ah'] == 'Negatif'){ $resultatParatyphiAH = 'N�gatif'; }
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatParatyphiAH,'',0,'L',1);
					
				if($resultats[62]['widal_titre_ah']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','',0,'L',1);
				
					$this->SetFont('times','B',11);
					$this->Cell(25,6,$resultats[62]['widal_titre_ah'].' ','',1,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(35,6,'','',1,'L',1);
				}
					
				///**** FIN Paratyphi AH ************************************************************
				///**** FIN Paratyphi AH ************************************************************
					
				/*
				 * FIN Paratyphi AO&AH --- FIN Paratyphi AO&AH --- FIN Paratyphi AO&AH --- FIN Paratyphi AO&AH
				*/
				
				
				
				/*
				 * Paratyphi BO&BH --- Paratyphi BO&BH --- Paratyphi BO&BH --- Paratyphi BO&BH
				*/
				$this->Cell(30,6,'','',0,'L',1);
				///**** Paratyphi BO ************************************************************
				///**** Paratyphi BO ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(28,6,'� Paratyphi BO :','',0,'L',1);
					
				$resultatParatyphiBO  = 'Positif';
				if($resultats[62]['widal_bo'] == 'Negatif'){ $resultatParatyphiBO = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultatParatyphiBO,'',0,'L',1);
				
				if($resultats[62]['widal_titre_bo']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','',0,'L',1);
				
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[62]['widal_titre_bo'].' ','',0,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(28,6,'','',0,'L',1);
				}
					
				///**** FIN Paratyphi BO ************************************************************
				///**** FIN Paratyphi BO ************************************************************
				
				
				///**** Paratyphi BH ************************************************************
				///**** Paratyphi BH ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(26,6,'� Paratyphi BH :','',0,'L',1);
					
				$resultatParatyphiBH  = 'Positif';
				if($resultats[62]['widal_bh'] == 'Negatif'){ $resultatParatyphiBH = 'N�gatif'; }
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatParatyphiBH,'',0,'L',1);
					
				if($resultats[62]['widal_titre_bh']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','',0,'L',1);
				
					$this->SetFont('times','B',11);
					$this->Cell(25,6,$resultats[62]['widal_titre_bh'].' ','',1,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(35,6,'','',1,'L',1);
				}
					
				///**** FIN Paratyphi BH ************************************************************
				///**** FIN Paratyphi BH ************************************************************
					
				/*
				 * FIN Paratyphi BO&BH --- FIN Paratyphi BO&BH --- FIN Paratyphi BO&BH --- FIN Paratyphi BO&BH
				*/
				
				
				/*
				 * Paratyphi CO&CH --- Paratyphi CO&CH --- Paratyphi CO&CH --- Paratyphi CO&CH
				*/
				$this->Cell(30,6,'','B',0,'L',1);
				///**** Paratyphi CO ************************************************************
				///**** Paratyphi CO ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(28,6,'� Paratyphi CO :','B',0,'L',1);
					
				$resultatParatyphiCO  = 'Positif';
				if($resultats[62]['widal_co'] == 'Negatif'){ $resultatParatyphiCO = 'N�gatif'; }
				
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultatParatyphiCO,'B',0,'L',1);
				
				if($resultats[62]['widal_titre_co']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','B',0,'L',1);
				
					$this->SetFont('times','B',11);
					$this->Cell(18,6,$resultats[62]['widal_titre_co'].' ','B',0,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(28,6,'','B',0,'L',1);
				}
					
				///**** FIN Paratyphi CO ************************************************************
				///**** FIN Paratyphi CO ************************************************************
				
				
				///**** Paratyphi CH ************************************************************
				///**** Paratyphi CH ************************************************************
				$this->SetFont('times','',10);
				$this->Cell(26,6,'� Paratyphi CH :','B',0,'L',1);
					
				$resultatParatyphiCH  = 'Positif';
				if($resultats[62]['widal_ch'] == 'Negatif'){ $resultatParatyphiCH = 'N�gatif'; }
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultatParatyphiCH,'B',0,'L',1);
					
				if($resultats[62]['widal_titre_ch']){
					$this->SetFont('times','I',9);
					$this->Cell(10,6,'Titre :','B',0,'L',1);
				
					$this->SetFont('times','B',11);
					$this->Cell(25,6,$resultats[62]['widal_titre_ch'].' ','B',1,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(35,6,'','B',1,'L',1);
				}
					
				///**** FIN Paratyphi CH ************************************************************
				///**** FIN Paratyphi CH ************************************************************
					
				/*
				 * FIN Paratyphi CO&CH --- FIN Paratyphi CO&CH --- FIN Paratyphi CO&CH --- FIN Paratyphi CO&CH
				*/
				
				
			}
			
			
			// Beta HCG --- Beta HCG --- Beta HCG --- Beta HCG --- Beta HCG --- Beta HCG ---
			if(in_array(51, $idAnalysesSerologie)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
					
				if(in_array(51, $idAnalysesSerologie)){
					$this->SetFont('zap','',11.3);
					$this->Cell(5,6,' +','BT',0,'C',1);
					$this->SetFont('times','',9);
					$this->Cell(52,6,'BETA HCG PLASMATIQUE : ','BT',0,'L',1);
						
					$valeurBetaHcg = 'Positif';
					if($resultats[51]['beta_hcg_plasmatique'] == 'Negatif'){ $valeurBetaHcg = 'N�gatif'; }
					$this->SetFont('times','B',11);
					$this->Cell(24,6,$valeurBetaHcg,'BT',0,'R',1);
						
					$this->SetFont('times','I',8);
					$this->Cell(31,6,'( Qualitatif )','BT',0,'R',1);
			
					/** S�parateur --- S�parateur --- S�parateur **/
					$this->Cell(1,6,' ','',0,'L',1);
				}
					
				/*
				 if(in_array(19, $idAnalysesHemostase)){
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(42,6,'FACTEUR 9 : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(18,6,$resultats[19]['facteur_9'].'  ','BT',0,'R',1);
					
				$this->SetFont('times','I',8);
				$this->Cell(27,6,'','BT',0,'R',1);
				}
				*/
				$this->Cell(72,6,'','BT',0,'R',1);
				//Aller � la ligne
				$this->Cell(0,6,'','',1);
			}
			
		}
		
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- BILAN HEPATIQUE --- BILAN HEPATIQUE --- BILAN HEPATIQUE 
		 */
		
		$idAnalysesBilanHepatique = $this->getAnalysesBilanHepatique();
		if($idAnalysesBilanHepatique){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'BILAN HEPATIQUE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//TRANSAMINASES --- TRANSAMINASES --- TRANSAMINASES --- TRANSAMINASES
			if(in_array(37, $idAnalysesBilanHepatique)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'TRANSAMINASES : ','BT',0,'L',1);
				
				$this->SetFont('times','I',9);
				$this->Cell(20,6,'TGO/ASAT : ','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultats[37][1]['tgo_asat'].' u/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H < 35 , F < 31 )','BT',0,'L',1);
				
				$this->SetFont('times','I',9);
				$this->Cell(20,6,'TGP/ALAT : ','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultats[37][2]['tgp_alat'].' u/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H < 45 , F < 34 )','BT',1,'L',1);
		
			}
			
			//PHOSPHATAGE ALCALINE (PAL) --- PHOSPHATAGE ALCALINE (PAL) --- PHOSPHATAGE ALCALINE (PAL)
			if(in_array(38, $idAnalysesBilanHepatique)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'PHOSPHATAGE ALCALINE (PAL) : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultats[38]['valeur'].' u/l','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H: 40 � 129 ; F: 35 � 104 )','BT',0,'L',1);
			
				$this->SetFont('times','I',9);
				$this->Cell(20,6,'','BT',0,'R',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,'','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'','BT',1,'L',1);
			
			}
			
			//GAMA GT = YGT --- GAMA GT = YGT --- GAMA GT = YGT --- GAMA GT = YGT
			if(in_array(39, $idAnalysesBilanHepatique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'GAMA GT = YGT : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$resultats[39]['valeur'].' UI/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H: 11 � 50 ; F: 7 � 32 )','BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(20,6,'','BT',0,'R',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,'','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'','BT',1,'L',1);
					
			}
			
			//BILIRUBINE TOTALE & DIRECTE --- BILIRUBINE TOTALE & DIRECTE
			if(in_array(42, $idAnalysesBilanHepatique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249);}
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(55,6,'BILIRUBINE TOTALE & DIRECTE : ','T',0,'L',1);
					
				/*** Bilirubine totale ***/
				$this->SetFont('times','I',10);
				$this->Cell(33,5,'� Bilirubine totale :','T',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,5,$resultats[42]['bilirubine_totale'].' mg/l','T',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(20,5,'( < 10 )','T',0,'C',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,5,number_format($resultats[42]['bilirubine_totale_auto'], 2, ',', ' ').' umol/l','T',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(22,5,'( < 17,105 )','T',1,'C',1);
				
				/*** Bilirubine directe ***/
				$this->Cell(60,5,'','',0,'L',1);
				
				$this->SetFont('times','I',10);
				$this->Cell(33,5,'� Bilirubine directe :','',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,5,$resultats[42]['bilirubine_directe'].' mg/l','',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(20,5,'( < 4 )','T',0,'C',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,5,number_format($resultats[42]['bilirubine_directe_auto'], 2, ',', ' ').' umol/l','',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(22,5,'( < 6,8420 )','',1,'C',1);
					
				/*** Bilirubine indecte ***/
				$this->Cell(60,5,'','B',0,'L',1);
				
				$this->SetFont('times','I',10);
				$this->Cell(33,5,'� Bilirubine indirecte :','B',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,5,$resultats[42]['bilirubine_indirecte'].' mg/l','B',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(20,5,'( < 6 )','T',0,'C',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,5,number_format($resultats[42]['bilirubine_indirecte_auto'], 2, ',', ' ').' umol/l','B',0,'R',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(22,5,'( < 10,26 )','B',1,'C',1);
				
			}
			
			//LDH --- LDH --- LDH --- LDH --- LDH --- LDH --- LDH
			if(in_array(70, $idAnalysesBilanHepatique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'LDH : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[70]['valeur_ldh'], 0, ',', ' ').' UI/l','BT',0,'L',1);
					
				$this->SetFont('times','I',8);
				$this->Cell(30,6,'( 324 - 1029 )','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(45,6,'','BT',1,'L',1);
			}
			
		
		}
		
		
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- BILAN RENAL --- BILAN RENAL --- BILAN RENAL
		 */
		
		$idAnalysesBilanRenal = $this->getAnalysesBilanRenal();
		if($idAnalysesBilanRenal){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'BILAN RENAL','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//CREATININEMIE --- CREATININEMIE --- CREATININEMIE --- CREATININEMIE ---
			if(in_array(22, $idAnalysesBilanRenal)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); } 
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'CREATININEMIE : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(25,6,$resultats[22]['creatininemie'].' mg/l','BT',0,'L',1);
		
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( H= 7 � 13 | F= 6 � 11 )','BT',0,'L',1);
		
				
				if($resultats[22]['creatininemie']){
					$valeurEnUmol = number_format($resultats[22]['creatininemie']*8.84, 2, ',', ' ');
					$this->SetFont('times','B',11);
					$this->Cell(35,6,$valeurEnUmol.' mmol/l','BT',0,'L',1);
					
					$this->SetFont('times','I',7);
					$this->Cell(40,6,'( H= 61,8 � 114,9 | F= 53,0 � 97,2 )','BT',1,'L',1);
				}else{
					$this->SetFont('times','B',11);
					$this->Cell(35,6,'','BT',0,'R',1);
					
					$this->SetFont('times','I',7);
					$this->Cell(40,6,'','BT',1,'L',1);					
				}
		
			}
			
			//AZOTEMIE = UREE --- AZOTEMIE = UREE --- AZOTEMIE = UREE --- AZOTEMIE = UREE
			if(in_array(23, $idAnalysesBilanRenal)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'AZOTEMIE = UREE : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[23]['valeur'],2, ',', ' ').' mg/l','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( 0,15 � 0,45 )','BT',0,'L',1);

				$this->SetFont('times','B',11);
				$this->Cell(35,6,number_format($resultats[23]['valeur_mmol'],2, ',', ' ').' mmol/l','BT',0,'L',1);

				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( 2,49 � 7,49 )','BT',1,'L',1);
			
			}
			
			//URICEMIE = ACIDE URIQUE --- URICEMIE = ACIDE URIQUE --- URICEMIE = ACIDE URIQUE
			if(in_array(24, $idAnalysesBilanRenal)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(45,6,'URICEMIE = ACIDE URIQUE : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,6,$resultats[24]['acide_urique'].' mg/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( H= 35 � 72 ; F= 26 � 60 )','BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(7,6,'','BT',0,'R',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(33,6,number_format($resultats[24]['acide_urique_umol'],2, ',', ' ').' mmol/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'(  H= 208 � 428, F= 154 � 356  )','BT',1,'L',1);
					
			}
			
			//ALBUMINE URINAIRE (BANDELETTES) --- ALBUMINE URINAIRE (BANDELETTES) --- 
			if(in_array(47, $idAnalysesBilanRenal)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(65,6,'ALBUMINE URINAIRE (BANDELETTES) : ','T',0,'L',1);
					
				/*Albumine --- Albumine --- Albumine*/
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'� Albumine : ','T',0,'R',1);
				
				$albumine = 'Positif';
				if($resultats[47]['albumine_urinaire'] == 'negatif'){ $albumine = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(15,6,$albumine,'T',0,'L',1);
					
				if($resultats[47]['albumine_urinaire_degres']){
					$this->SetFont('zap','',11.5);
					$this->Cell(8,6,' �','T',0,'L',1);
					
					$this->SetFont('times','B',12);
					$this->Cell(15,6,$resultats[47]['albumine_urinaire_degres'],'T',0,'L',1);
				}else{
					$this->Cell(23,6,'','T',0,'L',1);
				}
				
				/*Sucre --- Sucre --- Sucre -- Sucre --- Sucre*/
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'� Sucre : ','T',0,'R',1);
				
				$sucre_urinaire = 'Positif';
				if($resultats[47]['sucre_urinaire'] == 'negatif'){ $sucre_urinaire = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(15,6,$sucre_urinaire,'T',0,'L',1);
					
				if($resultats[47]['sucre_urinaire_degres']){
					$this->SetFont('zap','',11.5);
					$this->Cell(8,6,' �','T',0,'L',1);
					
					$this->SetFont('times','B',12);
					$this->Cell(14,6,$resultats[47]['sucre_urinaire_degres'],'T',1,'L',1);
				}else{
					$this->Cell(22,6,'','T',1,'L',1);
				}
				
				
				/*Corps c�tonique --- Corps c�tonique --- Corps c�tonique*/
				$this->Cell(70,6,'','B',0,'L',1); 
				
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'� Corps c�tonique : ','B',0,'R',1);
				
				$albumine = 'Positif';
				if($resultats[47]['corps_cetonique_urinaire'] == 'negatif'){ $albumine = 'N�gatif'; }
				$this->SetFont('times','B',11);
				$this->Cell(15,6,$albumine,'B',0,'L',1);
					
				if($resultats[47]['corps_cetonique_urinaire_degres']){
					$this->SetFont('zap','',11.5);
					$this->Cell(8,6,' �','B',0,'L',1);
						
					$this->SetFont('times','B',12);
					$this->Cell(15,6,$resultats[47]['corps_cetonique_urinaire_degres'],'B',0,'L',1);
				}else{
					$this->Cell(23,6,'','B',0,'L',1);
				}
				
				$this->Cell(57,6,'','B',1,'L',1);
				
			}
			
			
			//PROTEINURIE DES 24H --- PROTEINURIE DES 24H --- PROTEINURIE DES 24H
			if(in_array(49, $idAnalysesBilanRenal)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(50,6,'PROTEINURIE DES 24H : ','BT',0,'L',1);
					
				$this->SetFont('times','',10);
				$this->Cell(25,6,number_format($resultats[49]['proteinurie_1'], 2, ',', ' ').'  g/l','BT',0,'L',1);
					
				$this->SetFont('timesi','',11);
				$this->Cell(16,6,'Diur�se :','BT',0,'L',1);
			
				$valeurProteinurie = $resultats[49]['proteinurie_2'];
				if(fmod($valeurProteinurie, 1) !== 0.00){$valeurProteinurie = number_format($valeurProteinurie, 1, ',', ' ');}
				$this->SetFont('times','B',11);
				$this->Cell(29,6,$valeurProteinurie.'  l','BT',0,'L',1);
					
				$valeurProteinurie24h = $resultats[49]['proteinurie_g24h'];
				if(fmod($valeurProteinurie24h, 1) !== 0.00){$valeurProteinurie24h = number_format($valeurProteinurie24h, 2, ',', ' ');}
				$this->SetFont('times','B',11);
				$this->Cell(35,6,$valeurProteinurie24h.'  g/24h','BT',0,'L',1);
					
				$this->SetFont('times','I',8);
				$this->Cell(25,6,'( < 0,15 g/24H )','BT',1,'L',1);
			
			}
			
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- METABOLISME GLUCIDIQUE --- METABOLISME GLUCIDIQUE --- METABOLISME GLUCIDIQUE
		 */
		
		$idAnalysesMetabolismeGlucidique = $this->getAnalysesMetabolismeGlucidique();
		if($idAnalysesMetabolismeGlucidique){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'METABOLISME GLUCIDIQUE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//GLYCEMIE --- GLYCEMIE --- GLYCEMIE --- GLYCEMIE --- GLYCEMIE
			if(in_array(21, $idAnalysesMetabolismeGlucidique)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'GLYCEMIE : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[21]['glycemie_1'], 2, ',', ' ').' g/l','BT',0,'L',1);
		
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( H= 0,7 � 1,10 )','BT',0,'L',1);

				$this->SetFont('times','B',11);
				$this->Cell(35,6,number_format($resultats[21]['glycemie_2'], 2, ',', ' ').' mmol/l','BT',0,'L',1);

				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( 4,1 � 5,9 )','BT',1,'L',1);
		
			}
			
			
			//HEMOGLOBINE GLYQUEE HbA1c  --- HEMOGLOBINE GLYQUEE HbA1c --- HEMOGLOBINE GLYQUEE HbA1c
			if(in_array(43, $idAnalysesMetabolismeGlucidique)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(55,6,'HEMOGLOBINE GLYQUEE HbA1c : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(18,6,number_format($resultats[43]['hemoglobine_glyquee_hbac'], 1, ',', ' ').' %','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( HbA1C DCCT N: 4,27 - 6,07 )','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(35,6,number_format($resultats[43]['hemoglobine_glyquee_hbac_mmol'], 1, ',', ' ').' mmol/mol','BT',0,'L',1);
						
				$this->SetFont('times','I',7);
				$this->Cell(32,6,'( HbA1C IFCC N: 23 - 42 )','BT',1,'L',1);
			
			}
		
		}
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- BILAN LIPIDIQUE --- BILAN LIPIDIQUE --- BILAN LIPIDIQUE --- BILAN LIPIDIQUE
		 */
		
		$idAnalysesBilanLipidique = $this->getAnalysesBilanLipidique();
		if($idAnalysesBilanLipidique){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'BILAN LIPIDIQUE','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//CHOLESTEROL TOTAL  --- CHOLESTEROL TOTAL --- CHOLESTEROL TOTAL
			if(in_array(25, $idAnalysesBilanLipidique)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,10,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,10,'CHOLESTEROL TOTAL : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[25]['cholesterol_total_1'], 2, ',', ' ').' g/l','BT',0,'L',1);
		
				$this->Cell(5,10,'','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[25]['cholesterol_total_2'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
		
				$this->Cell(5,10,'','BT',0,'L',1);
				
				$this->SetFont('times','I',7.2);
				$this->Cell(80,10,'','BT',0,'L',1);
				
				$x = $this->GetX(); $y = $this->GetY();
				$this->Text($x-80, $y+3, 'Moins de 30 ans <1, 80 (4, 7 mmol/l) - Plus de 30 ans < 2, 00 (< 5, 2mmol/l)');
				$this->Text($x-80, $y+6, 'Interpr�tation clinique : suspect sup�rieur � 2, 20 (5, 7 mmol/l)');
				$this->Text($x-80, $y+9, 'Risque �lev� sup�rieur � 2, 60 (6, 7 mmol/l)');
				
				//Allez � la ligne suivante
				$this->Cell(0,10,'','',1,'L',1);
			}
		
			//CHOLESTEROL HDL  --- CHOLESTEROL HDL --- CHOLESTEROL HDL --- CHOLESTEROL HDL
			if(in_array(27, $idAnalysesBilanLipidique)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,10,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,10,'CHOLESTEROL HDL : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[27]['cholesterol_HDL_1'], 2, ',', ' ').' g/l','BT',0,'L',1);
			
				$this->Cell(5,10,'','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[27]['cholesterol_HDL_2'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
			
				$this->Cell(5,10,'','BT',0,'L',1);
			
				$this->SetFont('times','I',7.2);
				$this->Cell(80,10,'','BT',0,'L',1);
			
				$x = $this->GetX(); $y = $this->GetY();
				$this->Text($x-80, $y+3, '< 0, 35 ( < 0, 9 mmol/l) facteur de risque pour coronaropathies');
				$this->Text($x-80, $y+6, '> 0, 60 ( > 1, 5 mmol/l) risque r�duit pour coronaropathies');
				$this->Text($x-80, $y+9, '');
			
				//Allez � la ligne suivante
				$this->Cell(0,10,'','',1,'L',1);
			}
			
			//CHOLESTEROL LDL  --- CHOLESTEROL LDL --- CHOLESTEROL LDL --- CHOLESTEROL LDL
			if(in_array(28, $idAnalysesBilanLipidique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,10,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,10,'CHOLESTEROL LDL : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[28]['cholesterol_LDL_1'], 2, ',', ' ').' g/l','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[28]['cholesterol_LDL_2'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				$this->SetFont('times','I',7.2);
				$this->Cell(80,10,'','BT',0,'L',1);
					
				$x = $this->GetX(); $y = $this->GetY();
				$this->Text($x-80, $y+3, 'H < 0,50 (< 1,3 mmol/l); F: < 0,63 (< 1,6 mmol/l) risque r�duit pour cor.. ');
				$this->Text($x-80, $y+6, 'H > 1,72 (> 4,5 mmol/l); F: > 1,67 (4,3 mmol/l) risque accru pour cor.. ');
				$this->Text($x-80, $y+9, '(cor = coronaropathies) ');
					
				//Allez � la ligne suivante
				$this->Cell(0,10,'','',1,'L',1);
			}
			
			//--- TRIGLYCERIDES  --- TRIGLYCERIDES --- TRIGLYCERIDES --- TRIGLYCERIDES
			if(in_array(26, $idAnalysesBilanLipidique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,10,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,10,'TRIGLYCERIDES : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[26]['triglycerides_1'], 2, ',', ' ').' g/l','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($resultats[26]['triglycerides_2'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				$this->Cell(80,10,'','BT',0,'L',1);
					
				$x = $this->GetX(); $y = $this->GetY();
				$this->SetFont('timesbi','U',7.2);
				$this->Text($x-80, $y+3, 'Suspect sup�rieur � 1,50 (1,71 mmol/l)');
				$this->SetFont('times','I',7.2);
				$this->Text($x-80, $y+6, 'Interpr�tation clinique pour risque d\'ath�rocl�rose');
				$this->Text($x-80, $y+9, 'Risque accru sup�rieur � 2,00 (2,28 mmol/l)');
					
				//Allez � la ligne suivante
				$this->Cell(0,10,'','',1,'L',1);
			}
			
			//--- RAPPORT CHOLESTEROL TOTAL & HDL  --- RAPPORT CHOLESTEROL TOTAL & HDL
			if(in_array(25, $idAnalysesBilanLipidique) && in_array(27, $idAnalysesBilanLipidique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,10,' *','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,10,'Rapport: CHOLT/HDL : ','BT',0,'L',1);
					
				$rapportCHOL = $resultats[25]['cholesterol_total_1']/$resultats[27]['cholesterol_HDL_1'];
				$this->SetFont('times','B',11);
				$this->Cell(25,10,number_format($rapportCHOL, 2, ',', ' ').' ','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				$this->SetFont('times','I',9);
				$this->Cell(25,10,'( N: < 4,5 )','BT',0,'L',1);
					
				$this->Cell(5,10,'','BT',0,'L',1);
					
				//Affichage de la conclusion du rapport
				if($rapportCHOL >= 4.5 && $rapportCHOL <= 5){
					$conclusion_rapport_chol_hdl = "Risque d'ath�rog�ne faible";
				}else if($rapportCHOL > 5 && $rapportCHOL <= 6.5){
					$conclusion_rapport_chol_hdl = "Risque d'ath�rog�ne mod�r�";
				}else if($rapportCHOL > 6.5){
					$conclusion_rapport_chol_hdl = "Risque d'ath�rog�ne �lev�";
				}else{
					$conclusion_rapport_chol_hdl = "RAS";
				}
				
				$this->SetFont('timesi','U',10);
				$this->Cell(20,10,'Conclusion :','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(60,10,$conclusion_rapport_chol_hdl,'BT',1,'L',1);
				
			}
			
			//LIPIDES TOTAUX  --- LIPIDES TOTAUX --- LIPIDES TOTAUX --- LIPIDES TOTAUX
			if(in_array(30, $idAnalysesBilanLipidique)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(1);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'LIPIDES TOTAUX : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[30]['lipides_totaux'], 2, ',', ' ').' g/l','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(5,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',8);
				$this->Cell(35,6,'( 4 - 7 g/l )','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(35,6,'','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'','BT',1,'L',1);
			
			}
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- METABOLISME DU FER --- METABOLISME DU FER --- METABOLISME DU FER --- METABOLISME DU FER
		 */
		
		$idAnalysesMetabolismeDuFer = $this->getAnalysesMetabolismeFer();
		if($idAnalysesMetabolismeDuFer){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,'METABOLISME DU FER','',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//FER SERIQUE  --- FER SERIQUE --- FER SERIQUE --- FER SERIQUE 
		    if(in_array(40, $idAnalysesMetabolismeDuFer)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); } 
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'FER SERIQUE : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[40]['valeur_ug'], 1, ',', ' ').' ug/dl','BT',0,'L',1);
		
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( H: 64,8 � 175 - F: 50,3 � 170 )','BT',0,'L',1);

				$this->SetFont('times','B',11);
				$this->Cell(35,6,number_format($resultats[40]['valeur_umol'], 1, ',', ' ').' umol/l','BT',0,'L',1);

				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( H: 11,6 � 31,3 - F: 9,0 � 30,4 )','BT',1,'L',1);
			}
			
			//FERRITININE  --- FERRITININE --- FERRITININE --- FERRITININE
			if(in_array(41, $idAnalysesMetabolismeDuFer)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(40,6,'FERRITININE : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,6,number_format($resultats[41]['ferritinine'], 0, ',', ' ').' ng/ml','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( hommes: 70 - 435 ng/ml )','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( Femmes cycliques: 10 - 160 ng/ml )','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(45,6,'( Femmes m�nopaus�es: 25 - 280 ng/ml )','BT',1,'L',1);
			}
			
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- BILAN D'ELECTROLYTE --- BILAN D'ELECTROLYTE --- BILAN D'ELECTROLYTE 
		 */
		
		$idAnalysesBilanElectrolyte = $this->getAnalysesBilanElectrolyte();
		if($idAnalysesBilanElectrolyte){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,"BILAN D'ELECTROLYTE",'',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//IONOGRAMME  --- IONOGRAMME --- IONOGRAMME --- IONOGRAMME
			if(in_array(31, $idAnalysesBilanElectrolyte)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(30,6,'IONOGRAMME : ','BT',0,'L',1);
		
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'Natr�mie :','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,6,number_format($resultats[31]['sodium_sanguin'], 0, ',', ' ').' mmol/l','BT',0,'L',1);
		
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'Kali�mie :','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(30,6,number_format($resultats[31]['potassium_sanguin'], 1, ',', ' ').' mmol/l','BT',0,'L',1);
				
				$this->SetFont('times','I',10);
				$this->Cell(20,6,'Chlor�mie :','BT',0,'R',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(30,6,number_format($resultats[31]['chlore_sanguin'], 0, ',', ' ').' mmol/l','BT',1,'L',1);
			}
			
			//CALCEMIE  --- CALCEMIE --- CALCEMIE --- CALCEMIE --- CALCEMIE
			if(in_array(32, $idAnalysesBilanElectrolyte)){
			
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(35,6,'CALCEMIE : ','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[32]['calcemie'], 0, ',', ' ').' mg/l','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(47,6,'( Adultes: 86 � 103  ;  Enfants: 100 � 120 )','BT',0,'L',1);
				
				$this->Cell(2,6,'','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(28,6,number_format($resultats[32]['calcemie_mmol'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
			
				$this->SetFont('times','I',7);
				$this->Cell(48,6,'( Adultes: 2,14 � 2,56 ; Enfants: 2,49 � 2,98 )','BT',1,'L',1);
			}
			
			//MAGNESEMIE  --- MAGNESEMIE --- MAGNESEMIE --- MAGNESEMIE --- MAGNESEMIE
			if(in_array(33, $idAnalysesBilanElectrolyte)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(35,6,'MAGNESEMIE : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(40,6,number_format($resultats[33]['magnesemie'], 0, ',', ' ').' mg/l','BT',0,'L',1);
					
				$this->SetFont('times','I',8);
				$this->Cell(50,6,'( 17 � 24 )','BT',0,'L',1);
			
				$this->SetFont('times','B',11);
				$this->Cell(25,6,'','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(30,6,'','BT',1,'L',1);
			}
			
			//PHOSPHOREMIE  --- PHOSPHOREMIE --- PHOSPHOREMIE --- PHOSPHOREMIE --- PHOSPHOREMIE
			if(in_array(34, $idAnalysesBilanElectrolyte)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(35,6,'PHOSPHOREMIE : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[34]['phosphoremie'], 0, ',', ' ').' mg/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(47,6,'( Adultes: 25 � 50  ;  Enfants: 40 � 70 )','BT',0,'L',1);
				
				$this->Cell(2,6,'','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(28,6,number_format($resultats[34]['phosphoremie_mmol'], 2, ',', ' ').' mmol/l','BT',0,'L',1);
					
				$this->SetFont('times','I',6.5);
				$this->Cell(48,6,'( Adultes: 8,07 � 16,15 ; Enfants: 12,92 � 22,61 )','BT',1,'L',1);
					
			}
				
			
				
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- TYPAGE DE L'HEMOGLOBINE --- TYPAGE DE L'HEMOGLOBINE --- TYPAGE DE L'HEMOGLOBINE
		 */
		
		$idAnalysesTypageHemoProteine = $this->getAnalysesTypageHemoProteine();
		if($idAnalysesTypageHemoProteine){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,"TYPAGE DE L'HEMOGLOBINE",'',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//ELECTROPHORESE DE L'HEMOGLOBINE  --- ELECTROPHORESE DE L'HEMOGLOBINE --- 
			if(in_array(44, $idAnalysesTypageHemoProteine)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(65,6,"ELECTROPHORESE DE L'HEMOGLOBINE : ",'BT',0,'L',1);
		
				$this->SetFont('times','I',11);
				$this->Cell(112,6,'','T',0,'L',1);
				
				$x = $this->GetX()-112;
				for($i = 0 ; $i < count($resultats[44])&& $i < 4 ; $i++){
				
					$y = $this->GetY();
					$libElecHem = $resultats[44][$i]['libelle'];
					if(strlen($libElecHem) == 4){
						//Dernier caract�re pour indice
						$dernierCaractere = $libElecHem{strlen($libElecHem)-1};
						$premierePartie = $libElecHem[0].$libElecHem[1].$libElecHem[2];
					}else{
						$dernierCaractere = null;
						$premierePartie = $libElecHem;
					}

					$this->SetFont('times','I',11);
					$this->Text($x, $y+4, $premierePartie);
					$this->Text($x+7, $y+5, $dernierCaractere);
					$this->Text($x+8.5, $y+4, ' : ');
					
					$this->SetFont('times','B',11);
					$this->Text($x+10.5, $y+4, '  '.number_format($resultats[44][$i]['valeur'], 1, ',', ' '));
					$x+=25;
					
					
				}
				
				$this->Cell(3,6,'','T',1,'L',1);
				
				
				if(str_replace(' ','', $resultats[44][0]['conclusion'])){
					$this->Cell(5,6,'','',0,'L',1);
					$this->SetFont('timesbi','U',10);
					$this->Cell(180,6,'Commentaire :','',1,'L',1);
					$this->SetFont('timesb','',11);
					$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[44][0]['conclusion']),0,'J',1);
				}
				
				
			}
			
		}
		
		
		/**
		 * GESTION DES RESULTATS DANS
		 * --- METABOLISME PROTIDIQUE --- METABOLISME PROTIDIQUE --- METABOLISME PROTIDIQUE
		 */
		
		$idAnalysesMetabolismeProtidique = $this->getAnalysesMetabolismeProtidique();
		if($idAnalysesMetabolismeProtidique){
			$indice = 0;
			$this->Ln(1);
			//AFFICHAGE DE L'EN TETE DU TEXTE
			//AFFICHAGE DE L'EN TETE DU TEXTE
			$this->SetDrawColor(220,220,220);
		
			$this->SetFont('times','I',9);
			$this->Cell(35,7,'','',0,'L',0);
			$this->SetFont('times','U',10);
			$this->Cell(115,7,"METABOLISME PROTIDIQUE",'',0,'C',0);
			$this->Cell(35,7,'','',1,'C',0);
			$this->Ln(1);
		
			//ELECTROPHORESE DES PROTEINES  --- ELECTROPHORESE DES PROTEINES ---
			if(in_array(45, $idAnalysesMetabolismeProtidique)){
		
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,"ELECTROPHORESE DES PROTEINES : ",'BT',0,'L',1);
		
				/** Prot�ine totale --- Prot�ine totale --- Prot�ine totale**/
				/** Prot�ine totale --- Prot�ine totale --- Prot�ine totale**/
				$this->SetFont('times','I',11);
				$this->Cell(33,6,'� Prot�ine totale : ','BT',0,'L',1);
		
				$this->SetFont('times','B',11);
				$this->Cell(30,6,number_format($resultats[45]['proteine_totale'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(57,6,'( N: Adultes: 66 � 83 ; Nouveaux n�s: 52 � 91 )','BT',1,'L',1);
				
				
				/** Albumine --- Albumine --- Albumine --- Albumine */
				/** Albumine --- Albumine --- Albumine --- Albumine */
				$this->Cell(15,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Albumine : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['albumine'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['albumine_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 40,2 - 47,6 )','BT',0,'L',1);
				
				/** Partie vide --- Partie vide --- Partie vide **/
				$this->SetFont('times','I',11);
				$this->Cell(23,6,' ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,'','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'','BT',1,'L',1);
				
				
				/** Alpha 1 --- Alpha 1 --- Alpha 1**/
				/** Alpha 1 --- Alpha 1 --- Alpha 1**/
				$this->Cell(15,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Alpha 1 : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['alpha_1'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['alpha_1_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 2,1 - 3,5 )','BT',0,'L',1);
				
				/** Alpha 2 --- Alpha 2 --- Alpha 2**/
				/** Alpha 2 --- Alpha 2 --- Alpha 2**/
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Alpha 2 : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['alpha_2'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['alpha_2_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 5,1 - 8,5 )','BT',1,'L',1);

				
				/** Beta 1 --- Beta 1 --- Beta 1**/
				/** Beta 1 --- Beta 1 --- Beta 1**/
				$this->Cell(15,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Beta 1 : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['beta_1'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['beta_1_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 3,4 - 5,2 )','BT',0,'L',1);
				
				/** Beta 2 --- Beta 2 --- Beta 2**/
				/** Beta 2 --- Beta 2 --- Beta 2**/
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Beta 2 : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['beta_2'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['beta_2_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 2,3 - 4,7 )','BT',1,'L',1);
				
				
				/** Gamma --- Gamma --- Gamma**/
				/** Gamma --- Gamma --- Gamma**/
				$this->Cell(15,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',11);
				$this->Cell(23,6,'� Gamma : ','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['gamma'], 1, ',', ' ').' %','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,number_format($resultats[45]['gamma_abs'], 1, ',', ' ').' g/l','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'( 8,0 - 13,5 )','BT',0,'L',1);
				
				/** Espace vide --- Espace vide --- Espace vide **/
				/** Espace vide --- Espace vide --- Espace vide **/
				
				$this->SetFont('times','I',11);
				$this->Cell(43,6,'','BT',0,'L',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,'','BT',0,'L',1);
				
				$this->SetFont('times','I',7);
				$this->Cell(22,6,'','BT',1,'L',1);
				
				
				
				/** Conclusion --- Conclusion --- Conclusion**/
				/** Conclusion --- Conclusion --- Conclusion**/
				
				if(str_replace(' ','', $resultats[45]['commentaire'])){
					$this->Cell(15,6,'','',0,'L',1);
					$this->SetFont('timesbi','U',10);
					$this->Cell(170,6,'Commentaire :','',1,'L',1);
					
					$this->SetFont('times','B',11);
					$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[45]['commentaire']),'BT','J',1);
				}
				
			}
			

			//PROTEINES TOTAL (PROTIDEMIE) --- PROTEINES TOTAL (PROTIDEMIE) --- PROTEINES TOTAL (PROTIDEMIE)
			if(in_array(48, $idAnalysesMetabolismeProtidique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(60,6,'PROTEINES TOTAL (PROTIDEMIE) : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,6,$resultats[48]['protidemie'].'  g/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(50,6,'( N: Adultes: 66 � 83 ; Nouveaux n�s: 52 � 91 )','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(45,6,'','BT',1,'L',1);
					
			}
			
			
			//ALBUMINEMIE --- ALBUMINEMIE --- ALBUMINEMIE --- ALBUMINEMIE
			if(in_array(46, $idAnalysesMetabolismeProtidique)){
					
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
					
				$this->Ln(0.5);
				$this->SetFont('zap','',11.3);
				$this->Cell(5,6,' +','BT',0,'C',1);
				$this->SetFont('times','',9);
				$this->Cell(45,6,'ALBUMINEMIE : ','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(25,6,$resultats[46]['albuminemie'].' g/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(40,6,'( 35 - 53 )','BT',0,'L',1);
					
				$this->SetFont('times','B',11);
				$this->Cell(40,6,number_format($resultats[46]['albuminemie_umol'],2, ',', ' ').' umol/l','BT',0,'L',1);
					
				$this->SetFont('times','I',7);
				$this->Cell(30,6,'( 507,25 - 768,12 )','BT',1,'L',1);
					
			}
			
			
			
		}
		
		
		
		
		
		
		/**
		 * COMMENTAIRE DU BILAN --- COMMENTAIRE DU BILAN --- COMMENTAIRE DU BILAN
 		 * COMMENTAIRE DU BILAN --- COMMENTAIRE DU BILAN --- COMMENTAIRE DU BILAN
		 */
		
		$commentaireBilan = $this->getCommentaireBilan();
		if($commentaireBilan){
		    
		    /** Espace vide --- Espace vide --- Espace vide **/
		    /** Espace vide --- Espace vide --- Espace vide **/
		    
		    $this->Ln(8);

		    /** Conclusion --- Conclusion --- Conclusion**/
		    /** Conclusion --- Conclusion --- Conclusion**/
		    
		    if(str_replace(' ','', $commentaireBilan)){
		        $this->SetFillColor(249,249,249);
		        
		        $this->SetFont('zap','',11.3);
		        $this->Cell(4,6,' a','LT',0,'C',1);
		        $this->SetFont('timesb','U',10);
		        $this->Cell(181,6,'COMMENTAIRE DU BILAN','RT',1,'L',1);
		        	
		        $this->SetFont('times','',11);
		        $this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $commentaireBilan['conclusion_bilan']),'LRB','J',1);
		    }
		}
		
		
	
	}
	
	
	
	
	
	function AfficherResultatsTypageHemoglobine(){
		$controle = new DateHelper();
		$this->AddFont('symb','','symbol.php');
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
		
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
		
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
		
			if($idanalyse == 68){
				$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
				$idAnalyses[$idanalyse]          = $idanalyse;
				$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
				$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
			}
		}
		
		//Date de prel�vement
		$datePrelevement = $infosAnalyseDemande[68]['DateHeurePrelevement'];
		
		//Affichage des infos sur le biologiste et le technicien
		$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[68]['DateEnregistrementResultat']);
		$prenomNomTechnicien = $infosAnalyseDemande[68]['Prenom'].' '.$infosAnalyseDemande[68]['Nom'];
		$prenomNomBiologiste = $infosAnalyseDemande[68]['PrenomValidateur'].' '.$infosAnalyseDemande[68]['NomValidateur'];
		
		$this->SetFont('times','',8);
		//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
		
		//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
		$this->Cell(90,-1,'','',1,'L',0);
		
		$this->Ln(5);
		
		//AFFICHAGE DE L'EN TETE DU TEXTE
		//AFFICHAGE DE L'EN TETE DU TEXTE
		$this->SetFillColor(249,249,249);
		$this->SetDrawColor(220,220,220);
		
		$this->SetFont('times','I',9);
		$this->Cell(35,7,'D�pistage n�onatal','',0,'L',0);
		$this->SetFont('times','U',10);
		$this->Cell(115,7,"TYPAGE DE L'HEMOGLOBINE",'',0,'C',0);
		$this->Cell(35,7,'','',1,'C',0);
		
		$this->Ln(3);
		
		//mat�riel utilis� --- mat�riel utilis� --- mat�riel utilis�
		$this->SetFont('zap','',11.3);
		$this->Cell(4,6,' ^','BT',0,'C',1);
		$this->SetFont('times','',11);
		$this->Cell(181,6,'Type de mat�riel utilis� : '.$resultats[68]['type_materiel'],'BT',1,'L',1);
		
		$this->Ln(4);
		
		$indice = 0;
		$this->Ln(1);
		
		$idAnalysesTypageHemoglobine = $this->getAnalysesTypageHemoglobine();
		if(in_array(68, $idAnalysesTypageHemoglobine)){
		
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
		
			$this->Ln(0.5);
			$this->SetFont('zap','',11.3);
			$this->Cell(45,6,' +','BT',0,'R',1);
			$this->SetFont('times','',9);
			$this->Cell(35,6,'PROFIL DU PATIENT : ','BT',0,'L',1);
		
			$this->SetFont('times','B',11);
			$this->Cell(60,6,iconv ('UTF-8' , 'windows-1252', $resultats[68]['Designation_stat'].''),'BT',0,'L',1);
		
			$this->SetFont('symb','',11);
			$this->Cell(45,6,'','BT',0,'L',1);
		
		}
		
		
	}
	
	
	
	function AfficherResultatsSerologieHIV(){
		$controle = new DateHelper();
		$this->AddFont('symb','','symbol.php');
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
	
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
	
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
	
			if($idanalyse == 64){
				$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
				$idAnalyses[$idanalyse]          = $idanalyse;
				$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
				$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
			}
		}
	
		//Date de prel�vement
		$datePrelevement = $infosAnalyseDemande[64]['DateHeurePrelevement'];
	
		//Affichage des infos sur le biologiste et le technicien
		$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[64]['DateEnregistrementResultat']);
		$prenomNomTechnicien = $infosAnalyseDemande[64]['Prenom'].' '.$infosAnalyseDemande[64]['Nom'];
		$prenomNomBiologiste = $infosAnalyseDemande[64]['PrenomValidateur'].' '.$infosAnalyseDemande[64]['NomValidateur'];
	
		$this->SetFont('times','',8);
		//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
	
		//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
		$this->Cell(90,-1,'','',1,'L',0);
	
		$this->Ln(5);
	
		//AFFICHAGE DE L'EN TETE DU TEXTE
		//AFFICHAGE DE L'EN TETE DU TEXTE
		$this->SetFillColor(249,249,249);
		$this->SetDrawColor(220,220,220);
	
		$this->SetFont('times','I',9);
		$this->Cell(35,7,'','',0,'L',0);
		$this->SetFont('times','U',10);
		$this->Cell(115,7,"SEROLOGIE HIV",'',0,'C',0);
		$this->Cell(35,7,'','',1,'C',0);
	
		$this->Ln(3);
	
		//mat�riel utilis� --- mat�riel utilis� --- mat�riel utilis�
		$this->SetFont('zap','',11.3);
		$this->Cell(4,6,' ^','BT',0,'C',1);
		$this->SetFont('times','',11);
		$this->Cell(181,6,'Type de mat�riel utilis� : '.$resultats[64]['type_materiel'],'BT',1,'L',1);
	
		$this->Ln(4);
	
		$indice = 0;
		$this->Ln(1);
	
		$idAnalysesSerologieHIV = $this->getAnalysesSerologieHIV();
		if(in_array(64, $idAnalysesSerologieHIV)){
	
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	
			$this->Ln(0.5);
			$this->SetFont('zap','',11.3);
			$this->Cell(45,6,' +','BT',0,'R',1);
			$this->SetFont('times','',11);
			$this->Cell(35,6,'R�sultat : ','BT',0,'L',1);
	
			$resultatHiv = 'Positif';
			if($resultats[64]['hiv'] == 'Negatif'){ $resultatHiv = 'N�gatif'; }
			$this->SetFont('times','B',11);
			$this->Cell(20,6,$resultatHiv,'BT',0,'L',1);
			
			$this->SetFont('times','I',11);
			$this->Cell(20,6,'Typage :','BT',0,'R',1);
	
			
			$typageHiv = '';
			if($resultats[64]['hiv_typage']){ 
				$typageHiv = $resultats[64]['hiv_typage']; 
				if($typageHiv == 'indetermine'){ $typageHiv = 'Ind�termin�'; }
			}
			$this->SetFont('times','B',11);
			$this->Cell(65,6,$typageHiv,'BT',0,'L',1);
	
		}
	
	
	}
	
	
	
	
	
	
	function AfficherResultatsPV(){
	    $controle = new DateHelper();
	    $this->AddFont('symb','','symbol.php');
	    $this->AddFont('zap','','zapfdingbats.php');
	    $this->AddFont('timesb','','timesb.php');
	    $this->AddFont('timesi','','timesi.php');
	    $this->AddFont('times','','times.php');
	    $this->AddFont('bordure','','borderpi1515-9.php');
	
	    $resultats = $this->getResultatsAnalysesDemandees();
	    $listeAnalysesDemandees = $this->getAnalysesDemandees();
	    $infosAnalyseDemande = array();
	
	    for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
	        $idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
	
	        if($idanalyse == 65 || $idanalyse == 74){
	            $analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
	            $idAnalyses[$idanalyse]          = $idanalyse;
	            $typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
	            $infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
	        }
	    }
	
	    if(array_key_exists(65, $infosAnalyseDemande) || array_key_exists(74, $infosAnalyseDemande)){
	    	
	    	$idanalyse = (array_key_exists(65, $infosAnalyseDemande))? 65:74;
	    	
	    	//Date de prel�vement
	    	$datePrelevement = $infosAnalyseDemande[$idanalyse]['DateHeurePrelevement'];
	    	
	    	//Affichage des infos sur le biologiste et le technicien
	    	$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[$idanalyse]['DateEnregistrementResultat']);
	    	$prenomNomTechnicien = $infosAnalyseDemande[$idanalyse]['Prenom'].' '.$infosAnalyseDemande[$idanalyse]['Nom'];
	    	$prenomNomBiologiste = $infosAnalyseDemande[$idanalyse]['PrenomValidateur'].' '.$infosAnalyseDemande[$idanalyse]['NomValidateur'];
	    	
	    	$this->SetFont('times','',8);
	    	//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
	    	$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
	    	
	    	//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
	    	$this->Cell(90,-1,'','',1,'L',0);
	    }
	    
	    
	    $this->Ln(5);
	
	    //AFFICHAGE DE L'EN TETE DU TEXTE
	    //AFFICHAGE DE L'EN TETE DU TEXTE
	    $this->SetFillColor(249,249,249);
	    $this->SetDrawColor(220,220,220);
	

	    $this->SetTextColor(123, 125, 125);
	    $this->SetFont('bordure','',12);
	    $x = 87.5;
	    $y = 68.3;
	    
	    if($idanalyse == 65){
	    	$this->Text($x-8, $y+3, 'GKKKKKKKKKK');
	    	$this->Text($x+38.5, $y+3, 'H');
	    	$this->Text($x-8, $y+7, 'JMMMMMMMMMM');
	    	$this->Text($x+38.5, $y+7, 'I');
	    	$this->SetTextColor(0,0,0);
	    	
	    	$this->SetFont('times','',11);
	    	$this->Cell(35,7,'','',0,'L',0);
	    	$this->Cell(115,6,"PRELEVEMENT VAGINAL",'',0,'C',0);
	    	$this->Cell(35,7,'','',1,'C',0);

	    }elseif($idanalyse == 74){
	    	$this->Text($x-26.5, $y+3, 'GKKKKKKKKKKKKKKKKKKK');
	    	$this->Text($x+57.7, $y+3, 'H');
	    	$this->Text($x-26.5, $y+7, 'JMMMMMMMMMMMMMMMMMMM');
	    	$this->Text($x+57.7, $y+7, 'I');
	    	$this->SetTextColor(0,0,0);
	    	
	    	$this->SetFont('times','',11);
	    	$this->Cell(35,7,'','',0,'L',0);
	    	$this->Cell(115,6,"Pr�l�vement vaginal + Mychoplasmes + Chlamydia",'',0,'C',0);
	    	$this->Cell(35,7,'','',1,'C',0);
	    }
	
	    
	    
	    
	    
	    $this->Ln(3);
	
	    //mat�riel utilis� --- mat�riel utilis� --- mat�riel utilis�
	    $this->SetFont('zap','',11.3);
	    $this->Cell(4,6,' ^','BT',0,'C',1);
	    $this->SetFont('times','',11);
	    $this->Cell(181,6,'Type de mat�riel utilis� : '.iconv ('UTF-8' , 'windows-1252', $resultats[65]['type_materiel']),'BT',1,'L',1);
	
	    $this->Ln(5);
	    $indice = 0;
	    
	    
	    $idAnalysesSerologiePV = $this->getAnalysesSerologiePV();
	    
	    $listeAspectPertesAbondance = array(0=>'', 1=>'Peu abondantes', 2=>'Abondantes', 3=>'Tr�s abondantes');
	    $listeAspectPertesCouleurs  = array(0=>'', 1=>'Blanch�tres', 2=>'Stri�es de sang', 3=>'Caillebott�es', 'Marron');
	    $listeAspectPertesOdeurs    = array(0=>'', 1=>'F�tides', 2=>'Non f�tides');
	    $listeAspectOrgane          = array(0=>'', 1=>'Col sain', 2=>'Col inflamm�', 3=>'Col saingnant au contact', 4=>'Col l�g�rement inflamm�');
	    
	    $listeLeucocytesChamp       = array(0=>'', 1=>'Pr�sents', 2=>'Absents');
	    $listeHematiesChamp         = array(0=>'', 1=>'Pr�sentes', 2=>'Absentes');
	    $listeCellulesEpitheliales  = array(0=>'', 1=>'Pr�sentes', 2=>'Absentes');
	    $listeTrichomonasVaginalis  = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeLevuresFilamentsMyceliens = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeGardnerellaVaginalis      = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeMobiluncusSpp = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeClueCells     = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeLactobacillus = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
	    $listeAutreFlore    = array(0=>'', 1=>'Bacilles � Gram n�gatif', 2=>'Bacilles � Gram positif', 3=>'Cocci � Gram positif', 4=>'Diplocoques � Gram n�gatif');
	    $listeCulture       = array(0=>'', 1=>'Positive', 2=>'N�gative');
	    $listeIdentificationCulture = array(0=>'', 1=>'Candida albicans', 2=>'Escherichia coli', 3=>'Staphylococcus aureus');
	    $listeRechercheDirecteMycoplasmes = array(0=>'', 1=>'Positive', 2=>'N�gative');
	    $listeRechercheDirecteAntigeneChlamydia = array(0=>'', 1=>'Positive', 2=>'N�gative');
	    $listeIdentificationRdm = array(0=>'', 1=>'Positive', 2=>'N�gative');
	    $listeFlore = array(0=>'', 1=>'Type 1', 2=>'Type 2', 3=>'Type 3', 4=>'Type 4');
	    $listeIdentificationRdmPositive = array(0=>'', 1=>'Ureaplasma urealyticum', 2=>'Mycoplasma hominis');
	    
	    if(in_array(65, $idAnalysesSerologiePV)){
	
	        /**
	         * Titre examen macroscopique
	         */
	        $this->SetFont('zap','',10);
	        $this->Cell(4,6,' m','',0,'C',0);
	        $this->SetFont('timesi','U',11);
	        $this->Cell(181,6,'Examen macroscopique','',1,'L',0);
	        /**
	         * ==========================
	         */
	        
	        /*
	         * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
	         */
	        $indice = 0;
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Aspect des pertes (Abondance) :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexAPA = (int)$resultats[65]['aspect_pertes_abondance'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeAspectPertesAbondance[$indexAPA],'BT',0,'L',1);
	        
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Aspect des pertes (Odeur) :','BT',0,'R',1);
	        
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexAPO = (int)$resultats[65]['aspect_pertes_odeurs'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeAspectPertesOdeurs[$indexAPO],'BT',1,'L',1);
	        
	        /*
	         * Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
	         */
	        $this->Ln(0.5);
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	         
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Aspect des pertes (Couleur) :','BT',0,'R',1);
	         
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexAPC = (int)$resultats[65]['aspect_pertes_couleurs'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeAspectPertesCouleurs[$indexAPC],'BT',0,'L',1);
	         
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Aspect organe (Col) :','BT',0,'R',1);
	         
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexAO = (int)$resultats[65]['aspect_organe_col'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeAspectOrgane[$indexAO],'BT',1,'L',1);
	       
	        
	        /**
	         * Titre examen microscopique
	         */
	        $this->Ln(2);
	        $this->SetFont('zap','',10);
	        $this->Cell(4,6,' m','',0,'C',0);
	        $this->SetFont('timesi','U',11);
	        $this->Cell(181,6,'Examen microscopique','',1,'L',0);
	        /**
	         * ==========================
	         */
	        
	        /*
	         * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
	         */
	        $indice = 0;
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	         
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Leucocytes :','BT',0,'R',1);
	         
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexLC = (int)$resultats[65]['leucocytes_champ'];
	        $this->SetFont('times','B',11);
	        $this->Cell(22,6,$listeLeucocytesChamp[$indexLC],'BT',0,'L',1);
	        
	        /*3) Troisi�me colonne ==== Pour les r�sultats*/
	        $valChamp = $resultats[65]['leucocytes_champ_valeur'];
	        if($valChamp){
	            $this->SetFont('times','B',11);
	            $this->Cell(8,6,$valChamp,'BT',0,'R',1);
	            $this->SetFont('timesi','',10);
	            $this->Cell(18,6,'/champ','BT',0,'L',1);
	        }else{
	            $this->Cell(26,6,'','BT',0,'R',1);
	        }
	        
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'H�maties :','BT',0,'R',1);
	         
	        /*5) Cinqui�me colonne ===== pour les r�f�rences */
	        $indexHC = (int)$resultats[65]['hematies_champ'];
	        $this->SetFont('times','B',11);
	        $this->Cell(22,6,$listeHematiesChamp[$indexHC],'BT',0,'L',1);
	        
	        /*6) Sixi�me colonne ===== pour les r�f�rences */
	        $valChampHem = $resultats[65]['hematies_champ_valeur'];
	        if($valChampHem){
	            $this->SetFont('times','B',11);
	            $this->Cell(8,6,$valChampHem,'BT',0,'R',1);
	            $this->SetFont('timesi','',10);
	            $this->Cell(13,6,'/champ','BT',1,'L',1);
	        }else{
	            $this->Cell(21,6,'','BT',1,'R',1);
	        }
	        
	        
	        /*
	         * Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
	         */
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Cellules �pith�liales :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexCE = (int)$resultats[65]['cellules_epitheliales'];
	        $this->SetFont('times','B',11);
	        $this->Cell(22,6,$listeCellulesEpitheliales[$indexCE],'BT',0,'L',1);
	        
	        /*3a) Troisi�me colonne A ==== Pour les r�sultats*/
	        $valChampCEV = $resultats[65]['cellules_epitheliales_champ_valeur'];
	        if($valChampCEV){
	            $this->SetFont('times','B',11);
	            $this->Cell(8,6,$valChampCEV,'BT',0,'R',1);
	            $this->SetFont('timesi','',10);
	            $this->Cell(18,6,'/champ','BT',0,'L',1);
	        }else{
	            $this->Cell(26,6,'','BT',0,'R',1);
	        }
	        
	        /*3b) Troisi�me colonne B ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Trichomonas vaginalis :','BT',0,'R',1);
	        
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexTV = (int)$resultats[65]['trichomonas_vaginalis'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeTrichomonasVaginalis[$indexTV],'BT',1,'L',1);
	        
	        /*
	         * Troisi�me ligne --- Troisi�me ligne --- Troisi�me ligne
	         */
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Levures','BT',0,'R',1);
	         
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexLFM = (int)$resultats[65]['levures'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeLevuresFilamentsMyceliens[$indexLFM],'BT',0,'L',1);
	         
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Filaments myc�liens : :','BT',0,'R',1);
	         
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexFM = (int)$resultats[65]['filaments_myceliens'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeLevuresFilamentsMyceliens[$indexFM],'BT',1,'L',1);
	        
	        /*
	         * Quatri�me ligne --- Quatri�me ligne --- Quatri�me ligne
	         */
	        
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Lactobacillus :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexL = (int)$resultats[65]['lactobacillus'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeLactobacillus[$indexL],'BT',0,'L',1);
	        
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Gardnerella vaginalis :','BT',0,'R',1);
	        
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexGV = (int)$resultats[65]['gardnerella_vaginalis'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeGardnerellaVaginalis[$indexGV],'BT',1,'L',1);
	        
	        
	        /*
	         * Cinqui�me ligne --- Cinqui�me ligne --- Cinqui�me ligne
	         */
	        
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Mobiluncus spp :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexMS = (int)$resultats[65]['mobiluncus_spp'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeMobiluncusSpp[$indexMS],'BT',0,'L',1);
	        
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','',10);
	        $this->Cell(42,6,'Clue cells :','BT',0,'R',1);
	        
	        /*4) Quatri�me colonne ===== pour les r�f�rences */
	        $indexCC = (int)$resultats[65]['clue_cells'];
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,$listeClueCells[$indexCC],'BT',1,'L',1);
	        
	        /*
	         * Cinqui�me ligne --- Cinqui�me ligne --- Cinqui�me ligne
	         */
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Flore :','BT',0,'R',1);
	         
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexFlore = (int)$resultats[65]['flore'];
	        $this->SetFont('times','B',11);
	        $this->Cell(16,6,$listeFlore[$indexFlore],'BT',0,'L',1);
	         
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $flore_note = $resultats[65]['flore_note'];
	        if($flore_note){
	            $this->SetFont('zap','',10.5);
	            $this->Cell(6,6,'�','BT',0,'L',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(111,6,$flore_note,'BT',1,'L',1);
	        }else{
	            $this->Cell(117,6,'','BT',1,'L',1);
	        }
	        
	        /*
	         * Sixi�me ligne --- Sixi�me ligne --- Sixi�me ligne
	         */
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        $this->Ln(0.5);
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Autre flore :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexAF = (int)$resultats[65]['autre_flore'];
	        $cocciChoix1 = $resultats[65]['autre_flore_cocci_pos_Choix1'];
	        $cocciChoix2 = $resultats[65]['autre_flore_cocci_pos_Choix2'];
	        if($cocciChoix1==1 && $cocciChoix2==1){
	            $compInfos = ' en amas et en chainettes';
	        }elseif ($cocciChoix1==1){
	            $compInfos = ' en amas';
	        }elseif ($cocciChoix2==1){
	            $compInfos = ' en chainettes';
	        }else{
	            $compInfos = '';
	        }
	        
	        $this->SetFont('times','B',11);
	        $this->Cell(90,6,$listeAutreFlore[$indexAF].$compInfos,'BT',0,'L',1);
	        
	        /*3) Troisi�me colonne ===== pour les r�f�rences */
	        $this->SetFont('times','B',11);
	        $this->Cell(43,6,'','BT',1,'L',1);
	        
	        /**
	         * Titre culture
	         */
	        $this->Ln(2);
	        $this->SetFont('zap','',10);
	        $this->Cell(4,6,' m','',0,'C',0);
	        $this->SetFont('timesi','U',11);
	        $this->Cell(181,6,'Culture','',1,'L',0);
	        /**
	         * ==========================
	         */
	         
	        /*
	         * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
	         */
	        $indice = 0;
	        if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        
	        /*1) Premi�re colonne ==== Pour les libell�s*/
	        $this->SetFont('zap','',10.5);
	        $this->Cell(5,6,'','BT',0,'L',1);
	        $this->SetFont('times','',10);
	        $this->Cell(47,6,'Culture :','BT',0,'R',1);
	        
	        /*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        $indexC = (int)$resultats[65]['culture'];
	        $this->SetFont('times','B',11);
	        $this->Cell(48,6,$listeCulture[$indexC],'BT',0,'L',1);
	        
	        if($indexC == 1){
	        	/*3) Troisi�me colonne ===== pour les r�f�rences */
	        	$this->SetFont('times','',10);
	        	$this->Cell(19,6,'Identification :','BT',0,'R',1);
	        	 
	        	/*4) Quatri�me colonne ===== pour les r�f�rences */
	        	$indexIC = (int)$resultats[65]['identification_culture'];
	        	$this->SetFont('times','B',11);
	        	$this->Cell(66,6,$listeIdentificationCulture[$indexIC],'BT',1,'L',1);
	        	
	        	/*Antibiogramme en pi�ce jointe*/
	        	if($indexIC != 1){
	        		$this->Cell(100,6,'','',0,'C',0);
	        		$this->SetFont('zap','',12);
	        		$this->Cell(4,6,'b','',0,'C',0);
	        		$this->SetFont('timesi','',11);
	        		$this->Cell(80,6,'(ABG en pi�ce jointe)','',1,'L',0);
	        	}
	        	
	        }else{
	        	$this->Cell(85,6,'','BT',1,'R',1);
	        }
	        
	        
	        if($idanalyse == 74){
	        	
	        	/**
	        	 * Titre Recherches particuli�res
	        	 */
	        	$indexRDAC = (int)$resultats[65]['recherche_directe_antigene_chlamydia'];
	        	$indexRDM = (int)$resultats[65]['recherche_directe_mycoplasmes'];
	        	if($indexRDAC || $indexRDM){
	        		$this->Ln(2);
	        		$this->SetFont('zap','',10);
	        		$this->Cell(4,6,' m','',0,'C',0);
	        		$this->SetFont('timesi','U',11);
	        		$this->Cell(181,6,'Recherches particuli�res','',1,'L',0);
	        	}
	        	/**
	        	 * ==========================
	        	 */
	        	 
	        	/*
	        	 * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
	        	*/
	        	$enterPremierLine = 0;
	        	if($indexRDAC){
	        		$indice = 0;
	        		$enterPremierLine = 1;
	        		if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        		 
	        		/*1) Premi�re colonne ==== Pour les libell�s*/
	        		$this->SetFont('zap','',10.5);
	        		$this->Cell(5,6,'','BT',0,'L',1);
	        		$this->SetFont('times','',10);
	        		$this->Cell(65,6,"Recherche d'Ag de Chlamydia trachomatis :",'BT',0,'R',1);
	        		 
	        		/*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        		$this->SetFont('times','B',11);
	        		$this->Cell(43,6,$listeRechercheDirecteAntigeneChlamydia[$indexRDAC],'BT',0,'L',1);
	        		 
	        		/*3) Troisi�me colonne ===== pour les r�f�rences */
	        		$this->SetFont('times','',10);
	        		$this->Cell(47,6,'','BT',0,'R',1);
	        		 
	        		/*4) Quatri�me colonne ===== pour les r�f�rences */
	        		$this->SetFont('times','B',11);
	        		$this->Cell(25,6,'','BT',1,'L',1);
	        	}
	        	 
	        	/*
	        	 * Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
	        	*/
	        	if($indexRDM){
	        		if($enterPremierLine == 0){ $indice = 0; }
	        		if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	        		$this->Ln(0.5);
	        		 
	        		if($indexRDM && $indexRDM == 1){
	        			/*
	        			 * Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
	        			*/
	        	
	        			/*1) Premi�re colonne ==== Pour les libell�s*/
	        			$this->SetFont('zap','',10.5);
	        			$this->Cell(5,6,'','T',0,'L',1);
	        			$this->SetFont('times','',10);
	        			$this->Cell(65,6,'Recherche directe de Mycoplasmes :','T',0,'R',1);
	        			 
	        			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        			$this->SetFont('times','B',11);
	        			$this->Cell(18,6,$listeRechercheDirecteMycoplasmes[$indexRDM],'T',0,'L',1);
	        			 
	        			/*3) Troisi�me colonne ===== pour les r�f�rences */
	        			$this->SetFont('zap','',10.5);
	        			$this->Cell(6,6,'�','T',0,'L',1);
	        			$this->SetFont('times','',10);
	        			$this->Cell(12,6,'Titre :','T',0,'R',1);
	        	
	        			$this->AddFont('math','','maths.php');
	        			$this->SetFont('math','',12);
	        			$this->Cell(4,6,'8','T',0,'L',1);
	        	
	        			$x = $this->GetX(); $y = $this->GetY();
	        			$this->SetFont('times','B',11);
	        			$this->Cell(8,6,'10 ','T',0,'L',1);
	        			$this->Text($x+5.5, $y+3, '4');
	        			$this->Cell(67,6,' ucc/ml','T',1,'L',1);
	        	
	        			/*
	        			 * Troisi�me ligne --- Troisi�me ligne --- Troisi�me ligne
	        			*/
	        			 
	        			/*1) Premi�re colonne ==== Pour les libell�s*/
	        			$this->SetFont('zap','',10.5);
	        			$this->Cell(5,6,'','B',0,'L',1);
	        			$this->SetFont('times','',10);
	        			$this->Cell(65,6,'Identification :','B',0,'R',1);
	        	
	        			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        			$identifChoix1 = (int)$resultats[65]['identification_rdm_positive_choix1'];
	        			$identifChoix2 = (int)$resultats[65]['identification_rdm_positive_choix2'];
	        			if($identifChoix1==1 && $identifChoix2==1){
	        				$infoChoix1Et2 = 'Ureaplasma urealyticum + Mycoplasma hominis';
	        			}elseif ($identifChoix1==1){
	        				$infoChoix1Et2 = 'Ureaplasma urealyticum';
	        			}elseif ($identifChoix2==1){
	        				$infoChoix1Et2 = 'Mycoplasma hominis';
	        			}else{
	        				$infoChoix1Et2 = '';
	        			}
	        			$this->SetFont('times','B',11);
	        			$this->Cell(97,6,$infoChoix1Et2,'B',0,'L',1);
	        	
	        			/*3) Troisi�me colonne ===== pour les r�f�rences */
	        			$this->Cell(18,6,'','B',1,'L',1);
	        	
	        		}else{
	        			/*1) Premi�re colonne ==== Pour les libell�s*/
	        			$this->SetFont('zap','',10.5);
	        			$this->Cell(5,6,'','BT',0,'L',1);
	        			$this->SetFont('times','',10);
	        			$this->Cell(65,6,'Recherche directe de Mycoplasmes :','BT',0,'R',1);
	        	
	        			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
	        			$this->SetFont('times','BT',11);
	        			$this->Cell(18,6,$listeRechercheDirecteMycoplasmes[$indexRDM],'BT',0,'L',1);
	        	
	        			/*3) Troisi�me colonne ===== pour les r�f�rences */
	        			$this->Cell(97,6,'','BT',1,'L',1);
	        	
	        		}
	        	
	        	}
	        	 
	        }
	


	        
	        
	        /**
	         * 
	         */
	        if(str_replace(' ','', $resultats[65]['commentaire']) || str_replace(' ','', $resultats[65]['precision_commentaire'])){
	        	$this->SetFillColor(249,249,249);
	        	
	        	$this->Ln(2);
	        	$this->SetFont('zap','',10);
	        	$this->Cell(4,6,' ','',0,'C',0);
	        	$this->SetFont('timesi','U',11);
	        	$this->Cell(181,6,'Conclusion','',1,'L',1);
	        }
	        /**
	         * ==========================
	         */
	        
	        
	        /**
	         * Partie du commentaire de la conclusion
	         */
	        if(str_replace(' ','', $resultats[65]['commentaire'])){
	            
	            $this->SetFont('times','',10.5);
	            $this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[65]['commentaire']),0,'J',1);
	        }
	        
	        /**
	         * Pr�cision sur la conclusion
	         */
	        if(str_replace(' ','', $resultats[65]['precision_commentaire'])){
	        	 
	        	$this->SetFillColor(249,249,249);
	        	$this->Ln(2);
	        	$this->SetFont('times','',10.5);
	        	$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[65]['precision_commentaire']),0,'J',1);
	        }
	        
	        $this->Ln(6);
	        $this->SetFont('timesi','U',9);
	        $this->Cell(165,6,'Cachet et signature','',0,'R',0);
	        $this->Cell(20,6,'','',0,'C',0);
	        
	        
	        /**
	         * Affichage de l'antibiogramme --- Affichage de l'antibiogramme
	         */
	        $indexIC = (int)$resultats[65]['identification_culture'];
	        if( $resultats[65]['identification_culture'] != 0 && $indexIC != 1){ // Ne pas afficher si c'est un "Candida albicans"
	            $iddemande = $resultats[65]['idresultat_demande_analyse'];
	            
	            $resultatsABGPV = $this->getResultatsAntiBioGrammePVDemande();
	            $this->affichageResultatsAntibiogrammePV($resultatsABGPV);
	        }
	    }
	    
	    
	    
	
	}
	
	public function baseUrl(){
	    $baseUrl = $_SERVER['REQUEST_URI'];
	    $tabURI  = explode('public', $baseUrl);
	    return $tabURI[0];
	}
	
	function AfficherResultatsAntibiogrammePV($resultatsABGPV){
	    
	    $controle = new DateHelper();
	    $this->AddFont('symb','','symbol.php');
	    $this->AddFont('zap','','zapfdingbats.php');
	    $this->AddFont('timesb','','timesb.php');
	    $this->AddFont('timesi','','timesi.php');
	    $this->AddFont('times','','times.php');
	    //$this->AddFont('bordure','','borderpi1515-9.php');
	    
	    $resultats = $this->getResultatsAnalysesDemandees();
	    $listeAnalysesDemandees = $this->getAnalysesDemandees();
	    $infosAnalyseDemande = array();
	    
	    for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
	        $idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
	    
	        if($idanalyse == 65){
	            $analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
	            $idAnalyses[$idanalyse]          = $idanalyse;
	            $typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
	            $infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
	        }
	    }
	    
	    //Date de prel�vement
	    $datePrelevement = $infosAnalyseDemande[65]['DateHeurePrelevement'];
	    
	    //Affichage des infos sur le biologiste et le technicien
	    $dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[65]['DateEnregistrementResultat']);
	    $prenomNomTechnicien = $infosAnalyseDemande[65]['Prenom'].' '.$infosAnalyseDemande[65]['Nom'];
	    $prenomNomBiologiste = $infosAnalyseDemande[65]['PrenomValidateur'].' '.$infosAnalyseDemande[65]['NomValidateur'];
	    
	    $this->SetFont('times','',8);
	    //$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
	    $this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
	    
	    //$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
	    $this->Cell(90,-1,'','',1,'L',0);
	    
	    $this->Ln(5);
	    
	    //AFFICHAGE DE L'EN TETE DU TEXTE
	    //AFFICHAGE DE L'EN TETE DU TEXTE
	    $this->SetFillColor(249,249,249);
	    $this->SetDrawColor(202,207,210);
	    
	    $this->SetFont('times','I',10);
	    $this->Cell(75,7,'','',0,'L',0);
	    
	    $this->SetTextColor(123, 125, 125);
	    
	    $this->SetFont('bordure','',12);
	    $x = $this->GetX(); $y = $this->GetY();
	    $this->Text($x-1, $y+3, 'GKKKKKKK');
	    $this->Text($x+32, $y+3, 'H');
	    $this->Text($x-1, $y+7, 'JMMMMMMM');
	    $this->Text($x+32, $y+7, 'I');
	    
	    $this->SetTextColor(0,0,0);
	    
	    $this->SetFont('times','',11);
	    $this->Cell(35,6,"ANTIBIOGRAMME",'',0,'C',0);
	    
	    $this->Cell(75,7,'','',1,'C',0);
	    
	    $this->Ln(3);
	    
	    $listeIdentificationCulture = array(0=>'', 1=>'Candida albicans', 2=>'Escherichia coli', 3=>'Staphylococcus aureus');
	    $indexIC = (int)$resultats[65]['identification_culture'];
	    
	    //souche isol�e -- souche isol�e -- souche isol�e
	    $this->SetFont('zap','',11.3);
	    $this->Cell(4,6,' ^','BT',0,'C',1);
	    $this->SetFont('times','',11);
	    $this->Cell(181,6,'Souche isol�e (PV) : '.iconv ('UTF-8' , 'windows-1252', $listeIdentificationCulture[$indexIC]),'BT',1,'L',1);
	    
	    $this->Ln(5);
	    $indice = 0;
	     
	    
	    /** Affichage des donn�es de l'antibiogramme **/
	    $resultatsPossibles = array(1=>'R�sistante', 2=>'Sensible', 3=> 'Interm�diaire');
	    
	    
	    if($resultatsABGPV){
	        
	        $groupeDESBLactamines = array();
	        if($resultatsABGPV['ampicillineAM']){ $groupeDESBLactamines[] = array('libelle'  => 'Ampicilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['ampicillineAM']], 'regroup' => 1 ); }
	        if($resultatsABGPV['amoxillineAMX']){ $groupeDESBLactamines[] = array('libelle'  => 'Amoxilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['amoxillineAMX']], 'regroup' => 1); }
	        if($resultatsABGPV['ticarcillineTIC']){ $groupeDESBLactamines[] = array('libelle'  => 'Ticarcilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['ticarcillineTIC']], 'regroup' => 1); }
	        if($resultatsABGPV['piperacillinePIP']){ $groupeDESBLactamines[] = array('libelle'  => 'Piperacilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['piperacillinePIP']], 'regroup' => 1); }
	        if($resultatsABGPV['acideClavulaniqueAmoxicillineAMC']){ $groupeDESBLactamines[] = array('libelle'  => 'Amoxicilline + Acide clavulanique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideClavulaniqueAmoxicillineAMC']], 'regroup' => 1); }
	        if($resultatsABGPV['ticAcClavTCC']){ $groupeDESBLactamines[] = array('libelle'  => 'Ticarcilline + Acide clavulanique', 'resultat' => $resultatsPossibles[$resultatsABGPV['ticAcClavTCC']], 'regroup' => 1); }
	        if($resultatsABGPV['ertapenemeETP']){ $groupeDESBLactamines[] = array('libelle'  => 'Ertap�n�me', 'resultat' => $resultatsPossibles[$resultatsABGPV['ertapenemeETP']], 'regroup' => 1); }
	        if($resultatsABGPV['imipenemeIPM']){ $groupeDESBLactamines[] = array('libelle'  => 'Imip�n�me', 'resultat' => $resultatsPossibles[$resultatsABGPV['imipenemeIPM']], 'regroup' => 1); }
	        if($resultatsABGPV['oxacillineOX']){ $groupeDESBLactamines[] = array('libelle'  => 'Oxacilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['oxacillineOX']], 'regroup' => 1); }
	        if($resultatsABGPV['penicillineP']){ $groupeDESBLactamines[] = array('libelle'  => 'P�nicilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['penicillineP']], 'regroup' => 1); }
	        if($resultatsABGPV['cefalotineCF']){ $groupeDESBLactamines[] = array('libelle'  => 'C�falotine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefalotineCF']], 'regroup' => 1); }
	        if($resultatsABGPV['cefoxitineFOX']){ $groupeDESBLactamines[] = array('libelle'  => 'C�foxitine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefoxitineFOX']], 'regroup' => 1); }
	        if($resultatsABGPV['piperacillineTazobactamePPT']){ $groupeDESBLactamines[] = array('libelle'  => 'Pip�racilline tazobactame', 'resultat' => $resultatsPossibles[$resultatsABGPV['piperacillineTazobactamePPT']], 'regroup' => 1); }
	        if($resultatsABGPV['cefotaximeCTX']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fotaxime', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefotaximeCTX']], 'regroup' => 1); } 
	        if($resultatsABGPV['cefsulodineCFS']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fsulodine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefsulodineCFS']], 'regroup' => 1); }
	        if($resultatsABGPV['CFP']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fop�razone', 'resultat' => $resultatsPossibles[$resultatsABGPV['CFP']], 'regroup' => 1); }
	        if($resultatsABGPV['ceftazidimeCAZ']){ $groupeDESBLactamines[] = array('libelle'  => 'C�ftazidime', 'resultat' => $resultatsPossibles[$resultatsABGPV['ceftazidimeCAZ']], 'regroup' => 1); }
	        if($resultatsABGPV['ceftriaxoneCRO']){ $groupeDESBLactamines[] = array('libelle'  => 'C�ftriaxone', 'resultat' => $resultatsPossibles[$resultatsABGPV['ceftriaxoneCRO']], 'regroup' => 1); }
	        if($resultatsABGPV['cefepimeFEP']){ $groupeDESBLactamines[] = array('libelle'  => 'C�f�pime', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefepimeFEP']], 'regroup' => 1); }
	        if($resultatsABGPV['aztreonamATM']){ $groupeDESBLactamines[] = array('libelle'  => 'Aztr�onam', 'resultat' => $resultatsPossibles[$resultatsABGPV['aztreonamATM']], 'regroup' => 1); }
	          
	        $groupeDESPolymyxine = array();
	        if($resultatsABGPV['colistineCS']){ $groupeDESPolymyxine[] = array('libelle'  => 'Colistine', 'resultat' => $resultatsPossibles[$resultatsABGPV['colistineCS']], 'regroup' => 2); }
	        if($resultatsABGPV['polymicine']){ $groupeDESPolymyxine[] = array('libelle'  => 'Polymicine', 'resultat' => $resultatsPossibles[$resultatsABGPV['polymicine']], 'regroup' => 2); }
	         
	        $groupeDESAminosides = array();
	        if($resultatsABGPV['kanamycineK']){ $groupeDESAminosides[] = array('libelle'  => 'Kanamycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['kanamycineK']], 'regroup' => 3); }
	        if($resultatsABGPV['tobramycineTB']){ $groupeDESAminosides[] = array('libelle'  => 'Tobramycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['tobramycineTB']], 'regroup' => 3); }
	        if($resultatsABGPV['gentamicineGM']){ $groupeDESAminosides[] = array('libelle'  => 'Gentamicine', 'resultat' => $resultatsPossibles[$resultatsABGPV['gentamicineGM']], 'regroup' => 3); }
	        if($resultatsABGPV['amikacineAN']){ $groupeDESAminosides[] = array('libelle'  => 'Amikacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['amikacineAN']], 'regroup' => 3); }
	        if($resultatsABGPV['netilmycine']){ $groupeDESAminosides[] = array('libelle'  => 'Netilmycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['netilmycine']], 'regroup' => 3); }
	          
	        $groupeDESPhenicoles = array();
	        if($resultatsABGPV['chloramphenicolC']){ $groupeDESPhenicoles[] = array('libelle'  => 'Chloramphenicol', 'resultat' => $resultatsPossibles[$resultatsABGPV['chloramphenicolC']], 'regroup' => 4); }
	         
	        $groupeDESCyclines = array();
	        if($resultatsABGPV['minocycline']){ $groupeDESCyclines[] = array('libelle'  => 'Minocycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['minocycline']], 'regroup' => 5); }
	        if($resultatsABGPV['tetracyclineTE']){ $groupeDESCyclines[] = array('libelle'  => 'T�tracycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['tetracyclineTE']], 'regroup' => 5); }
	        if($resultatsABGPV['doxycyclineDO']){ $groupeDESCyclines[] = array('libelle'  => 'Doxycycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['doxycyclineDO']], 'regroup' => 5); }
	         
	        $groupeDESMacrolidesEtApparentes = array();
	        if($resultatsABGPV['azithromycineAZT']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Azithromycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['azithromycineAZT']], 'regroup' => 6); }
	        if($resultatsABGPV['erythromycineE']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Erythromycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['erythromycineE']], 'regroup' => 6); }
	        if($resultatsABGPV['lincomycineL']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Lincomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['lincomycineL']], 'regroup' => 6); }
	        if($resultatsABGPV['pristinamycinePT']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Pristinamycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['pristinamycinePT']], 'regroup' => 6); }

	        $groupeDESFluoroquinolones = array();
	        if($resultatsABGPV['acideNalidixiqueNA']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Acide nalidixique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideNalidixiqueNA']], 'regroup' => 7); }
	        if($resultatsABGPV['pefloxacinePEF']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'P�floxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['pefloxacinePEF']], 'regroup' => 7); }
	        if($resultatsABGPV['norfloxacineNOR']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Norfloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['norfloxacineNOR']], 'regroup' => 7); }
	        if($resultatsABGPV['ciprofloxacineCIP']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Ciprofloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['ciprofloxacineCIP']], 'regroup' => 7); }
	        if($resultatsABGPV['LEV']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'L�vofloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['LEV']], 'regroup' => 7); }
	         
	        $groupeDESImidazoles = array();
	        if($resultatsABGPV['cotrimoxazoleSXT']){ $groupeDESImidazoles[] = array('libelle'  => 'Cotrimoxazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['cotrimoxazoleSXT']], 'regroup' => 8); }
	         
	        $groupeDesGlycopeptides = array();
	        if($resultatsABGPV['vancomycineVA']){ $groupeDesGlycopeptides[] = array('libelle'  => 'Vancomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['vancomycineVA']], 'regroup' => 9); }
	        if($resultatsABGPV['teicoplanine']){ $groupeDesGlycopeptides[] = array('libelle'  => 'Teicoplanine', 'resultat' => $resultatsPossibles[$resultatsABGPV['teicoplanine']], 'regroup' => 9); }
	         
	        $groupeDesProduitsNitres = array();
	        if($resultatsABGPV['nitrofurane']){ $groupeDesProduitsNitres[] = array('libelle'  => 'Nitrofurane', 'resultat' => $resultatsPossibles[$resultatsABGPV['nitrofurane']], 'regroup' => 10); }
	        if($resultatsABGPV['metronidazole']){ $groupeDesProduitsNitres[] = array('libelle'  => 'M�tronidazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['metronidazole']], 'regroup' => 10); }
	        
	        $groupeDesSulfamides = array();
	        if($resultatsABGPV['trimethoprimeSulfametoxazoleSXT']){ $groupeDesSulfamides[] = array('libelle'  => 'Trim�thoprime + Sulfam�toxazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['trimethoprimeSulfametoxazoleSXT']], 'regroup' => 11); }
	        
	        $groupeDesAutres = array();
	        if($resultatsABGPV['fosfomycineFOS']){ $groupeDesAutres[] = array('libelle'  => 'Fosfomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['fosfomycineFOS']], 'regroup' => 12); }
	        if($resultatsABGPV['acideFusidiqueFA']){ $groupeDesAutres[] = array('libelle'  => 'Acide fusidique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideFusidiqueFA']], 'regroup' => 12); }
	        if($resultatsABGPV['rifampicineRA']){ $groupeDesAutres[] = array('libelle'  => 'Rifampycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['rifampicineRA']], 'regroup' => 12); }
	         
	        
	        $conclusion = $resultatsABGPV['conclusion'];
	        
 
	        /* Affichage en ligne*/
	        //$this->affichageInfosEnLigneABGPV($groupeDESBLactamines, $groupeDESPolymyxine, $groupeDESAminosides, $groupeDESPhenicoles, $groupeDESCyclines, $titreAlign='left');
	        
	        /* Affichage en colonne*/
	        $this->affichageInfosEnColonneABGPV($groupeDESBLactamines, $groupeDESPolymyxine, $groupeDESAminosides, $groupeDESPhenicoles, $groupeDESCyclines, $groupeDESMacrolidesEtApparentes, $groupeDESFluoroquinolones, $groupeDESImidazoles, $groupeDesGlycopeptides, $groupeDesProduitsNitres, $groupeDesSulfamides, $groupeDesAutres, $conclusion);
	        
	    }
	    
	    
	    
	}
	
	
	function affichageInfosEnLigneABGPV($groupeDESBLactamines,$groupeDESPolymyxine,$groupeDESAminosides,
	    $groupeDESPhenicoles,$groupeDESCyclines,$titreAlign = 'left'){
	    
	        
	    /**
	     * Affichage du groupe des B-Lactamines
	     */
	    if($groupeDESBLactamines){
	         
	        if($titreAlign == 'left'){
	            /* Titre B-Lactamines "alignement:gauche"*/
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('symb','U',11);
	            $this->Cell(2,6,'b ','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(177,6,'-Lactamines','',1,'L',0);
	            $this->Ln(0.5);
	            /* ====================== */
	        }else{
	            /* Titre B-Lactamines "alignement:centrer"*/
	             $this->SetFont('zap','',11);
	             $this->Cell(82,6,' O','',0,'R',0);
	             $this->SetFont('symb','U',11);
	             $this->Cell(2,6,'b ','',0,'L',0);
	             $this->SetFont('times','U',11);
	             $this->Cell(101,6,'-Lactamines','',1,'L',0);
	             $this->Ln(0.5);
	            /* ========================== */
	        }
	          
	        $indice = 0;
	        for($i=0; $i<count($groupeDESBLactamines); $i++){
	    
	            if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	    
	            /*1) Premi�re colonne ==== */
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$groupeDESBLactamines[$i]['libelle'].' :','BT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31.5,6,$groupeDESBLactamines[$i]['resultat'],'BT',0,'L',1);
	            $i++;
	            if(array_key_exists($i, $groupeDESBLactamines)){
	                /* S�parateur ------ S�parateur */
	                $this->Cell(2,6,'','',0,'R',0);
	                 
	                /*2) Deuxi�me colonne ==== */
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$groupeDESBLactamines[$i]['libelle'].' :','BT',0,'R',1);
	                $this->SetFont('times','B',11);
	                $this->Cell(31.5,6,$groupeDESBLactamines[$i]['resultat'],'BT',0,'L',1);
	            }
	            $this->Ln();
	        }
	    
	    }
	     
	     
	    /**
	     * Affichage du groupe des Polymyxines
	     */
	    if($groupeDESPolymyxine){
	         
	         
	        /* Titre Polymyxines */
	        if($titreAlign == 'left'){
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(179,6,'Polymyxines','',1,'L',0);
	            $this->Ln(0.5);
	        }else{
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(82,6,' O','',0,'R',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(103,6,'Polymyxines','',1,'L',0);
	            $this->Ln(0.5);
	        }
	        /* ========================== */
	         
	        $indice = 0;
	        for($i=0; $i<count($groupeDESPolymyxine); $i++){
	             
	            if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	             
	            /*1) Premi�re colonne ==== */
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$groupeDESPolymyxine[$i]['libelle'].' :','BT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31.5,6,$groupeDESPolymyxine[$i]['resultat'],'BT',0,'L',1);
	            $i++;
	            if(array_key_exists($i, $groupeDESPolymyxine)){
	                /* S�parateur ------ S�parateur */
	                $this->Cell(2,6,'','',0,'R',0);
	    
	                /*2) Deuxi�me colonne ==== */
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$groupeDESPolymyxine[$i]['libelle'].' :','BT',0,'R',1);
	                $this->SetFont('times','B',11);
	                $this->Cell(31.5,6,$groupeDESPolymyxine[$i]['resultat'],'BT',0,'L',1);
	            }
	            $this->Ln();
	        }
	         
	    }
	     
	     
	    /**
	     * Affichage du groupe des Aminosides
	     */
	    if($groupeDESAminosides){
	         
	        /* Titre Aminosides */
	        if($titreAlign == 'left'){
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(179,6,'Aminosides','',1,'L',0);
	            $this->Ln(0.5);
	        }else{
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(82,6,' O','',0,'R',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(103,6,'Aminosides','',1,'L',0);
	            $this->Ln(0.5);
	        }
	        /* ========================== */
	         
	        $indice = 0;
	        for($i=0; $i<count($groupeDESAminosides); $i++){
	    
	            if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	    
	            /*1) Premi�re colonne ==== */
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$groupeDESAminosides[$i]['libelle'].' :','BT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31.5,6,$groupeDESAminosides[$i]['resultat'],'BT',0,'L',1);
	            $i++;
	            if(array_key_exists($i, $groupeDESAminosides)){
	                /* S�parateur ------ S�parateur */
	                $this->Cell(2,6,'','',0,'R',0);
	                 
	                /*2) Deuxi�me colonne ==== */
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$groupeDESAminosides[$i]['libelle'].' :','BT',0,'R',1);
	                $this->SetFont('times','B',11);
	                $this->Cell(31.5,6,$groupeDESAminosides[$i]['resultat'],'BT',0,'L',1);
	            }
	            $this->Ln();
	        }
	    
	    }
	     
	     
	    /**
	     * Affichage du groupe des Ph�nicoles
	     */
	    if($groupeDESPhenicoles){
	    
	        /* Titre Ph�nicoles */
	        if($titreAlign == 'left'){
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(179,6,'Ph�nicoles','',1,'L',0);
	            $this->Ln(0.5);
	        }else{
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(82,6,' O','',0,'R',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(103,6,'Ph�nicoles','',1,'L',0);
	            $this->Ln(0.5);
	        }
	        /* ========================== */
	    
	        $indice = 0;
	        for($i=0; $i<count($groupeDESPhenicoles); $i++){
	             
	            if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	             
	            /*1) Premi�re colonne ==== */
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$groupeDESPhenicoles[$i]['libelle'].' :','BT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31.5,6,$groupeDESPhenicoles[$i]['resultat'],'BT',0,'L',1);
	            $i++;
	            if(array_key_exists($i, $groupeDESPhenicoles)){
	                /* S�parateur ------ S�parateur */
	                $this->Cell(2,6,'','',0,'R',0);
	    
	                /*2) Deuxi�me colonne ==== */
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$groupeDESPhenicoles[$i]['libelle'].' :','BT',0,'R',1);
	                $this->SetFont('times','B',11);
	                $this->Cell(31.5,6,$groupeDESPhenicoles[$i]['resultat'],'BT',0,'L',1);
	            }
	            $this->Ln();
	        }
	         
	    }
	     
	     
	    /**
	     * Affichage du groupe des cyclines
	     */
	    if($groupeDESCyclines){
	         
	        /* Titre Cyclines */
	        if($titreAlign == 'left'){
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(179,6,'Cyclines','',1,'L',0);
	            $this->Ln(0.5);
	        }else{
	            $this->Ln(2);
	            $this->SetFont('zap','',11);
	            $this->Cell(82,6,' O','',0,'R',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(103,6,'Cyclines','',1,'L',0);
	            $this->Ln(0.5);
	        }
	        /* ========================== */
	         
	        $indice = 0;
	        for($i=0; $i<count($groupeDESCyclines); $i++){
	    
	            if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
	    
	            /*1) Premi�re colonne ==== */
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$groupeDESCyclines[$i]['libelle'].' :','BT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31.5,6,$groupeDESCyclines[$i]['resultat'],'BT',0,'L',1);
	            $i++;
	            if(array_key_exists($i, $groupeDESCyclines)){
	                /* S�parateur ------ S�parateur */
	                $this->Cell(2,6,'','',0,'R',0);
	                 
	                /*2) Deuxi�me colonne ==== */
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$groupeDESCyclines[$i]['libelle'].' :','BT',0,'R',1);
	                $this->SetFont('times','B',11);
	                $this->Cell(31.5,6,$groupeDESCyclines[$i]['resultat'],'BT',0,'L',1);
	            }
	            $this->Ln();
	        }
	    
	    }
	    
	}
	
	
	
	function affichageInfosEnColonneABGPV($groupeDESBLactamines,$groupeDESPolymyxine,$groupeDESAminosides,
	         $groupeDESPhenicoles, $groupeDESCyclines, $groupeDESMacrolidesEtApparentes, $groupeDESFluoroquinolones, 
	         $groupeDESImidazoles, $groupeDesGlycopeptides, $groupeDesProduitsNitres, $groupeDesSulfamides, $groupeDesAutres, $conclusion){
	    
	         $donneesAntiBioGrammePV = array();
	         if($groupeDESBLactamines){ 
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'BLactamines', 'regroup' => 1)), $groupeDESBLactamines);
	         }
	         if($groupeDESPolymyxine){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Polymyxine', 'regroup' => 2)), $groupeDESPolymyxine);
	         }
	         if($groupeDESAminosides){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Aminosides', 'regroup' => 3)), $groupeDESAminosides);
	         }
	         if($groupeDESPhenicoles){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Ph�nicol�s', 'regroup' => 4)), $groupeDESPhenicoles);
	         }
	         if($groupeDESCyclines){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Cyclines', 'regroup' => 5)), $groupeDESCyclines);
	         }
	         if($groupeDESMacrolidesEtApparentes){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Macrolides et apparent�s', 'regroup' => 6)), $groupeDESMacrolidesEtApparentes);
	         }
	         if($groupeDESFluoroquinolones){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Fluoroquinolones', 'regroup' => 7)), $groupeDESFluoroquinolones);
	         }
	         if($groupeDESImidazoles){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Imidazol�s', 'regroup' => 8)), $groupeDESImidazoles);
	         }
	         if($groupeDesGlycopeptides){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Glycopeptides', 'regroup' => 9)), $groupeDesGlycopeptides);
	         }
	         if($groupeDesProduitsNitres){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Produits nitr�s', 'regroup' => 10)), $groupeDesProduitsNitres);
	         }
	         if($groupeDesSulfamides){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Sulfamides', 'regroup' => 11)), $groupeDesSulfamides);
	         }
	         if($groupeDesAutres){
	             $donneesAntiBioGrammePV = array_merge($donneesAntiBioGrammePV, array(array('libelle'=>'libOk','Titrelib'=>'Autres', 'regroup' => 12)), $groupeDesAutres);
	         }
	         
	         //var_dump($donneesAntiBioGrammePV); exit();
	         //var_dump(array_chunk($donneesAntiBioGrammePV, 10)); exit();
	         
	         /** Affichage en colonne automatiquement */
	         //$this->affichageInfosEnColonneABGPV_Auto($donneesAntiBioGrammePV, $conclusion);
	         
	         /** * Affichage en colonne avec ajustement */
	         $this->affichageInfosEnColonneABGPV_Ajuster($donneesAntiBioGrammePV, $conclusion);
	}
	
	/**
	 * Affichage en colonne automatiquement
	 */
	function affichageInfosEnColonneABGPV_Auto($donneesAntiBioGrammePV, $conclusion){
	    
	    $nbLigneABG = (int)(count($donneesAntiBioGrammePV)/2);
	    for($i=0,$j=$nbLigneABG ; $i<$nbLigneABG ; $i++,$j++){
	    
	        /*
	         * Premi�re colonne
	         */
	        if($donneesAntiBioGrammePV[$i]['libelle'] == 'libOk'){
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            if($donneesAntiBioGrammePV[$i]['Titrelib'] == 'BLactamines'){
	                $this->SetFont('symb','U',11);
	                $this->Cell(2,6,'b ','',0,'L',0);
	                $this->SetFont('times','U',11);
	                $this->Cell(83,6,'-Lactamines','',0,'L',0);
	            }else{
	                $this->SetFont('times','U',11);
	                $this->Cell(85,6,$donneesAntiBioGrammePV[$i]['Titrelib'],'',0,'L',0);
	            }
	        }else{
	            $this->SetFont('times','',11);
	            $this->Cell(60,6,$donneesAntiBioGrammePV[$i]['libelle'].' :','BLT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(31,6,$donneesAntiBioGrammePV[$i]['resultat'],'BRT',0,'L',1);
	        }
	    
	        /* S�parateur ------ S�parateur */
	        $this->Cell(4,6,'','',0,'R',0);
	    
	        /*
	         * Deuxi�me colonne
	         */
	        if($donneesAntiBioGrammePV[$j]['libelle'] == 'libOk'){
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            $this->SetFont('times','U',11);
	            $this->Cell(85,6,$donneesAntiBioGrammePV[$j]['Titrelib'],'',1,'L',0);
	        }else{
	            $this->SetFont('times','',11);
	            $this->Cell(50,6,$donneesAntiBioGrammePV[$j]['libelle'].' :','BLT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(41,6,$donneesAntiBioGrammePV[$j]['resultat'],'BRT',1,'L',1);
	        }
	    
	        $this->Ln(0.5);
	    }
	    
	    /**
	     * Ligne restante
	     */
	    $nbLigneABG = count($donneesAntiBioGrammePV)%2;
	    if($nbLigneABG != 0){
	        /*
	         * Premi�re colonne
	         */
	        $this->Cell(91,6,'','',0,'',0);
	        /* S�parateur ------ S�parateur */
	        $this->Cell(4,6,'','',0,'',0);
	        /*
	         * Deuxi�me colonne
	         */
	        if(array_key_exists($j, $donneesAntiBioGrammePV)){
	            $this->SetFont('times','',11);
	            $this->Cell(50,6,$donneesAntiBioGrammePV[$j]['libelle'].' :','BLT',0,'R',1);
	            $this->SetFont('times','B',11);
	            $this->Cell(41,6,$donneesAntiBioGrammePV[$j]['resultat'],'BRT',0,'L',1);
	        }
	        $this->Ln();
	    }
	    
	    /**
	     * Conclusion
	     */
	    if(str_replace(' ','', $conclusion)){
	        $this->SetFont('zap','',11);
	        $this->Cell(5,6,'*','',0,'L',0);
	        $this->SetFont('timesb','U',10);
	        $this->Cell(180,6,'Conclusion :','',1,'L',0);
	         
	        $this->SetFont('times','',10.5);
	        $this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $conclusion),0,'J',1);
	    }
	    $this->Ln();
	    $this->SetFont('timesi','U',9);
	    $this->Cell(140,6,'','',0,'',0);
	    $this->Cell(45,6,'Cachet et signature','',1,'L',0);
	    
	}
	
	/**
	 * Affichage en colonne avec ajustement
	 */
	function affichageInfosEnColonneABGPV_Ajuster($donneesAntiBioGrammePV, $conclusion){
	    
	    $elementsCol1 = array();
	    $elementsCol2 = array();
	    
	    $nbLignes = (int)(count($donneesAntiBioGrammePV)/2);
	    $subdivListe = array_chunk($donneesAntiBioGrammePV, $nbLignes);
	    
	    $listeElements = array();
	    for($i=0 ; $i<count($subdivListe) ; $i++){
	        $listeElements['liste'.$i] = $subdivListe[$i];
	    }
	    
	    $liste1 = $listeElements['liste0'];
	    $liste2 = $listeElements['liste1'];
	    $liste3 = (count($listeElements) == 3) ? $listeElements['liste2'] : array();
	    
	    $lastElementListe1 = $liste1[count($liste1)-1];
	    $elSuivants = array();
	    for($i=0 ; $i<count($liste2) ; $i++){
	        if($liste2[$i]['regroup'] == $lastElementListe1['regroup']){
	            $elSuivants [] = $liste2[$i];
	        }else{
	            $elementsCol2 [] = $liste2[$i];
	        }
	    }
	    
	    /**
	     * Premiere colonne
	     */
	    $elementsCol1 = array_merge($liste1, $elSuivants);
	    
	    /**
	     * Deuxieme colonne
	     */
	    $elementsCol2 = array_merge($elementsCol2, $liste3);
	     
	    
	    //var_dump($elementsCol2); exit();
	    //var_dump($donneesAntiBioGrammePV[$nbLigne]); exit();
	    
	    
	    
	    for($i=0 ; $i<count($elementsCol1) ; $i++){
	         
	        /*
	         * Premi�re colonne
	         */
	        if($elementsCol1[$i]['libelle'] == 'libOk'){
	            $this->SetFont('zap','',11);
	            $this->Cell(6,6,' O','',0,'L',0);
	            if($elementsCol1[$i]['Titrelib'] == 'BLactamines'){
	                $this->SetFont('symb','U',11);
	                $this->Cell(2,6,'b ','',0,'L',0);
	                $this->SetFont('times','U',11);
	                $this->Cell(83,6,'-Lactamines','',0,'L',0);
	            }else{
	                $this->SetFont('times','U',11);
	                $this->Cell(85,6,$elementsCol1[$i]['Titrelib'],'',0,'L',0);
	            }
	        }else{
	            $libelleCol1 = $elementsCol1[$i]['libelle'];
	            
	            if($libelleCol1 == 'Amoxicilline + Acide clavulanique' || $libelleCol1 == 'Ticarcilline + Acide clavulanique'){
	                $this->SetFont('times','',11);
	                $this->Cell(60,6,$libelleCol1.' :','BLT',0,'R',1);
	                $resultatCol1 = $elementsCol1[$i]['resultat'];
	                if($resultatCol1 == 'Sensible'){
	                    $this->SetFont('times','B',11);
	                    $this->Cell(31,6,$resultatCol1,'BRT',0,'L',1);
	                }else{
	                    $this->SetFont('times','',11);
	                    $this->Cell(31,6,$resultatCol1,'BRT',0,'L',1);
	                }
	            }else{
	                $this->SetFont('times','',11);
	                $this->Cell(50,6,$libelleCol1.' :','BLT',0,'R',1);
	                $resultatCol1 = $elementsCol1[$i]['resultat'];
	                if($resultatCol1 == 'Sensible'){
	                    $this->SetFont('times','B',11);
	                    $this->Cell(41,6,$resultatCol1,'BRT',0,'L',1);
	                }else{
	                    $this->SetFont('times','',11);
	                    $this->Cell(41,6,$resultatCol1,'BRT',0,'L',1);
	                }
	            }
	        }
	         
	        /* S�parateur ------ S�parateur */
	        $this->Cell(4,6,'','',0,'R',0);
	         
	        /*
	         * Deuxi�me colonne
	         */
	        if(array_key_exists($i, $elementsCol2)){
	            if($elementsCol2[$i]['libelle'] == 'libOk'){
	                $this->SetFont('zap','',11);
	                $this->Cell(6,6,' O','',0,'L',0);
	                $this->SetFont('times','U',11);
	                $this->Cell(85,6,$elementsCol2[$i]['Titrelib'],'',1,'L',0);
	            }else{
	                $this->SetFont('times','',11);
	                $libelle = $elementsCol2[$i]['libelle'];
	                if($libelle == 'Trim�thoprime + Sulfam�toxazole'){
	                    $this->Cell(60,6,$libelle.' :','BLT',0,'R',1);
	                     
	                    $resultat = $elementsCol2[$i]['resultat'];
	                    if($resultat == 'Sensible'){
	                        $this->SetFont('times','B',11);
	                        $this->Cell(31,6,$resultat,'BRT',1,'L',1);
	                    }else{
	                        $this->SetFont('times','',11);
	                        $this->Cell(31,6,$resultat,'BRT',1,'L',1);
	                    }
	                }else{
	                    $this->Cell(50,6,$libelle.' :','BLT',0,'R',1);
	                     
	                    $resultat = $elementsCol2[$i]['resultat'];
	                    if($resultat == 'Sensible'){
	                        $this->SetFont('times','B',11);
	                        $this->Cell(41,6,$resultat,'BRT',1,'L',1);
	                    }else{
	                        $this->SetFont('times','',11);
	                        $this->Cell(41,6,$resultat,'BRT',1,'L',1);
	                    }
	                }
	            }
	        }else {$this->Ln();}
	         
	        $this->Ln(0.5);
	    }
	    
	    
	    
	    /**
	     * Dernier element � afficher si la colonne 2 est superieur � la colonne 1
	     */
	    
	    /**
	     * Ligne restante
	     */
	    
	    if(count($elementsCol2) > count($elementsCol1)){
	        /*
	         * Premi�re colonne
	         */
	        $this->Cell(91,6,'','',0,'',0);
	        /* S�parateur ------ S�parateur */
	        $this->Cell(4,6,'','',0,'',0);
	        /*
	         * Deuxi�me colonne
	         */
	        if(array_key_exists(count($elementsCol2)-1, $elementsCol2)){
	            
	            $libelle = $elementsCol2[count($elementsCol2)-1]['libelle'];
	            
	            $this->SetFont('times','',11);
	            if($libelle == 'Trim�thoprime + Sulfam�toxazole'){
	                $this->Cell(60,6,$libelle.' :','BLT',0,'R',1);
	                $resultat = $elementsCol2[count($elementsCol2)-1]['resultat'];
	                if($resultat == 'Sensible'){
	                    $this->SetFont('times','B',11);
	                    $this->Cell(31,6,$resultat,'BRT',0,'L',1);
	                }else{
	                    $this->SetFont('times','',11);
	                    $this->Cell(31,6,$resultat,'BRT',0,'L',1);
	                }
	            }else{
	                $this->Cell(50,6,$libelle.' :','BLT',0,'R',1);
	                $resultat = $elementsCol2[count($elementsCol2)-1]['resultat'];
	                if($resultat == 'Sensible'){
	                    $this->SetFont('times','B',11);
	                    $this->Cell(41,6,$resultat,'BRT',0,'L',1);
	                }else{
	                    $this->SetFont('times','',11);
	                    $this->Cell(41,6,$resultat,'BRT',0,'L',1);
	                }
	            }
	            
	        }
	        
	        $this->Ln();
	    }
	    
	    $this->Ln(6);
	    
	     
	    /**
	     * Conclusion
	     */
	    if(str_replace(' ','', $conclusion)){
	        $this->SetFont('zap','',11);
	        $this->Cell(5,6,'*','',0,'L',0);
	        $this->SetFont('timesb','U',10);
	        $this->Cell(180,6,'Conclusion :','',1,'L',0);
	    
	        $this->SetFont('times','',10.5);
	        $this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $conclusion),0,'J',1);
	    }
	    $this->Ln();
	    $this->SetFont('timesi','U',9);
	    $this->Cell(140,6,'','',0,'',0);
	    $this->Cell(45,6,'Cachet et signature','',1,'L',0);
	    
	}
	
	
	
	
	function AfficherResultatsECBU(){
		$controle = new DateHelper();
		$this->AddFont('symb','','symbol.php');
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
		$this->AddFont('bordure','','borderpi1515-9.php');
		$this->AddFont('math','','maths.php');
		
	
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
	
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
	
			if($idanalyse == 66){
				$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
				$idAnalyses[$idanalyse]          = $idanalyse;
				$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
				$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
			}
		}
	
		//Date de prel�vement
		$datePrelevement = $infosAnalyseDemande[66]['DateHeurePrelevement'];
	
		//Affichage des infos sur le biologiste et le technicien
		$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[66]['DateEnregistrementResultat']);
		$prenomNomTechnicien = $infosAnalyseDemande[66]['Prenom'].' '.$infosAnalyseDemande[66]['Nom'];
		$prenomNomBiologiste = $infosAnalyseDemande[66]['PrenomValidateur'].' '.$infosAnalyseDemande[66]['NomValidateur'];
	
		$this->SetFont('times','',8);
		//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
	
		//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
		$this->Cell(90,-1,'','',1,'L',0);
	
		$this->Ln(5);
	
		//AFFICHAGE DE L'EN TETE DU TEXTE
		//AFFICHAGE DE L'EN TETE DU TEXTE
		$this->SetFillColor(249,249,249);
		$this->SetDrawColor(220,220,220);
	
	
		$this->SetTextColor(123, 125, 125);
		$this->SetFont('bordure','',12);
		$x = 87.5;
		$y = 68.3;
		 
		$this->Text($x-16, $y+3, 'GKKKKKKKKKKKKKK');
		$this->Text($x+46.5, $y+3, 'H');
		$this->Text($x-16, $y+7, 'JMMMMMMMMMMMMMM');
		$this->Text($x+46.5, $y+7, 'I');
		 
		$this->SetTextColor(0,0,0);
		 
		$this->SetFont('times','',11);
		$this->Cell(35,7,'','',0,'L',0);
		$this->Cell(115,6,"Examen Cytobact�riologique des Urines",'',0,'C',0);
		$this->Cell(35,7,'','',1,'C',0);
	
		$this->Ln(3);
	
		//mat�riel utilis� --- mat�riel utilis� --- mat�riel utilis�
		$this->SetFont('zap','',11.3);
		$this->Cell(4,6,' ^','BT',0,'C',1);
		$this->SetFont('times','',11);
		$this->Cell(181,6,'Type de mat�riel utilis� : '.iconv ('UTF-8' , 'windows-1252', $resultats[66]['type_materiel']),'BT',1,'L',1);
	
		$this->Ln(5);
		$indice = 0;
		 
		 
		$idAnalysesSerologieECBU = $this->getAnalysesSerologieECBU();
		 
		$listeAspectUrines = array(0=>'', 1=>'Claires', 2 =>'L�g�rement troubles', 3=>'Troubles', 4=>'H�matiques', 5=>'Purulentes');

		$listeLeucocytesRep = array(0=>'', 1=>'Pr�sentes', 2=>'Absentes');
		$listeHematiesRep   = array(0=>'', 1=>'Pr�sentes', 2=>'Absentes');
		$listeLevuresRep    = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
		$listeFilamentsMyceliensRep = array(0=>'', 1=>'Pr�sence', 2=>'Absence');
		$listeFloreRep    = array(0=>'', 1=>'Bacilles � Gram n�gatif', 2=>'Bacilles � Gram positif', 3=>'Cocci � Gram positif', 4=>'Diplocoques � Gram n�gatif', 5=>'Absence');
		$listeCulotRep = array(0=>'', 1=>'Positive', 2=>'N�gative');
		$listeCultureRep = array(0=>'', 1=>'Positive', 2=>'N�gative');
		$listeIdentifSouchesRep = array();

		$listeEcbuCulotRep = array(0=>'', 1=>'Oeufs', 2=>'Cristaux', 3=>'Cylindres', 4=>'Parasites');
		$listeEcbuCulotOeufsRep = array(0=>'', 1=>'Schistoma haematobium', 2=>'Schistoma mansoni');
		$listeEcbuCulotCristauxRep = array(0=>'', 1=>'Oxalate de calcium', 2=>'Phosphate', 3=>'Acide Urique', 4=>'Phospho-ammoniacomagnesien');
		$listeEcbuCulotCylindresRep = array(0=>'', 1=>'H�matiques', 2=>'Leucocytaires');
		
		
		
		if(in_array(66, $idAnalysesSerologieECBU)){
	
			/**
			 * Titre examen macroscopique
			 */
			$this->SetFont('zap','',10);
			$this->Cell(4,6,' m','',0,'C',0);
			$this->SetFont('timesi','U',11);
			$this->Cell(181,6,'Examen macroscopique','',1,'L',0);
			/**
			 * ==========================
			*/
			 
			/*
			 * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
			*/
			$indice = 0;
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			 
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Urines :','BT',0,'R',1);
			
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$indexAspectUrines = (int)$resultats[66]['Urines'];
			$this->SetFont('times','B',11);
			$this->Cell(48,6,$listeAspectUrines[$indexAspectUrines],'BT',0,'L',1);
			
			/*3) Troisi�me colonne ===== pour les r�f�rences */
			$this->SetFont('times','',10);
			$this->Cell(85,6,'','BT',1,'R',1);
			
			
			/**
			 * Titre examen microscopique
			*/
			$this->Ln(2);
			$this->SetFont('zap','',10);
			$this->Cell(4,6,' m','',0,'C',0);
			$this->SetFont('timesi','U',11);
			$this->Cell(181,6,'Examen microscopique','',1,'L',0);
			/**
			 * ==========================
			*/
			 
			
			/*
			 * Premi�re ligne --- Premi�re ligne --- Premi�re ligne
			*/
			$indice = 0;
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Leucocytes :','BT',0,'R',1);
			
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$indexLC = (int)$resultats[66]['Leucocytes'];
			$this->SetFont('times','B',11);
			$this->Cell(22,6,$listeLeucocytesRep[$indexLC],'BT',0,'L',1);
			 
			/*3) Troisi�me colonne ==== Pour les r�sultats*/
			$valChamp = $resultats[66]['LeucocytesChamp'];
			if($valChamp){
				$this->SetFont('times','B',11);
				$this->Cell(8,6,$valChamp,'BT',0,'R',1);
				$this->SetFont('timesi','',10);
				$this->Cell(18,6,'/champ','BT',0,'L',1);
			}else{
				$this->Cell(26,6,'','BT',0,'R',1);
			}
			 
			/*4) Quatri�me colonne ===== pour les r�f�rences */
			$this->SetFont('times','',10);
			$this->Cell(42,6,'H�maties :','BT',0,'R',1);
			
			/*5) Cinqui�me colonne ===== pour les r�f�rences */
			$indexHC = (int)$resultats[66]['Hematies'];
			$this->SetFont('times','B',11);
			$this->Cell(22,6,$listeHematiesRep[$indexHC],'BT',0,'L',1);
			 
			/*6) Sixi�me colonne ===== pour les r�f�rences */
			$valChampHem = $resultats[66]['HematiesChamp'];
			if($valChampHem){
				$this->SetFont('times','B',11);
				$this->Cell(8,6,$valChampHem,'BT',0,'R',1);
				$this->SetFont('timesi','',10);
				$this->Cell(13,6,'/champ','BT',1,'L',1);
			}else{
				$this->Cell(21,6,'','BT',1,'R',1);
			}
			 
			 
			/*
			 * Deuxi�me ligne --- Deuxi�me ligne --- Deuxi�me ligne
			*/
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			$this->Ln(0.5);
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Levures :','BT',0,'R',1);
			
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$indexLev = (int)$resultats[66]['Levures'];
			$this->SetFont('times','B',11);
			$this->Cell(48,6,$listeLevuresRep[$indexLev],'BT',0,'L',1);
			 
                        $indexTV = (int)$resultats[66]['FilamentsMyceliens'];
                        if($indexTV){
                        /*3) Troisi�me colonne B ===== pour les r�f�rences */
			$this->SetFont('times','',10);
			$this->Cell(42,6,'Filaments myc�liens :','BT',0,'R',1);

			/*4) Quatri�me colonne ===== pour les r�f�rences */
			
			$this->SetFont('times','B',11);
			$this->Cell(43,6,$listeFilamentsMyceliensRep[$indexTV],'BT',1,'L',1);

                        }else{
                        $this->Cell(85,6,$listeFilamentsMyceliensRep[$indexTV],'BT',1,'L',1);
                        }
			

			





/*
			 * SUivant Premi�re ligne --- Premi�re ligne --- Premi�re ligne
			*/
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			$this->Ln(0.5);
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Cellules �pitheliales :','BT',0,'R',1);
			
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$indexLC = (int)$resultats[66]['CellulesEpitheliales'];
			$this->SetFont('times','B',11);
			$this->Cell(22,6,$listeLeucocytesRep[$indexLC],'BT',0,'L',1);
				
			/*3) Troisi�me colonne ==== Pour les r�sultats*/
			$valChamp = $resultats[66]['CellulesEpithelialesChamp'];
			if($valChamp){
				$this->SetFont('times','B',11);
				$this->Cell(8,6,$valChamp,'BT',0,'R',1);
				$this->SetFont('timesi','',10);
				$this->Cell(18,6,'/champ','BT',0,'L',1);
			}else{
				$this->Cell(26,6,'','BT',0,'R',1);
			}
				
			/*4) Quatri�me colonne ===== pour les r�f�rences */
			$this->SetFont('times','',10);
			$this->Cell(85,6,'','BT',1,'R',1);







			/*
			 * Troisi�me ligne --- Troisi�me ligne --- Troisi�me ligne
			*/
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			$this->Ln(0.5);
			
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Flore :','BT',0,'R',1);
			 
			/*2) Deuxi�me colonne ==== Pour les r�sultats*/
			$indexAF = (int)$resultats[66]['Flore'];
			$cocciChoix1 = $resultats[66]['FloreAmas'];
			$cocciChoix2 = $resultats[66]['FloreChainette'];
			if($cocciChoix1==1 && $cocciChoix2==1){
				$compInfos = ' en amas et en chainettes';
			}elseif ($cocciChoix1==1){
				$compInfos = ' en amas';
			}elseif ($cocciChoix2==1){
				$compInfos = ' en chainettes';
			}else{
				$compInfos = '';
			}
			 
			$this->SetFont('times','B',11);
			$this->Cell(90,6,$listeFloreRep[$indexAF].$compInfos,'BT',0,'L',1);
			 
			/*3) Troisi�me colonne ===== pour les r�f�rences */
			$this->SetFont('times','B',11);
			$this->Cell(43,6,'','BT',1,'L',1);
			 
			
			
			/*
			 * Parasites --- Parasites --- Parasites
			 * Parasites --- Parasites --- Parasites 
			 */
			$leParasite = (int)$resultats[66]['Parasites'];
			if($leParasite){
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
				$this->Ln(0.5);
					
				/*1) Premi�re colonne ==== Pour les libell�s*/
				$this->SetFont('zap','',10.5);
				$this->Cell(5,6,'','BT',0,'L',1);
				$this->SetFont('times','',10);
				$this->Cell(47,6,'Parasites :','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(90,6,$this->listeParasites[$leParasite],'BT',0,'L',1);
				
				/*3) Troisi�me colonne ===== pour les r�f�rences */
				$this->SetFont('times','B',11);
				$this->Cell(43,6,'','BT',1,'L',1);
			}
			
			
			/*
			 * Quatri�me ligne --- Quatri�me ligne --- Quatri�me ligne
			*/
			if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
			$this->Ln(0.5);
				
			/*1) Premi�re colonne ==== Pour les libell�s*/
			$this->SetFont('zap','',10.5);
			$this->Cell(5,6,'','BT',0,'L',1);
			$this->SetFont('times','',10);
			$this->Cell(47,6,'Culot :','BT',0,'R',1);
			
			$indexCulot = (int)$resultats[66]['Culot'];
			$this->SetFont('times','B',11);
			$this->Cell(90,6,$listeCulotRep[$indexCulot],'BT',0,'L',1);
			
			/*3) Troisi�me colonne ===== pour les r�f�rences */
			$this->SetFont('times','B',11);
			$this->Cell(43,6,'','BT',1,'L',1);
			
			//Afficher les elements selectionnes du colut positif
			if($indexCulot == 1){
				$ecbuCulotOeufs = null;
				$ecbuCulotCristaux = null;
				$ecbuCulotCylindres = null;
				$ecbuCulotParasites = null;
				
				$resultatCulPos = $this->resultatsCulotPositifECBU;
				
				foreach ($resultatCulPos as $resultCul){
					
					$typeCulot = $resultCul['type_culot'];
					$infoResult = "";
					if($typeCulot == 4){
						$infoResult = str_replace( '"', '\"', $resultCul['info_culot']);
						
						$ecbuCulotParasites .= $infoResult.' + ';
					}else{
						$valCulot = $resultCul['valeur_culot'];
						if($typeCulot == 1){
							$infoResult = $listeEcbuCulotOeufsRep[$valCulot];
							$ecbuCulotOeufs .= $infoResult.' + ';
						}elseif ($typeCulot == 2){
							$infoResult = $listeEcbuCulotCristauxRep[$valCulot];
							$ecbuCulotCristaux .= $infoResult.' + ';
						}elseif ($typeCulot == 3){
							$infoResult = $listeEcbuCulotCylindresRep[$valCulot];
							$ecbuCulotCylindres .= $infoResult.' + ';
						}
						
					}
					
				}
				
				if($ecbuCulotOeufs){
					$this->SetFont('zap','',9);
					$this->Cell(5,6,'','',0,'L',1);
					$this->Cell(51,6,'�','',0,'R',1);
					$this->SetFont('times','',11);
					$this->Cell(129,6,'Oeufs : '.rtrim($ecbuCulotOeufs,'+ '),'',1,'L',1);
				}
				
				if($ecbuCulotCristaux){
					$this->SetFont('zap','',9);
					$this->Cell(5,6,'','',0,'L',1);
					$this->Cell(51,6,'�','',0,'R',1);
					$this->SetFont('times','',11);
					$this->Cell(129,6,'Cristaux : '.rtrim($ecbuCulotCristaux,'+ '),'',1,'L',1);
				}
				
				if($ecbuCulotCylindres){
					$this->SetFont('zap','',9);
					$this->Cell(5,6,'','',0,'L',1);
					$this->Cell(51,6,'�','',0,'R',1);
					$this->SetFont('times','',11);
					$this->Cell(129,6,'Cylindres : '.rtrim($ecbuCulotCylindres,'+ '),'',1,'L',1);
				}
				
				if($ecbuCulotParasites){
					$this->SetFont('zap','',9);
					$this->Cell(5,6,'','',0,'L',1);
					$this->Cell(51,6,'�','',0,'R',1);
					$this->SetFont('times','',11);
					$this->Cell(129,6,'Parasites : '.rtrim($ecbuCulotParasites,'+ '),'',1,'L',1);
				}
				
				$this->Cell(185,1,'','B',1,'L',1);
			}
			
			
			/*
			 * Sixi�me ligne --- Sixi�me ligne --- Sixi�me ligne
			*/
			$indexCulture = (int)$resultats[66]['Culture'];
			if($indexCulture){
				
				/**
				 * Titre culture
				 */
				$this->Ln(2);
				$this->SetFont('zap','',10);
				$this->Cell(4,6,' m','',0,'C',0);
				$this->SetFont('timesi','U',11);
				$this->Cell(181,6,'Culture','',1,'L',0);
				/**
				 * ==========================
				*/
				
				$indice = 0;
				if(($indice++%2) == 0){ $this->SetFillColor(225,225,225); }else{ $this->SetFillColor(249,249,249); }
				$this->Ln(0.5);
					
				/*1) Premi�re colonne ==== Pour les libell�s*/
				$this->SetFont('zap','',10.5);
				$this->Cell(5,6,'','BT',0,'L',1);
				$this->SetFont('times','',10);
				$this->Cell(47,6,'Culture :','BT',0,'R',1);
				
				$this->SetFont('times','B',11);
				$this->Cell(20,6,$listeCultureRep[$indexCulture],'BT',0,'L',1);
				
				$this->SetFont('zap','',11);
				$this->Cell(8,6,'�','BT',0,'L',1);
					
				if($indexCulture == 1){
					$CulturePos1 = (int)$resultats[66]['CulturePos1'];
					$CulturePos2 = (int)$resultats[66]['CulturePos2'];
				
					if($CulturePos1 == 1){
							
						$this->SetFont('times','',11);
						$this->Cell(12,6,'DGU','BT',0,'R',1);
							
						$this->SetFont('math','',13);
						$this->Cell(5,6,'8','T',0,'L',1);
							
						$x = $this->GetX(); $y = $this->GetY();
						$this->SetFont('times','B',10);
						$this->Cell(8,6,'10 ','T',0,'L',1);
						$this->Text($x+5.5, $y+3, '5');
						$this->SetFont('times','B',11);
						$this->Cell(80,6,'germes/ml','BT',1,'L',1);
						
						//La souche identifi�e -- La souche identifi�e
						$this->SetFont('zap','',10.5);
						$this->Cell(5,6,'','BT',0,'L',1);
						$this->SetFont('times','',10);
						$this->Cell(47,6,'Identification :','BT',0,'R',1);
						
						$identifSouche = (int)$resultats[66]['IdentificationCulture'];
						
						$this->SetFont('times','B',11);
						$this->Cell(133,6,$this->listeSouchesIdentif[$identifSouche],'BT',1,'L',1);
						

						/*Antibiogramme en pi�ce jointe*/
						$this->Cell(45,6,'','',0,'C',0);
						$this->SetFont('zap','',12);
						$this->Cell(4,6,'b','',0,'C',0);
						$this->SetFont('timesi','',11);
						$this->Cell(135,6,'(ABG en pi�ce jointe)','',0,'L',0);
						
					
					}elseif ($CulturePos2 == 1){
							
						$x = $this->GetX(); $y = $this->GetY();
						$this->SetFont('times','B',10);
						$this->Cell(8,6,'10 ','T',0,'L',1);
						$this->Text($x+5.5, $y+3, '4');
							
						$this->SetFont('times','B',13);
						$this->Cell(5,6,'<','T',0,'R',1);
							
						$this->SetFont('times','',11);
						$this->Cell(12,6,'DGU','BT',0,'C',1);
							
						$this->SetFont('times','B',13);
						$this->Cell(5,6,'>','T',0,'L',1);
							
						$x = $this->GetX(); $y = $this->GetY();
						$this->SetFont('times','B',10);
						$this->Cell(8,6,'10 ','T',0,'L',1);
						$this->Text($x+5.5, $y+3, '5');
							
						$this->SetFont('times','B',11);
						$this->Cell(67,6,'germes/ml','BT',1,'L',1);
							
					}else{
						$this->SetFont('times','B',11);
						$this->Cell(105,6,'','BT',0,'L',1);
					}
				
				}elseif ($indexCulture == 2){
				
					$this->SetFont('times','',11);
					$this->Cell(12,6,'DGU','BT',0,'R',1);
						
					$this->SetFont('times','',13);
					$this->Cell(5,6,'<','T',0,'L',1);
						
					$x = $this->GetX(); $y = $this->GetY();
					$this->SetFont('times','B',10);
					$this->Cell(8,6,'10 ','T',0,'L',1);
					$this->Text($x+5.5, $y+3, '4');
					$this->SetFont('times','B',11);
					$this->Cell(80,6,'germes/ml','BT',1,'L',1);
					
				}else {
					$this->SetFont('times','B',11);
					$this->Cell(105,6,'','BT',0,'L',1);
				}
				
			}

			
			
			$this->Ln();
			
			$conclusion = $resultats[66]['conclusion'];
			
			/**
			 *
			 */
			if(str_replace(' ','', $conclusion) || str_replace(' ','', $resultats[65]['precision_commentaire'])){
				$this->SetFillColor(249,249,249);
			
				$this->Ln(2);
				$this->SetFont('zap','',10);
				$this->Cell(4,6,' ','',0,'C',0);
				$this->SetFont('timesi','U',11);
				$this->Cell(181,6,'Conclusion','',1,'L',1);
			}
			/**
			 * ==========================
			 */
			
			/**
			 * Conclusion
			 */
			if(str_replace(' ','', $conclusion)){
				$this->SetFont('times','',10.5);
				$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $conclusion),0,'J',1);
			}
			/**
			 * Pr�cision sur la conclusion
			 */
			if(str_replace(' ','', $resultats[66]['precision_commentaire'])){
				 
				$this->Ln(1);
				$this->SetFont('times','',10.5);
				$this->MultiCell(185,6,iconv ('UTF-8' , 'windows-1252', $resultats[66]['precision_commentaire']),0,'J',1);
			}
			
			
			$this->Ln();
			$this->SetFont('timesi','U',9);
			$this->Cell(140,6,'','',0,'',0);
			$this->Cell(45,6,'Cachet et signature','',1,'L',0);
			
			
			
			/**
			 * Affichage de l'antibiogramme --- Affichage de l'antibiogramme
			 */
			if( $resultats[66]['IdentificationCulture'] != 0 ){
				$iddemande = $resultats[66]['idresultat_demande_analyse'];
				 
				$resultatsABGPV = $this->getResultatsAntiBioGrammeECBUDemande();
				//var_dump($resultatsABGPV); exit();
				$this->affichageResultatsAntibiogrammeECBU($resultatsABGPV);
			}
			
		}
		 
	}
	
	
	function AfficherResultatsAntibiogrammeECBU($resultatsABGPV){
		 
		$controle = new DateHelper();
		$this->AddFont('symb','','symbol.php');
		$this->AddFont('zap','','zapfdingbats.php');
		$this->AddFont('timesb','','timesb.php');
		$this->AddFont('timesi','','timesi.php');
		$this->AddFont('times','','times.php');
		//$this->AddFont('bordure','','borderpi1515-9.php');
		 
		$resultats = $this->getResultatsAnalysesDemandees();
		$listeAnalysesDemandees = $this->getAnalysesDemandees();
		$infosAnalyseDemande = array();
		 
		for($i = 0 ; $i < count($listeAnalysesDemandees) ; $i++){
			$idanalyse = $listeAnalysesDemandees[$i]['idanalyse'];
		  
			if($idanalyse == 66){
				$analyses[$idanalyse]            = $listeAnalysesDemandees[$i]['Designation'];
				$idAnalyses[$idanalyse]          = $idanalyse;
				$typesAnalyses[$idanalyse]       = $listeAnalysesDemandees[$i]['Libelle'];
				$infosAnalyseDemande[$idanalyse] = $listeAnalysesDemandees[$i];
			}
		}
		 
		//Date de prel�vement
		$datePrelevement = $infosAnalyseDemande[66]['DateHeurePrelevement'];
		 
		//Affichage des infos sur le biologiste et le technicien
		$dateEnregistrement  =  $controle->convertDateTime($infosAnalyseDemande[66]['DateEnregistrementResultat']);
		$prenomNomTechnicien = $infosAnalyseDemande[66]['Prenom'].' '.$infosAnalyseDemande[66]['Nom'];
		$prenomNomBiologiste = $infosAnalyseDemande[66]['PrenomValidateur'].' '.$infosAnalyseDemande[66]['NomValidateur'];
		 
		$this->SetFont('times','',8);
		//$this->Cell(45,-1,'Enregistr� le : '.$dateEnregistrement,'',0,'L',0);
		$this->Cell(45,-1,'Pr�l�vement effectu� le : '.$datePrelevement,'',0,'L',0);
		 
		//$this->Cell(90,-1,'par : '.$prenomNomTechnicien.' ; valid� par : '.$prenomNomBiologiste,'',1,'L',0);
		$this->Cell(90,-1,'','',1,'L',0);
		 
		$this->Ln(5);
		 
		//AFFICHAGE DE L'EN TETE DU TEXTE
		//AFFICHAGE DE L'EN TETE DU TEXTE
		$this->SetFillColor(249,249,249);
		$this->SetDrawColor(202,207,210);
		 
		$this->SetFont('times','I',10);
		$this->Cell(75,7,'','',0,'L',0);
		 
		$this->SetTextColor(123, 125, 125);
		 
		$this->SetFont('bordure','',12);
		$x = $this->GetX(); $y = $this->GetY();
		$this->Text($x-1, $y+3, 'GKKKKKKK');
		$this->Text($x+32, $y+3, 'H');
		$this->Text($x-1, $y+7, 'JMMMMMMM');
		$this->Text($x+32, $y+7, 'I');
		 
		$this->SetTextColor(0,0,0);
		 
		$this->SetFont('times','',11);
		$this->Cell(35,6,"ANTIBIOGRAMME",'',0,'C',0);
		 
		$this->Cell(75,7,'','',1,'C',0);
		 
		$this->Ln(3);
		 
		$indexIC = (int)$resultats[66]['IdentificationCulture'];
		 
		//souche isol�e -- souche isol�e -- souche isol�e
		$this->SetFont('zap','',11.3);
		$this->Cell(4,6,' ^','BT',0,'C',1);
		$this->SetFont('times','',11);
		$this->Cell(181,6,'Souche isol�e (ECBU) : '.iconv ('UTF-8' , 'windows-1252', $this->listeSouchesIdentif[$indexIC] ),'BT',1,'L',1);
		 
		$this->Ln(5);
		$indice = 0;
	
		 
		/** Affichage des donn�es de l'antibiogramme **/
		$resultatsPossibles = array(1=>'R�sistante', 2=>'Sensible', 3=> 'Interm�diaire');
		 
		 
		if($resultatsABGPV){
			 
			$groupeDESBLactamines = array();
			if($resultatsABGPV['ampicillineAM']){ $groupeDESBLactamines[] = array('libelle'  => 'Ampicilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['ampicillineAM']], 'regroup' => 1 ); }
			if($resultatsABGPV['amoxillineAMX']){ $groupeDESBLactamines[] = array('libelle'  => 'Amoxilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['amoxillineAMX']], 'regroup' => 1); }
			if($resultatsABGPV['ticarcillineTIC']){ $groupeDESBLactamines[] = array('libelle'  => 'Ticarcilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['ticarcillineTIC']], 'regroup' => 1); }
			if($resultatsABGPV['piperacillinePIP']){ $groupeDESBLactamines[] = array('libelle'  => 'Piperacilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['piperacillinePIP']], 'regroup' => 1); }
			if($resultatsABGPV['acideClavulaniqueAmoxicillineAMC']){ $groupeDESBLactamines[] = array('libelle'  => 'Amoxicilline + Acide clavulanique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideClavulaniqueAmoxicillineAMC']], 'regroup' => 1); }
			if($resultatsABGPV['ticAcClavTCC']){ $groupeDESBLactamines[] = array('libelle'  => 'Ticarcilline + Acide clavulanique', 'resultat' => $resultatsPossibles[$resultatsABGPV['ticAcClavTCC']], 'regroup' => 1); }
			if($resultatsABGPV['ertapenemeETP']){ $groupeDESBLactamines[] = array('libelle'  => 'Ertap�n�me', 'resultat' => $resultatsPossibles[$resultatsABGPV['ertapenemeETP']], 'regroup' => 1); }
			if($resultatsABGPV['imipenemeIPM']){ $groupeDESBLactamines[] = array('libelle'  => 'Imip�n�me', 'resultat' => $resultatsPossibles[$resultatsABGPV['imipenemeIPM']], 'regroup' => 1); }
			if($resultatsABGPV['oxacillineOX']){ $groupeDESBLactamines[] = array('libelle'  => 'Oxacilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['oxacillineOX']], 'regroup' => 1); }
			if($resultatsABGPV['penicillineP']){ $groupeDESBLactamines[] = array('libelle'  => 'P�nicilline', 'resultat' => $resultatsPossibles[$resultatsABGPV['penicillineP']], 'regroup' => 1); }
			if($resultatsABGPV['cefalotineCF']){ $groupeDESBLactamines[] = array('libelle'  => 'C�falotine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefalotineCF']], 'regroup' => 1); }
			if($resultatsABGPV['cefoxitineFOX']){ $groupeDESBLactamines[] = array('libelle'  => 'C�foxitine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefoxitineFOX']], 'regroup' => 1); }
			if($resultatsABGPV['piperacillineTazobactamePPT']){ $groupeDESBLactamines[] = array('libelle'  => 'Pip�racilline tazobactame', 'resultat' => $resultatsPossibles[$resultatsABGPV['piperacillineTazobactamePPT']], 'regroup' => 1); }
			if($resultatsABGPV['cefotaximeCTX']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fotaxime', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefotaximeCTX']], 'regroup' => 1); }
			if($resultatsABGPV['cefsulodineCFS']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fsulodine', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefsulodineCFS']], 'regroup' => 1); }
			if($resultatsABGPV['CFP']){ $groupeDESBLactamines[] = array('libelle'  => 'C�fop�razone', 'resultat' => $resultatsPossibles[$resultatsABGPV['CFP']], 'regroup' => 1); }
			if($resultatsABGPV['ceftazidimeCAZ']){ $groupeDESBLactamines[] = array('libelle'  => 'C�ftazidime', 'resultat' => $resultatsPossibles[$resultatsABGPV['ceftazidimeCAZ']], 'regroup' => 1); }
			if($resultatsABGPV['ceftriaxoneCRO']){ $groupeDESBLactamines[] = array('libelle'  => 'C�ftriaxone', 'resultat' => $resultatsPossibles[$resultatsABGPV['ceftriaxoneCRO']], 'regroup' => 1); }
			if($resultatsABGPV['cefepimeFEP']){ $groupeDESBLactamines[] = array('libelle'  => 'C�f�pime', 'resultat' => $resultatsPossibles[$resultatsABGPV['cefepimeFEP']], 'regroup' => 1); }
			if($resultatsABGPV['aztreonamATM']){ $groupeDESBLactamines[] = array('libelle'  => 'Aztr�onam', 'resultat' => $resultatsPossibles[$resultatsABGPV['aztreonamATM']], 'regroup' => 1); }
			 
			$groupeDESPolymyxine = array();
			if($resultatsABGPV['colistineCS']){ $groupeDESPolymyxine[] = array('libelle'  => 'Colistine', 'resultat' => $resultatsPossibles[$resultatsABGPV['colistineCS']], 'regroup' => 2); }
			if($resultatsABGPV['polymicine']){ $groupeDESPolymyxine[] = array('libelle'  => 'Polymicine', 'resultat' => $resultatsPossibles[$resultatsABGPV['polymicine']], 'regroup' => 2); }
	
			$groupeDESAminosides = array();
			if($resultatsABGPV['kanamycineK']){ $groupeDESAminosides[] = array('libelle'  => 'Kanamycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['kanamycineK']], 'regroup' => 3); }
			if($resultatsABGPV['tobramycineTB']){ $groupeDESAminosides[] = array('libelle'  => 'Tobramycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['tobramycineTB']], 'regroup' => 3); }
			if($resultatsABGPV['gentamicineGM']){ $groupeDESAminosides[] = array('libelle'  => 'Gentamicine', 'resultat' => $resultatsPossibles[$resultatsABGPV['gentamicineGM']], 'regroup' => 3); }
			if($resultatsABGPV['amikacineAN']){ $groupeDESAminosides[] = array('libelle'  => 'Amikacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['amikacineAN']], 'regroup' => 3); }
			if($resultatsABGPV['netilmycine']){ $groupeDESAminosides[] = array('libelle'  => 'Netilmycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['netilmycine']], 'regroup' => 3); }
			 
			$groupeDESPhenicoles = array();
			if($resultatsABGPV['chloramphenicolC']){ $groupeDESPhenicoles[] = array('libelle'  => 'Chloramphenicol', 'resultat' => $resultatsPossibles[$resultatsABGPV['chloramphenicolC']], 'regroup' => 4); }
	
			$groupeDESCyclines = array();
			if($resultatsABGPV['minocycline']){ $groupeDESCyclines[] = array('libelle'  => 'Minocycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['minocycline']], 'regroup' => 5); }
			if($resultatsABGPV['tetracyclineTE']){ $groupeDESCyclines[] = array('libelle'  => 'T�tracycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['tetracyclineTE']], 'regroup' => 5); }
			if($resultatsABGPV['doxycyclineDO']){ $groupeDESCyclines[] = array('libelle'  => 'Doxycycline', 'resultat' => $resultatsPossibles[$resultatsABGPV['doxycyclineDO']], 'regroup' => 5); }
	
			$groupeDESMacrolidesEtApparentes = array();
			if($resultatsABGPV['azithromycineAZT']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Azithromycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['azithromycineAZT']], 'regroup' => 6); }
			if($resultatsABGPV['erythromycineE']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Erythromycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['erythromycineE']], 'regroup' => 6); }
			if($resultatsABGPV['lincomycineL']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Lincomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['lincomycineL']], 'regroup' => 6); }
			if($resultatsABGPV['pristinamycinePT']){ $groupeDESMacrolidesEtApparentes[] = array('libelle'  => 'Pristinamycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['pristinamycinePT']], 'regroup' => 6); }
	
			$groupeDESFluoroquinolones = array();
			if($resultatsABGPV['acideNalidixiqueNA']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Acide nalidixique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideNalidixiqueNA']], 'regroup' => 7); }
			if($resultatsABGPV['pefloxacinePEF']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'P�floxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['pefloxacinePEF']], 'regroup' => 7); }
			if($resultatsABGPV['norfloxacineNOR']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Norfloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['norfloxacineNOR']], 'regroup' => 7); }
			if($resultatsABGPV['ciprofloxacineCIP']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'Ciprofloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['ciprofloxacineCIP']], 'regroup' => 7); }
			if($resultatsABGPV['LEV']){ $groupeDESFluoroquinolones[] = array('libelle'  => 'L�vofloxacine', 'resultat' => $resultatsPossibles[$resultatsABGPV['LEV']], 'regroup' => 7); }
	
			$groupeDESImidazoles = array();
			if($resultatsABGPV['cotrimoxazoleSXT']){ $groupeDESImidazoles[] = array('libelle'  => 'Cotrimoxazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['cotrimoxazoleSXT']], 'regroup' => 8); }
	
			$groupeDesGlycopeptides = array();
			if($resultatsABGPV['vancomycineVA']){ $groupeDesGlycopeptides[] = array('libelle'  => 'Vancomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['vancomycineVA']], 'regroup' => 9); }
			if($resultatsABGPV['teicoplanine']){ $groupeDesGlycopeptides[] = array('libelle'  => 'Teicoplanine', 'resultat' => $resultatsPossibles[$resultatsABGPV['teicoplanine']], 'regroup' => 9); }
	
			$groupeDesProduitsNitres = array();
			if($resultatsABGPV['nitrofurane']){ $groupeDesProduitsNitres[] = array('libelle'  => 'Nitrofurane', 'resultat' => $resultatsPossibles[$resultatsABGPV['nitrofurane']], 'regroup' => 10); }
			if($resultatsABGPV['metronidazole']){ $groupeDesProduitsNitres[] = array('libelle'  => 'M�tronidazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['metronidazole']], 'regroup' => 10); }
			 
			$groupeDesSulfamides = array();
			if($resultatsABGPV['trimethoprimeSulfametoxazoleSXT']){ $groupeDesSulfamides[] = array('libelle'  => 'Trim�thoprime + Sulfam�toxazole', 'resultat' => $resultatsPossibles[$resultatsABGPV['trimethoprimeSulfametoxazoleSXT']], 'regroup' => 11); }
			 
			$groupeDesAutres = array();
			if($resultatsABGPV['fosfomycineFOS']){ $groupeDesAutres[] = array('libelle'  => 'Fosfomycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['fosfomycineFOS']], 'regroup' => 12); }
			if($resultatsABGPV['acideFusidiqueFA']){ $groupeDesAutres[] = array('libelle'  => 'Acide fusidique', 'resultat' => $resultatsPossibles[$resultatsABGPV['acideFusidiqueFA']], 'regroup' => 12); }
			if($resultatsABGPV['rifampicineRA']){ $groupeDesAutres[] = array('libelle'  => 'Rifampycine', 'resultat' => $resultatsPossibles[$resultatsABGPV['rifampicineRA']], 'regroup' => 12); }
	
			 
			$conclusion = $resultatsABGPV['conclusion'];
			 
	
			/* Affichage en colonne*/
			$this->affichageInfosEnColonneABGPV($groupeDESBLactamines, $groupeDESPolymyxine, $groupeDESAminosides, $groupeDESPhenicoles, $groupeDESCyclines, $groupeDESMacrolidesEtApparentes, $groupeDESFluoroquinolones, $groupeDESImidazoles, $groupeDesGlycopeptides, $groupeDesProduitsNitres, $groupeDesSulfamides, $groupeDesAutres, $conclusion);
			 
		}
		 
	}
	
	
	
}

?>
