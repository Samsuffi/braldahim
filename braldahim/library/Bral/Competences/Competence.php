<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
abstract class Bral_Competences_Competence {
	
	protected $view;
	protected $reloadInterface = false;
	private $estEvenementAuto = true;
	private $evenementQueSurOkJet1 = true;
	private $detailEvenement = null;
	private $idTypeEvenement = null;
	
	function __construct($competence, $hobbitCompetence, $request, $view, $action) {
		Zend_Loader::loadClass("Bral_Util_Evenement");
		
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $competence["nom_systeme"];
		$this->competence = $competence;
		
		$this->view->jetUtilise = false;
		$this->view->balanceFaimUtilisee = false;
		
		$this->view->nb_px_commun = 0;
		
		$this->view->effetMotD = false;
		$this->view->effetMotE = false;
		$this->view->effetMotG = false;
		$this->view->effetMotH = false;
		$this->view->effetMotI = false;
		$this->view->effetMotJ = false;
		$this->view->effetMotL = false;
		$this->view->effetMotQ = false;
		
		// recuperation de hobbit competence
		$this->hobbit_competence = $hobbitCompetence;
		
		// si c'est une competence metier, on verifie que ce n'est pas utilise plus de 2 fois par DLA
		$this->view->nbActionMetierParDlaOk = $this->calculNbActionMetierParDlaOk();
		
		// si c'est une competence commune avec un jet de dé, on verifie qu'on ne peut gagner de PX plus de 2 fois par DLA
		$this->view->nbGainCommunParDlaOk = $this->calculNbGainCommunParDlaOk();
		
		$this->prepareCommun();
		$this->calculNbPa();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function getListBoxRefresh();

	public function getIdEchoppeCourante() {
		return false;
	}
	
	protected function calculNbPa() {
		if ($this->view->user->pa_hobbit - $this->competence["pa_utilisation"] < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
		$this->view->nb_pa = $this->competence["pa_utilisation"];
	}
	
	protected function ameliorationCompetenceMetier() {
		Zend_Loader::loadClass("HobbitsMetiers");
		
		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($this->view->user->id_hobbit);
		$ameliorationCompetence = false;
		foreach($hobbitsMetierRowset as $m) {
			if ($this->competence["id_fk_metier_competence"] == $m["id_metier"]) {
				if ($m["est_actif_hmetier"] == "oui") {
					$ameliorationCompetence = true;
				}
				break;
			}
		}
		return $ameliorationCompetence;
	}
	
	protected function getIdMetier() {
		if ($this->competence["type_competence"] == "metier") {
			return $this->competence["id_fk_metier_competence"];
		} else {
			return null;
		}
	}
	
	protected function calculPx() {
		$this->view->calcul_px_generique = true;
		if ($this->view->okJet1 === true && $this->view->nbGainCommunParDlaOk === true) {
			$this->view->nb_px_perso = $this->competence["px_gain"];
		} else {
			$this->view->nb_px_perso = 0;
		}
		$this->view->nb_px = $this->view->nb_px_perso + $this->view->nb_px_commun;
	}

	protected function calculBalanceFaim() {
		$this->view->balanceFaimUtilisee = true;
		$this->view->balance_faim = $this->competence["balance_faim"];
		$this->view->user->balance_faim_hobbit = $this->view->user->balance_faim_hobbit + $this->view->balance_faim;
		Bral_Util_Faim::calculBalanceFaim($this->view->user);
	}
	
	protected function calculPoids() {
		$this->view->user->poids_transporte_hobbit = Bral_Util_Poids::calculPoidsTransporte($this->view->user->id_hobbit, $this->view->user->castars_hobbit);
	}
	
	protected function calculJets() {
		$this->view->jetUtilise = true;
		$this->view->okJet1 = false; // jet de compétence
		$this->view->okJet2 = false; // jet amélioration de la compétence
		$this->calculJets1();
		$this->calculJets2et3();
		$this->majSuiteJets();
		$this->updateCompetenceNbAction();
		$this->updateCompetenceNbGain();
	}
	
	private function calculJets1() {
		// 1er Jet : réussite ou non de la compétence
		$this->view->jet1 = Bral_Util_De::get_1d100();
		if ($this->view->jet1 <= $this->hobbit_competence["pourcentage_hcomp"]) {
			$this->view->okJet1 = true;
		} else { // si le jet est manquee, on recalcule le cout en PA
			$this->view->nb_pa = $this->competence["pa_manquee"];
		}
	}

	private function calculJets2et3() {
		$this->view->jet2Possible = false;
		$this->view->estCompetenceMetier = false;
		if ($this->competence["type_competence"] == "metier") {
			$this->view->estCompetenceMetier = true;
			$this->view->ameliorationCompetenceMetierCourant = $this->ameliorationCompetenceMetier();
		}
		
		// a t-on le droit d'améliorer la compétence métier
		if ($this->view->estCompetenceMetier === true && $this->view->ameliorationCompetenceMetierCourant === false) { 
			$this->view->okJet2 = false;
			
		}  else if (($this->view->okJet1 === true || $this->hobbit_competence["pourcentage_hcomp"] < 50) && $this->hobbit_competence["pourcentage_hcomp"] < $this->competence["pourcentage_max"]) {
			// 2nd Jet : réussite ou non de l'amélioration de la compétence
			// seulement si la maitrise de la compétence est < 50 ou si le jet1 est réussi
			// et qu'on n'a pas le max de la compétence
			$this->view->jet2 = Bral_Util_De::get_1d100();
			$this->view->jet2Possible = true;
			if ($this->view->jet2 > $this->hobbit_competence["pourcentage_hcomp"]) {
				$this->view->okJet2 = true;
			}
		}

		// 3ème Jet : % d'amélioration de la compétence
		if ($this->view->okJet2 === true) {
			if ($this->hobbit_competence["pourcentage_hcomp"] < 50) {
				if ($this->view->okJet1 === true) {
					$this->view->jet3 = Bral_Util_De::get_1d6();
				} else {
					$this->view->jet3 = Bral_Util_De::get_1d3();
				}
			} else if ($this->hobbit_competence["pourcentage_hcomp"] < 75) {
				$this->view->jet3 = Bral_Util_De::get_1d3();
			} else if ($this->hobbit_competence["pourcentage_hcomp"] < 90) {
				$this->view->jet3 = Bral_Util_De::get_1d1();
			}
		}
	}

	// mise à jour de la table hobbit competence
	private function majSuiteJets() {
		if ($this->view->okJet2 === true) { // uniquement dans le cas de réussite du jet2
			$hobbitsCompetencesTable = new HobbitsCompetences();
			$pourcentage = $this->hobbit_competence["pourcentage_hcomp"] + $this->view->jet3;
			if ($pourcentage > $this->competence["pourcentage_max"]) { // % comp maximum
				$pourcentage = $this->competence["pourcentage_max"];
			}
			$data = array('pourcentage_hcomp' => $pourcentage);
			$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
			$hobbitsCompetencesTable->update($data, $where);
		}
	}

	/*
	 * Mise à jour des événements du hobbit / du monstre.
	 */
	protected function setDetailsEvenement($details, $idType) {
		$this->detailEvenement = $details;
		$this->idTypeEvenement = $idType;
	}
	
	/*
	 * Mise à jour des événements du hobbit / du monstre.
	 */
	protected function setEstEvenementAuto($flag) {
		$this->estEvenementAuto = $flag;
	}
	
	/*
	 * Mise à jour des événements du hobbit / du monstre.
	 */
	protected function setEvenementQueSurOkJet1($flag) {
		$this->evenementQueSurOkJet1 = $flag;
	}
	
	/*
	 * Mise à jour des événements du hobbit : type : compétence.
	 */
	private function majEvenementsStandard($detailsBot) {
		if ($this->estEvenementAuto === true) {
			if ($this->idTypeEvenement == null) {
				$this->idTypeEvenement = $this->view->config->game->evenements->type->competence;
			}
			if ($this->detailEvenement == null) {
				$this->detailEvenement = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a réussi l'utilisation d'une compétence";
			}
			if ($this->view->okJet1 === true || $this->evenementQueSurOkJet1 == false) {
				Bral_Util_Evenement::majEvenements($this->view->user->id_hobbit, $this->idTypeEvenement, $this->detailEvenement, $detailsBot);
			}
		}
	}
	
	/*
	 * Mise à jour des PA, des PX et de la balance de faim.
	 */
	protected function majHobbit() {
		Bral_Util_Faim::calculBalanceFaim($this->view->user);
		$this->view->user->pa_hobbit = $this->view->user->pa_hobbit - $this->view->nb_pa;
		$this->view->user->px_perso_hobbit = $this->view->user->px_perso_hobbit + $this->view->nb_px_perso;
		$this->view->user->px_commun_hobbit = $this->view->user->px_commun_hobbit + $this->view->nb_px_commun;
		
		if ($this->view->user->balance_faim_hobbit < 0) {
			$this->view->user->balance_faim_hobbit = 0;
		}
		
		if ($this->view->user->pa_hobbit  < 0) { // verif au cas où...
			$this->view->user->pa_hobbit = 0;
		}

		$this->view->changeNiveau = false;
		$this->calculNiveau();

		$data = array(
			'pa_hobbit' => $this->view->user->pa_hobbit,
			'px_perso_hobbit' => $this->view->user->px_perso_hobbit,
			'px_commun_hobbit' => $this->view->user->px_commun_hobbit,
			'pi_hobbit' => $this->view->user->pi_hobbit,
			'niveau_hobbit' => $this->view->user->niveau_hobbit,
			'pi_cumul_hobbit' => $this->view->user->pi_cumul_hobbit,
			'balance_faim_hobbit' => $this->view->user->balance_faim_hobbit,
			'nb_hobbit_kill_hobbit' => $this->view->user->nb_hobbit_kill_hobbit,
			'nb_monstre_kill_hobbit' => $this->view->user->nb_monstre_kill_hobbit,
			'x_hobbit' => $this->view->user->x_hobbit,
			'y_hobbit'  => $this->view->user->y_hobbit,
			'pv_restant_hobbit' => $this->view->user->pv_restant_hobbit,
			'pv_max_bm_hobbit' => $this->view->user->pv_max_bm_hobbit,
			'poids_transporte_hobbit' => $this->view->user->poids_transporte_hobbit,
			'poids_transportable_hobbit' => $this->view->user->poids_transportable_hobbit,
			'castars_hobbit' => $this->view->user->castars_hobbit,
			'force_bbdf_hobbit' => $this->view->user->force_bbdf_hobbit,
			'agilite_bbdf_hobbit' => $this->view->user->agilite_bbdf_hobbit,
			'vigueur_bbdf_hobbit' => $this->view->user->vigueur_bbdf_hobbit,
			'sagesse_bbdf_hobbit' => $this->view->user->sagesse_bbdf_hobbit,
			'agilite_bm_hobbit' => $this->view->user->agilite_bm_hobbit,
			'force_bm_hobbit' => $this->view->user->force_bm_hobbit,
			'vigueur_bm_hobbit' => $this->view->user->vigueur_bm_hobbit,
			'sagesse_bm_hobbit' => $this->view->user->sagesse_bm_hobbit,
			'duree_prochain_tour_hobbit' => $this->view->user->duree_prochain_tour_hobbit ,
			'armure_naturelle_hobbit' => $this->view->user->armure_naturelle_hobbit,
		);
		$where = "id_hobbit=".$this->view->user->id_hobbit;
		
		$hobbitTable = new Hobbit();
		$hobbitTable->update($data, $where);
		unset($hobbitTable);
		unset($data);
	}

	public function getNomInterne() {
		return "box_action";
	}

	public function render() {
		$this->view->competence = $this->competence;
		switch($this->action) {
			case "ask":
				$texte = $this->view->render("competences/".$this->nom_systeme."_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				
				return $this->view->render("competences/commun_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				$texte = $this->view->render("competences/".$this->nom_systeme."_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				
				$this->majEvenementsStandard(Bral_Helper_Affiche::copie($this->view->texte));
				return $this->view->render("competences/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	/**
	 * Le niveau suivant est calculé à partir d'un certain nombre de px perso
	 * qui doit être >= à :
	 * NiveauSuivantPX = NiveauSuivant x 5
	 */
	private function calculNiveau() {
		$niveauSuivantPx = ($this->view->user->niveau_hobbit + 1) * 5;
		if ($this->view->user->px_perso_hobbit >= $niveauSuivantPx) {
			$this->view->user->px_perso_hobbit = $this->view->user->px_perso_hobbit - $niveauSuivantPx;
			$this->view->user->niveau_hobbit = $this->view->user->niveau_hobbit + 1;
			$this->view->user->pi_cumul_hobbit = $this->view->user->pi_cumul_hobbit + $niveauSuivantPx;
			$this->view->user->pi_hobbit = $this->view->user->pi_hobbit + $niveauSuivantPx;
			$this->view->changeNiveau = true;
		}

		$niveauSuivantPx = ($this->view->user->niveau_hobbit + 1) * 5;
		if ($this->view->user->px_perso_hobbit >= $niveauSuivantPx) {
			$this->calculNiveau();
		}
	}
	
	protected function attaqueHobbit(&$hobbitAttaquant, $idHobbitCible, $effetMotSPossible = true) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$jetAttaquant = $this->calculJetAttaque($hobbitAttaquant);
		$jetsDegat = $this->calculDegat($hobbitAttaquant);
		$hobbitTable = new Hobbit();
		$hobbitRowset = $hobbitTable->find($idHobbitCible);
		$hobbitCible = $hobbitRowset->current();
		$jetCible = Bral_Util_Attaque::calculJetCibleHobbit($hobbitCible);
		$retourAttaque = Bral_Util_Attaque::attaqueHobbit(&$hobbitAttaquant, $hobbitCible, $jetAttaquant, $jetCible, $jetsDegat, $effetMotSPossible);
		$this->detailEvenement = $retourAttaque["details"];
		$this->idTypeEvenement =$retourAttaque["typeEvemenent"];
		return $retourAttaque;
	}
	
	protected function attaqueMonstre(&$hobbitAttaquant, $idMonstre) {
		Zend_Loader::loadClass("Bral_Util_Attaque");
		$jetAttaquant = $this->calculJetAttaque($hobbitAttaquant);
		$jetsDegat = $this->calculDegat($hobbitAttaquant);
		$monstreTable = new Monstre();
		$monstreRowset = $monstreTable->findById($idMonstre);
		$monstre = $monstreRowset;
		$jetCible = Bral_Util_Attaque::calculJetCibleMonstre($monstre);
		$retourAttaque = Bral_Util_Attaque::attaqueMonstre(&$hobbitAttaquant, $monstre, $jetAttaquant, $jetCible, $jetsDegat);
		$this->detailEvenement = $retourAttaque["details"];
		$this->idTypeEvenement =$retourAttaque["typeEvemenent"];
		return $retourAttaque;
	}
	
	private function updateCompetenceNbAction() {
		if ($this->view->okJet1 === true && $this->competence["type_competence"] == "metier") { // uniquement dans le cas de réussite du jet3
			$hobbitsCompetencesTable = new HobbitsCompetences();
			$data = array(
				'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_hobbit,
				'nb_action_tour_hcomp' => ($this->hobbit_competence["nb_action_tour_hcomp"] + 1),
			);
			$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
			$hobbitsCompetencesTable->update($data, $where);
		}
	}
	
	private function updateCompetenceNbGain() {
		if ($this->view->okJet1 === true && $this->competence["type_competence"] == "commun") { // uniquement dans le cas de réussite du jet3 et une compétence commune
			$hobbitsCompetencesTable = new HobbitsCompetences();
			$data = array(
				'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_hobbit,
				'nb_gain_tour_hcomp' => ($this->hobbit_competence["nb_gain_tour_hcomp"] + 1),
			);
			$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
			$hobbitsCompetencesTable->update($data, $where);
		}
	}
	
	private function calculNbActionMetierParDlaOk() {
		$retour = false;
		if ($this->competence["id_fk_metier_competence"] != null && $this->competence["id_fk_metier_competence"] > 0) {
			if ($this->view->user->date_debut_tour_hobbit == $this->hobbit_competence["date_debut_tour_hcomp"]) { 
				if ($this->hobbit_competence["nb_action_tour_hcomp"] >= 2) {
					$retour = false;
				} else { // < 2
					$retour = true;
				}
			} else { // premiere utilisation de la competence dans ce tour
				$retour = true;
				
				$hobbitsCompetencesTable = new HobbitsCompetences();
				$data = array(
					'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_hobbit,
					'nb_action_tour_hcomp' => 0,
				);
				$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
				$hobbitsCompetencesTable->update($data, $where);
			}
		} else { // competence non metier
			$retour = true;
		}
		return $retour;
	}
	
	private function calculNbGainCommunParDlaOk() {
		$retour = false;
		if ($this->competence["type_competence"] == "commun" && $this->competence["pourcentage_max"] < 100) {
			if ($this->view->user->date_debut_tour_hobbit == $this->hobbit_competence["date_debut_tour_hcomp"]) { 
				if ($this->hobbit_competence["nb_gain_tour_hcomp"] >= 2) {
					$retour = false;
				} else { // < 2
					$retour = true;
				}
			} else { // premiere utilisation de la competence dans ce tour
				$retour = true;
				
				$hobbitsCompetencesTable = new HobbitsCompetences();
				$data = array(
					'date_debut_tour_hcomp' => $this->view->user->date_debut_tour_hobbit,
					'nb_gain_tour_hcomp' => 0,
				);
				$where = array("id_fk_competence_hcomp = ".$this->hobbit_competence["id_fk_competence_hcomp"]." AND id_fk_hobbit_hcomp = ".$this->view->user->id_hobbit);
				$hobbitsCompetencesTable->update($data, $where);
			}
		} else { // competence non commune et soumise à un jet
			$retour = true;
		}
		return $retour;
	}
	
}
