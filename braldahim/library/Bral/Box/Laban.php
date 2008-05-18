<?php

class Bral_Box_Laban extends Bral_Box_Box {

	function getTitreOnglet() {
		return "Laban";
	}

	function getNomInterne() {
		return "box_laban";
	}
	
	function getChargementInBoxes() {
		return false;
	}

	function setDisplay($display) {
		$this->view->display = $display;
	}

	function render() {
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanPotion");
		Zend_Loader::loadClass("LabanRune");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("Metier");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		unset($hobbitsMetiersTable);
		
		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall(null, "nom_masculin_metier");
		unset($metiersTable);
		$metiersRowset = $metiersRowset->toArray();
		$tabHobbitMetiers = null;
		$tabMetiers = null;
		
		foreach($metiersRowset as $m) {
			if ($this->view->user->sexe_hobbit == 'feminin') {
				$nom_metier = $m["nom_feminin_metier"];
			} else {
				$nom_metier = $m["nom_masculin_metier"];
			}
			
			$possedeMetier = false;
			foreach($hobbitsMetierRowset as $h) {
				if ($h["id_metier"] == $m["id_metier"]) {
					$possedeMetier = true;
					break;
				}
			}
			
			if ($possedeMetier == true) {
				$tabHobbitMetiers[$m["nom_systeme_metier"]] = array(
						"id_metier" => $m["id_metier"],
						"nom" => $nom_metier,
						"nom_systeme" => $m["nom_systeme_metier"],
						"a_afficher" => true,
					);
			} else {
				$tabMetiers[$m["nom_systeme_metier"]] = array(
					"id_metier" => $m["id_metier"],
					"nom" => $m["nom_masculin_metier"],
					"nom_systeme" => $m["nom_systeme_metier"],
					"a_afficher" => false,
				);
			}
		}
		unset($metiersRowset);
		
		$tabMineraisBruts = null;
		$tabLingots = null;
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanMineraiTable);
	
		foreach ($minerais as $m) {
			if ($m["quantite_brut_laban_minerai"] > 0) {
				$tabMineraisBruts[] = array(
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_brut_laban_minerai"],
				);
			
				if (isset($tabMetiers["mineur"])) {
					$tabMetiers["mineur"]["a_afficher"] = true; 
				}
			}
			if ($m["quantite_lingots_laban_minerai"] > 0) {
				$tabLingots[] = array(
					"type" => $m["nom_type_minerai"],
					"quantite" => $m["quantite_lingots_laban_minerai"],
				);
			
				if (isset($tabMetiers["forgeron"])) {
					$tabMetiers["forgeron"]["a_afficher"] = true; 
				}
			}
		}
		unset($minerais);

