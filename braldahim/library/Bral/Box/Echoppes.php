<?php

class Bral_Box_Echoppes {
	
	function __construct($request, $view, $interne) {
		$this->_request = $request;
		$this->view = $view;
		$this->view->affichageInterne = $interne;
	}
	
	function getTitreOnglet() {
		return "Echoppes";
	}
	
	function getNomInterne() {
		return "box_echoppes";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Region");
		
		$regionTable = new Region();
		$regions = $regionTable->fetchAll(null, 'nom_region');
		$regions = $regions->toArray();
		
		$regionCourante = null;
		foreach ($regions as $r) {
			if ($r["x_min_region"]<=$this->view->user->x_hobbit && 
			$r["x_max_region"]>=$this->view->user->x_hobbit && 
			$r["y_min_region"]<=$this->view->user->y_hobbit && 
			$r["y_max_region"]>=$this->view->user->y_hobbit) {
				$regionCourante = $r;
				break;
			}
		}
		
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabEchoppes = null;
		foreach($echoppesRowset as $e) {
			$tabEchoppes[] = array(
			"id_echoppe" => $e["id_echoppe"],
			"x_echoppe" => $e["x_echoppe"],
			"y_echoppe" => $e["y_echoppe"],
			"id_metier" =>  $e["id_metier"],
			"id_region" => $e["id_region"],
			"nom_region" => $e["nom_region"]
			);
		}
		
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$tabMetiers = null;
		$tabMetierCourant = null;
		$this->view->constructionPossible = false;

		foreach($hobbitsMetierRowset as $m) {
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			
			$regionsMetier = null;
			$tabEchoppesMetier = null;
			foreach ($regions as $r) {
				$regionMetier = null;
				$regionMetier["nom_region"] = $r["nom_region"];
				$regionMetier["id_region"] = $r["id_region"];
				$regionMetier["echoppe"] = null;
				if (count($tabEchoppes) > 0) {
					foreach($tabEchoppes as $e) {
						if ($e["id_metier"] == $m["id_metier"] && 
							$r["id_region"] == $e["id_region"]) {
							$regionMetier["echoppe"] = $e;
						}
					}
				}
				$regionsMetier[] = $regionMetier;
			}
			
			$t = array("id_metier" => $m["id_metier"],
			"nom_metier" => $nom_metier,
			"nom_systeme" => $m["nom_systeme_metier"],
			"est_actif" => $m["est_actif_hmetier"],
			"regions" => $regionsMetier,
			);
			
			if ($m["construction_echoppe_metier"] == "oui") {
				$tabMetiers[] = $t;
			}
			
			if ($m["est_actif_hmetier"] == "oui") {
				$tabMetierCourant = $t;
				if ($m["construction_echoppe_metier"] == "oui") {
					$this->view->constructionPossible = true;
				} else {
					$this->view->constructionPossible = false;
				}
			}
		}
		
		$this->view->tabRegionCourante = $regionCourante;
		$this->view->tabMetierCourant = $tabMetierCourant;
		$this->view->tabMetiers = $tabMetiers;
		
		$this->view->echoppes = $tabEchoppes;
		$this->view->nEchoppes = count($tabEchoppes);
		
		$this->view->nom_interne = $this->getNomInterne();
		
		$this->view->htmlMenu = $this->view->render("interface/echoppes/menu.phtml");
		$this->view->htmlContenu = $this->view->render("interface/echoppes/liste_echoppes.phtml");
		
		return $this->view->render("interface/echoppes.phtml");
	}
	
}
