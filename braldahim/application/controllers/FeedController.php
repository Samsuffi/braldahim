<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class FeedController extends Zend_Controller_Action {

	public function indexAction(){
		 
		Zend_Loader::loadClass("Zend_Feed");
		Zend_Loader::loadClass("InfoJeu");
		 
		$infoJeuTable = new InfoJeu();
		$infos = $infoJeuTable -> fetchAll(null, "date_info_jeu desc");

		$feedArray = array(
            'title' => "Braldahim - Chronique", 
            'link' => 'http://www.braldahim.com',
            'charset' => 'utf-8', 
            'description' => "La Chronique de Braldahim",
            'author' => 'Thains - Braldahim', 
            'email' => 'webmaster@braldahim.com',
            'copyright' => 'Braldahim.com', 
            'generator' => 'Zend Framework Zend_Feed',
            'language' => 'fr', 
            'entries' => array() 
		);

		foreach ($infos as $info) {
			$texte = Bral_Util_BBParser::bbcodeReplace($info->text_info_jeu);
			if ($info->lien_info_jeu != null) {
				$text .= "<br><a href='".$info["lien_info_jeu"]."'>Discussions</a>";
				$lien = $info->lien_info_jeu;
			} else {
				$lien = "";
			}
			$feedArray['entries'][] = array(
                'title' => substr(strip_tags($texte), 0, 40)."...", 
                'link' => $info->lien_info_jeu,
                'description' => $texte,
                'content' => $texte,
				'pubDate' => $info->date_info_jeu,
				'guid' => $info->id_info_jeu,
			);
		}
		$feed = Zend_Feed::importArray($feedArray,'rss');
		$feed -> send();
	}
}