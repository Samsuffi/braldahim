<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Quete_Factory {
	static function getAction($request, $view) {
	 	Zend_Loader::loadClass("Bral_Quete_Quete");
		$matches = null;
		preg_match('/(.*)_quete_(.*)/', $request->get("caction"), $matches);
		$action = $matches[1]; // "do" ou "ask"
		$nomSystemeAction = $matches[2];
		$construct = null;

		if ($view->user->activation == false && $nomSystemeAction != 'voir') {
			throw new Zend_Exception("Tour non activé");
		}
		
		$construct = "Bral_Quete_".Bral_Util_String::firstToUpper($nomSystemeAction);
		try {
			Zend_Loader::loadClass($construct);
		} catch(Exception $e) {
			throw new Zend_Exception("Bral_Quete_Factory construct invalide (classe): ".$nomSystemeAction);
		}
		 
		// verification que la classe de l'action existe.
		if (($construct != null) && (class_exists($construct))) {
			return new $construct ($nomSystemeAction, $request, $view, $action);
		} else {
			throw new Zend_Exception("Bral_Quete_Factory action invalide: ".$nomSystemeAction);
		}
	}

	static function getVoir($request, $view, $idQuete) {
		Zend_Loader::loadClass("Bral_Quete_Quete");
		Zend_Loader::loadClass("Bral_Quete_Voir");
		return new Bral_Quete_Voir("voir", $request, $view, "ask", $idQuete);
	}
}