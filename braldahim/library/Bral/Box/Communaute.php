<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Communaute extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Communaut&eacute;";
	}

	function getNomInterne() {
		return "box_communaute";
	}
	
	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		if ($this->view->affichageInterne) {
			$this->prepareData();
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/communaute.phtml");
	}
	
	private function prepareData() {
		Zend_Loader::loadClass('Bral_Util_Communaute');
		
		$communaute = null;
		
		if ($this->view->user->id_fk_communaute_braldun != null) {
			Zend_Loader::loadClass("Communaute");
			$communauteTable = new Communaute();
			$communaute = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
			if (count($communaute) == 1) {
				$communaute = $communaute[0];
			} else {
				$communaute = null;
			}
			$estDansCommunaute = true;
		}
		
		$this->prepareBatiments($communaute);
		
		$this->view->communaute = $communaute;
	}
	
	private function prepareBatiments($communaute) {
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Bral_Helper_Communaute');
		
		$lieuTable = new Lieu();
		$batiments = $lieuTable->findByIdCommunaute($communaute['id_communaute']);
		$tabBatiments = null;
		foreach($batiments as $b) {
			$tabBatiments[] = array(
				'batiment' => $b,
				'couts' => Bral_Util_Communaute::getCoutsAmeliorationBatiment($b["niveau_prochain_lieu"]),
			);
		}
		$this->view->batiments = $tabBatiments;
	}
}
