<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Insertion dans le pipeline insert_head_css (SPIP)
 *
 * Ajout de la feuille de styles calculée de geodiversite
 *
 * @param string $flux
 * @return string
 */
function geol_insert_head_css($flux) {
	$flux .= '<link rel="stylesheet" href="' . produire_fond_statique(
		'css/geol.css',
		['ltr' => $GLOBALS['spip_lang_left'],
		]
	) . '" type="text/css" media="all" />';
	return $flux;
}

/**
 * Insertion dans le pipeline styliser (SPIP)
 *
 * Par défaut, appliquer le squelette de la composition 'page' aux pages uniques
 *
 * @param array $flux
 * @return array
 */
function geol_styliser($flux) {
	// Si c'est un squelette ayant rapport avec un article
	if (
		isset($flux['args']['contexte']['type-page'])
		and $flux['args']['contexte']['type-page'] == 'article'
		// Et qu'il porte la composition automatique des pages uniques
		and isset($flux['args']['contexte']['composition'])
		and strpos($flux['args']['contexte']['composition'], 'pageunique-') === 0
		// Et qu'il existe un squelette du même fond, mais avec le suffixe "page"
		and $f = find_in_path($flux['args']['fond'] . '-page.' . $flux['args']['ext'])
	) {
		// Alors ok, on l'utilise
		$flux['data'] = substr($f, 0, -strlen('.' . $flux['args']['ext']));
	}
	return $flux;
}

/**
 * Insertion dans le pipeline recuperer_fond (SPIP)
 *
 * Ajouter le script leaflet.geodiv.js au squelette du script de GIS
 *
 * @param array $flux
 * @return array
 */
function geol_recuperer_fond($flux) {
	if ($flux['args']['fond'] == 'javascript/gis.js') {
		$flux['data']['texte'] .= "\n\n(function() { L.gisConfig.getInfowindowUrl = '" . url_absolue(
			generer_url_public('get_infowindow')
		) . "'; })();";
		$flux['data']['texte'] .= "\n\n" . spip_file_get_contents(find_in_path('javascript/leaflet.geodiv.js'));
		$flux['data']['texte'] .= "\n\n" . spip_file_get_contents(find_in_path('javascript/leaflet.photon.js'));
	}
	return $flux;
}

/**
 * Insertion dans le pipeline formulaire_charger (SPIP)
 *
 * Surcharge du sujet et le texte du message généré par le formulaire_ecrire_auteur
 * Surcharge du formulaire d'inscription pour ne pas afficher l'explication
 *
 * @param array $flux
 * @return array
 */
function geol_formulaire_charger($flux) {
	// sujet perso pour formulaire_ecrire_auteur depuis une page article (erreur de localisation)
	if ($flux['args']['form'] == 'ecrire_auteur' and $flux['args']['args'][1] != '') {
		$flux['data']['sujet_message_auteur'] .= supprimer_tags(
			extraire_multi($GLOBALS['meta']['nom_site'])
		) . ' : ' . _T('geol:sujet_erreur_localisation');
		$flux['data']['texte_message_auteur'] .= _T('geol:depuis_page') . ' : ' . generer_objet_url_absolue(
			$flux['args']['args'][1],
			'article'
		) . "\n\nMessage :\n\n";
	}
	// pas d'explicaltion sur le form d'inscription
	if ($flux['args']['form'] == 'inscription' and $flux['args']['args'][0] == '1comite') {
		$flux['data']['_commentaire'] = '';
	}
	// limiter le form de polyhierarchie sur la branche des categories (dans le public)
	// cf http://zone.spip.org/trac/spip-zone/changeset/41280
	if ($flux['args']['form'] == 'editer_polyhierarchie' and !test_espace_prive()) {
		$flux['data']['limite_branche'] = lire_config('geol/secteur_categories', 2);
	}
	return $flux;
}

/**
 * Insertion dans le pipeline pre_boucle (SPIP)
 *
 * Forcer le critère {tout} sur les boucles rubriques
 *
 * @param array $boucle
 * @return array
 */
function geol_pre_boucle($boucle) {
	if ($boucle->type_requete == 'rubriques' and !isset($boucle->modificateur['criteres']['statut'])) {
		$boucle->modificateur['criteres']['statut'] = true;
	}
	return $boucle;
}

/**
 * Insertion dans le pipeline xmlrpc_methodes (xmlrpc)
 * Ajout de méthodes xml-rpc spécifiques à Geodiversite
 *
 * @param array $flux : un array des methodes déjà présentes, fonctionnant sous la forme :
 * -* clé = nom de la méthode;
 * -* valeur = le nom de la fonction à appeler;
 * @return array : l'array complété avec nos nouvelles méthodes
 */
function geol_xmlrpc_methodes($flux) {
	$flux['geodiv.liste_medias'] = 'geodiv_liste_medias';
	$flux['geodiv.lire_media'] = 'geodiv_lire_media';
	$flux['geodiv.creer_media'] = 'geodiv_creer_media';
	$flux['geodiv.update_media'] = 'geodiv_update_media';
	return $flux;
}

/**
 * Insertion dans le pipeline xmlrpc_server_class (xmlrpc)
 * Ajout de fonctions spécifiques utilisées par le serveur xml-rpc
 */
function geol_xmlrpc_server_class($flux) {
	include_spip('inc/geol_xmlrpc');
	return $flux;
}

/**
 * Insertion dans le pipeline menus_utiles (menus)
 *
 * Proposer les menus de la page d'accueil à la création
 *
 * @param array $menus
 * @return array
 */
function geol_menus_utiles($menus) {
	$menus['home_bloc1'] = _T('geol:nav_explorer');
	$menus['home_bloc2'] = _T('geol:nav_contribuer');
	$menus['home_bloc3'] = _T('geol:rechercher');
	return $menus;
}
