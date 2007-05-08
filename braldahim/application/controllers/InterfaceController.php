<?php

class InterfaceController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->xml_response = new Bral_Xml_Response();
		$t = Bral_Box_Factory::getTour($this->_request, $this->view, false);
		if ($t->activer()) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			$xml_entry->set_valeur("informations");
			$xml_entry->set_data($t->render());
			$this->xml_response->add_entry($xml_entry);
			
			if ($this->_request->action != 'boxes') {
				$this->refreshAll();
			}
		}
	}
	
	function clearAction() {
		echo $this->view->render("interface/clear.phtml");
	}

	function indexAction() {
 		$this->render();
	}
	
	function vueAction() {
		$this->view->affichageInterne = true;
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("box_vue");
		$box = Bral_Box_Factory::getVue($this->_request, $this->view, true);
		$xml_entry->set_data($box->render());
		$this->xml_response->add_entry($xml_entry);
		$this->xml_response->render();
	}
	
	function boxesAction() {
		$this->addBox(Bral_Box_Factory::getProfil($this->_request, $this->view, false), "boite_a");
		$this->addBox(Bral_Box_Factory::getEquipement($this->_request, $this->view, false), "boite_a");
		
		$this->addBox(Bral_Box_Factory::getCompetencesCommun($this->_request, $this->view, false), "boite_b");
		$this->addBox(Bral_Box_Factory::getCompetencesBasic($this->_request, $this->view, false), "boite_b");
		$this->addBox(Bral_Box_Factory::getCompetencesMetier($this->_request, $this->view, false), "boite_b");
		
		$this->addBox(Bral_Box_Factory::getVue($this->_request, $this->view, false), "boite_c");
		
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$xml_entry->set_valeur("racine");
		$xml_entry->set_data($this->getBoxesData());
		
		$this->xml_response->add_entry($xml_entry);
		$this->xml_response->render();
	}
	
	private function addBox($p, $position = "aucune") {
   		$this->m_list[$position][] = $p;
	}
	
	private function getBoxesData() {
		$r = $this->getDataList("boite_a");
		$r .= $this->getDataList("boite_b");
		$r .= $this->getDataList("boite_c");
		return $r;
	}
	
	private function getDataList($nom) {
		 $l = $this->m_list[$nom];
		 $liste = "";
		 $data = "";
		 $onglets = null;
		 
		 if ($nom != "aucune") {
		 	for ($i = 0; $i < count($l); $i ++) {
		 		if ($i == 0) {
		 			$css = "actif";
		 		} else {
		 			$css = "inactif";
		 		}
		 		
		 		$tab = array ("titre" => $l[$i]->getTitreOnglet(), "nom" => $l[$i]->getNomInterne(), "css" => $css);	
		 		$onglets[] = $tab;
		 		$liste .= $l[$i]->getNomInterne();
		 		if ($i < count($l)-1 ) {
		 			$liste .= ",";
		 		}
		 	}
		 	
		 	 for ($i = 0; $i < count($l); $i ++) {
		 	 	 if ($i == 0) {
		 	 	 	$display = "block";
		 	 	 } else {
		 	 	 	$display = "none";
		 	 	 }
		 	 	 
		 	 	 $l[$i]->setDisplay($display);
		 	 	 $data .= $l[$i]->render();
		 	 }
		 	 
		 	 $this->view->onglets = $onglets;
		 	 $this->view->liste = $liste;
		 	 $this->view->data = $data;
		 	 $this->view->conteneur = $nom;
		 	 return $this->view->render("interface/box_onglets.phtml");
		 }
	}
	
	private function refreshAll() {
		$boxToRefresh = array("box_profil", "box_vue", "box_competences_communes", "box_competences_basiques", "box_competences_metiers");
		for ($i=0; $i<count($boxToRefresh); $i++) {
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
			$xml_entry->set_valeur($c->getNomInterne());
			$xml_entry->set_data($c->render());
			$this->xml_response->add_entry($xml_entry);
		}
	}
}

