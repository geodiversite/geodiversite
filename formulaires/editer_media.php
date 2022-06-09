<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/autoriser');

function formulaires_editer_media_charger(){

	$valeurs = array(
		'editable' => true,
		'_bigup_rechercher_fichiers' => true,
		'_etapes' => 2
	);
	$id_rubrique = lire_config('geol/secteur_medias', 1);

	// récupérer l'éventuel article de l'auteur en cours de rédaction dans le secteur des médias
	$id_article = sql_getfetsel('id_article', "spip_articles AS article LEFT JOIN spip_auteurs_liens AS lien ON article.id_article=lien.id_objet AND lien.objet='article'", "article.statut='prepa' AND lien.id_auteur=" . intval($GLOBALS['visiteur_session']['id_auteur']) . " AND article.id_secteur=$id_rubrique");

	if (!intval($id_article) and (autoriser('publierdans', 'rubrique', $id_rubrique) or autoriser('configurer'))) {
		$valeurs['editable'] = true;
	} elseif (autoriser('modifier', 'article', $id_article)) {
		$article = sql_fetsel('*','spip_articles','id_article=' . intval($id_article));
		$valeurs['id_article'] = $id_article;
		$valeurs['titre'] = $article['titre'];
		$valeurs['texte'] = $article['texte'];
		$valeurs['id_licence'] = $article['id_licence'];
		$gis = sql_fetsel('*',
			'spip_gis as gis LEFT JOIN spip_gis_liens as gis_liens ON gis.id_gis=gis_liens.id_gis',
			'gis_liens.objet="article" AND gis_liens.id_objet='.intval($id_article)
		);
		if ($gis) {
			$valeurs['id_gis'] = $gis['id_gis'];
			$valeurs['lat'] = $gis['lat'];
			$valeurs['lon'] = $gis['lon'];
			$valeurs['zoom'] = $gis['zoom'];
		}
	} else {
		$valeurs['editable'] = false;
		$valeurs['message_erreur'] = _T('info_acces_interdit');
	}

	return $valeurs;
}


function formulaires_editer_media_verifier(){

	$erreurs = array();
	
	// ça ne devrait pas arriver mais on ne sait jamais...
	if (!_request('titre')) {
		$erreurs['titre'] = _T('info_obligatoire');
	}

	return $erreurs;
}


function formulaires_editer_media_traiter(){
	
	$res = array();
	$id_rubrique = lire_config('geol/secteur_medias', 1);
	
	include_spip('action/editer_objet');
	include_spip('action/editer_liens');
	
	// créer l'article et le publier
	if (!intval($id_article)) {
		$id_article = objet_inserer('article', $id_rubrique);
		objet_instituer('article', $id_article, array('statut' => 'publie'));
	}
	// sinon les mettre à jour
	objet_modifier('article', $id_article);

	// créer le point ou le mettre à jour
	$id_gis = _request('id_gis');
	if (!$id_gis and _request('lat')) {
		$id_gis = objet_inserer('gis');
	}
	if ($id_gis and intval($id_gis)) {
		objet_modifier('gis', $id_gis, array(
			'lat' => _request('lat'),
			'lon' => _request('lon'),
			'zoom' => _request('zoom'),
			'titre' => _request('titre'),
			'objet'=> 'article',
			'id_objet' => $id_article)
		);
	}

	include_spip('inc/invalideur');
	suivre_invalideur("id='id_article/$id_article'");

	// redirection
	include_spip('inc/headers');
	$res['message_ok'] = _T('info_modification_enregistree');
	$res['redirect'] = generer_url_entite($id_article,'article');
	
	return $res;
}