		$tabLaban = null;
		$labanTable = new Laban();
		$laban = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanTable);
		
		foreach ($laban as $p) {
			$tabLaban = array(
				"nb_peau" => $p["quantite_peau_laban"],
				"nb_viande" => $p["quantite_viande_laban"],
				"nb_viande_preparee" => $p["quantite_viande_preparee_laban"],
				"nb_ration" => $p["quantite_ration_laban"],
				"nb_cuir" => $p["quantite_cuir_laban"],
				"nb_fourrure" => $p["quantite_fourrure_laban"],
				"nb_planche" => $p["quantite_planche_laban"],
			);
			
			if ($p["quantite_peau_laban"] > 0 || $p["quantite_viande_laban"] > 0) {
				if (isset($tabMetiers["chasseur"])) {
					$tabMetiers["chasseur"]["a_afficher"] = true; 
				}
			}
			
			if ($p["quantite_viande_preparee_laban"] > 0 || $p["quantite_ration_laban"] > 0) {
				if (isset($tabMetiers["cuisinier"])) {
					$tabMetiers["cuisinier"]["a_afficher"] = true; 
				}
			}
			
			if ($p["quantite_cuir_laban"] > 0 || $p["quantite_fourrure_laban"] > 0) {
				if (isset($tabMetiers["tanneur"])) {
					$tabMetiers["tanneur"]["a_afficher"] = true; 
				}
			}

			if ($p["quantite_planche_laban"] > 0) {
				if (isset($tabMetiers["menuisier"])) {
					$tabMetiers["menuisier"]["a_afficher"] = true; 
				}
			}
		}
		unset($laban);
		
		$tabRunesIdentifiees = null;
		$tabRunesNonIdentifiees = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanRuneTable);

		foreach ($runes as $r) {
			if ($r["est_identifiee_rune"] == "oui") {
				$tabRunesIdentifiees[] = array(
					"id_rune" => $r["id_rune_laban_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
			} else {
				$tabRunesNonIdentifiees[] = array(
					"id_rune" => $r["id_rune_laban_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
				);
			}
		}
		unset($runes);

		$this->view->tabHobbitMetiers = $tabHobbitMetiers;
		$this->view->tabMetiers = $tabMetiers;
		
		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;
		
		$this->view->nb_runes = count($tabRunesIdentifiees) + count($tabRunesNonIdentifiees);
		$this->view->runesIdentifiees = $tabRunesIdentifiees;
		$this->view->runesNonIdentifiees = $tabRunesNonIdentifiees;
		$this->view->laban = $tabLaban;
		
		$this->renderPlante($tabMetiers);
		$this->renderEquipement();
		$this->renderPotion();
		
		$this->view->estEquipementsPotionsEtal = false;
		$this->view->estEquipementsPotionsEtalAchat = false;
		
		$this->view->nom_interne = $this->getNomInterne();
		
		unset($tabHobbitMetiers);
		unset($tabMetiers);
		unset($tabMineraisBruts);
		unset($tabLingots);
		unset($tabRunesIdentifiees);
		unset($tabRunesNonIdentifiees);
		
		return $this->view->render("interface/laban.phtml");
	}
	
	private function renderPlante($tabMetiers) {
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);
		
		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();
	
		$tabPartiePlantes = null;
		$tabPartiePlantesPreparees = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanPartiePlanteTable);
		
		foreach($typePartiePlantesRowset as $p) {
			foreach($typePlantesRowset as $t) {
				$val = false;
				if ($t["id_fk_partieplante1_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante2_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante3_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante4_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				
				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]])) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_systeme_type_plante' => $t["nom_systeme_type_plante"],
					);
					$tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]] = $tab;
				}
				
				$tabTypePlantes[$t["categorie_type_plante"]]["a_afficher"] = false;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["a_afficher"] = false;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["possible"] = $val;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = 0;
			}
		}
		unset($typePartiePlantesRowset);
		unset($typePlantesRowset);
		
		$tabTypePlantesBruts = $tabTypePlantes;
		$tabTypePlantesPrepares = $tabTypePlantes;
		
		foreach ($partiePlantes as $p) {
			if ($p["quantite_laban_partieplante"] > 0) {
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesBruts[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_laban_partieplante"];
				if (isset($tabMetiers["herboriste"])) {
					$tabMetiers["herboriste"]["a_afficher"] = true;
				}
			}
			
			if ($p["quantite_preparee_laban_partieplante"] > 0) {
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantesPrepares[$p["categorie_type_plante"]]["type_plante"][$p["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["quantite"] = $p["quantite_preparee_laban_partieplante"];
				if (isset($tabMetiers["apothicaire"])) {
					$tabMetiers["apothicaire"]["a_afficher"] = true; 
				}
			}
		}
		unset($partiePlantes);

		$this->view->typePlantesBruts = $tabTypePlantesBruts;
		$this->view->typePlantesPrepares = $tabTypePlantesPrepares;
	}
	
	private function renderEquipement() {
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanEquipementTable);
		
		$tabWhere = null;
		foreach ($equipements as $e) {
			$tabEquipements[$e["id_laban_equipement"]] = array(
					"id_equipement" => $e["id_laban_equipement"],
					"nom" => $e["nom_type_equipement"],
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"armure" => $e["armure_recette_equipement"],
					"force" => $e["force_recette_equipement"],
					"agilite" => $e["agilite_recette_equipement"],
					"vigueur" => $e["vigueur_recette_equipement"],
					"sagesse" => $e["sagesse_recette_equipement"],
					"vue" => $e["vue_recette_equipement"],
					"bm_attaque" => $e["bm_attaque_recette_equipement"],
					"bm_degat" => $e["bm_degat_recette_equipement"],
					"bm_defense" => $e["bm_defense_recette_equipement"],
					"suffixe" => $e["suffixe_mot_runique"],
					"runes" => array(),
			);
			$tabWhere[] = $e["id_laban_equipement"];
		}
		unset($equipements);
		
		if ($tabWhere != null) {
			Zend_Loader::loadClass("EquipementRune");
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($tabWhere);
			unset($equipementRuneTable);
			
			foreach($equipementRunes as $e) {
				$tabEquipements[$e["id_equipement_rune"]]["runes"][] = array(
					"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
					"id_fk_type_rune_equipement_rune" => $e["id_fk_type_rune_equipement_rune"],
					"nom_type_rune" => $e["nom_type_rune"],
					"image_type_rune" => $e["image_type_rune"],
					"effet_type_rune" => $e["effet_type_rune"],
				);
			}
			unset($equipementRunes);
		}
		
		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabEquipements;
	}
	
	private function renderPotion() {
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->view->user->id_hobbit);
		unset($labanPotionTable);
		
		foreach ($potions as $p) {
			$tabPotions[$p["id_laban_potion"]] = array(
					"id_potion" => $p["id_laban_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_laban_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
			);
		}
		unset($potions);
		
		$this->view->nb_potions = count($tabPotions);
		$this->view->potions = $tabPotions;
	}
}
