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
class StatsExperience extends Zend_Db_Table {
	protected $_name = 'stats_experience';
	protected $_primary = array('id_stats_experience');

	function findTop10($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->from('stats_experience', 'SUM(nb_px_perso_gagnes_stats_experience) as nombre');
		$select->where('id_fk_hobbit_stats_experience = id_hobbit');
		$select->where('mois_stats_experience >= ?', $dateDebut);
		$select->where('mois_stats_experience < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_hobbit', 'prenom_hobbit', 'id_hobbit'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByFamille($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', null);
		$select->from('stats_experience', 'SUM(nb_px_perso_gagnes_stats_experience) as nombre');
		$select->from('nom', 'nom');
		$select->where('id_fk_hobbit_stats_experience = id_hobbit');
		$select->where('id_nom = id_fk_nom_initial_hobbit');
		$select->where('mois_stats_experience >= ?', $dateDebut);
		$select->where('mois_stats_experience < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByNiveau($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_experience', array('SUM(nb_px_perso_gagnes_stats_experience) as nombre', 'floor(niveau_hobbit_stats_experience/10) as niveau'));
		$select->where('mois_stats_experience >= ?', $dateDebut);
		$select->where('mois_stats_experience < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau_hobbit_stats_experience'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findBySexe($dateDebut, $dateFin) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('hobbit', 'sexe_hobbit');
		$select->from('stats_experience', 'SUM(nb_px_perso_gagnes_stats_experience) as nombre');
		$select->where('id_fk_hobbit_stats_experience = id_hobbit');
		$select->where('mois_stats_experience >= ?', $dateDebut);
		$select->where('mois_stats_experience < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_hobbit'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}