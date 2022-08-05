<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/autoriser');

function formulaires_editer_media_charger_dist() {

	$valeurs = [
		'editable' => true,
		'_bigup_rechercher_fichiers' => true,
		'_etapes' => 2
	];

	$id_rubrique = lire_config('geol/secteur_medias', 1);

	// récupérer l'éventuel article de l'auteur en cours de rédaction dans le secteur des médias
	$id_article = sql_getfetsel(
		'id_article',
		"spip_articles AS article LEFT JOIN spip_auteurs_liens AS lien ON article.id_article=lien.id_objet AND lien.objet='article'",
		"article.statut='prepa' AND lien.id_auteur=" . intval($GLOBALS['visiteur_session']['id_auteur']) . " AND article.id_secteur=$id_rubrique"
	);

	if (!intval($id_article) and (autoriser('publierdans', 'rubrique', $id_rubrique) or autoriser('configurer'))) {
		$valeurs['editable'] = true;
	} elseif (autoriser('modifier', 'article', $id_article)) {
		$article = sql_fetsel('*', 'spip_articles', 'id_article=' . intval($id_article));
		$valeurs['id_article'] = $id_article;
		$valeurs['titre'] = $article['titre'];
		$valeurs['texte'] = $article['texte'];
		$valeurs['id_licence'] = $article['id_licence'];
		if (test_plugin_actif('cextras')) {
			// récupérer les saisies des extras automagiquement cf https://zone.spip.net/trac/spip-zone/changeset/99133/
			include_spip('inc/cextras');
			// pour ne pas se faire bloquer par champs_extras_autorisation() dans cextras_obtenir_saisies_champs_extras()
			// à cause de la restriction d'affichage des extras sur la rubrique
			set_request('id_rubrique', $id_rubrique);
			$saisies = cextras_obtenir_saisies_champs_extras('article', $id_article);
			$champs = saisies_lister_champs(champs_extras_saisies_lister_avec_sql($saisies), false);
			$valeurs['_extras'] = $saisies;
			foreach ($champs as $champ) {
				$valeurs[$champ] = $article[$champ];
			}
		}
		// récupérer les données de l'éventuel point lié
		$gis = sql_fetsel(
			'*',
			'spip_gis as gis LEFT JOIN spip_gis_liens as gis_liens ON gis.id_gis=gis_liens.id_gis',
			'gis_liens.objet="article" AND gis_liens.id_objet=' . intval($id_article)
		);
		if ($gis) {
			$valeurs['id_gis'] = $gis['id_gis'];
			$valeurs['lat'] = $gis['lat'];
			$valeurs['lon'] = $gis['lon'];
			$valeurs['zoom'] = $gis['zoom'];
		}
		// récupérer l'éventuel document lié à l'article en cours => étape 2
		$id_document = sql_getfetsel(
			'document.id_document',
			"spip_documents AS document LEFT JOIN spip_documents_liens AS lien ON lien.id_document=document.id_document AND lien.objet='article'",
			"lien.id_objet=$id_article"
		);
		if ($id_document) {
			set_request('_etape', 2);
		}
	} else {
		$valeurs['editable'] = false;
		$valeurs['message_erreur'] = _T('info_acces_interdit');
	}

	return $valeurs;
}

function formulaires_editer_media_verifier_etape_dist($etape) {

	$erreurs = [];
	$id_rubrique = lire_config('geol/secteur_medias', 1);
	$id_document = '';
	if ($id_article = _request('id_article')) {
		$id_document = sql_getfetsel(
			'document.id_document',
			"spip_documents AS document LEFT JOIN spip_documents_liens AS lien ON lien.id_document=document.id_document AND lien.objet='article'",
			"lien.id_objet=$id_article"
		);
	}

	if ($etape == 1) {
		// fichier obligatoire si aucun document
		if (!$id_document) {
			if (!isset($_FILES['media']) or !file_exists($_FILES['media']['tmp_name'])) {
				$erreurs['media'] = _T('info_obligatoire');
			} else {
				// créer l'article s'il n'y en a pas
				if (!intval($id_article)) {
					include_spip('action/editer_objet');
					$id_article = objet_inserer('article', $id_rubrique);
					set_request('id_article', $id_article);
					objet_instituer('article', $id_article, ['statut' => 'prepa']);
				}
				// lier le fichier en doc joint à l'article
				include_spip('action/ajouter_documents');
				$ajouter_document = charger_fonction('ajouter_un_document', 'action');
				$id_document = $ajouter_document('new', $_FILES['media'], 'article', $id_article, 'document');
				if (!intval($id_document)) {
					$erreurs['media'] = $id_document;
				} elseif (
					$id_gis = sql_getfetsel(
						'gis.id_gis',
						'spip_gis as gis LEFT JOIN spip_gis_liens as gis_liens ON gis.id_gis=gis_liens.id_gis',
						'gis_liens.objet="document" AND gis_liens.id_objet=' . intval($id_document)
					)
				) {
					// si le document a été géolocalisé à partir de ses exifs
					// supprimer l'éventuel point déjà lié à l'article
					if (
						$old_gis = sql_getfetsel(
							'gis.id_gis',
							'spip_gis as gis LEFT JOIN spip_gis_liens as gis_liens ON gis.id_gis=gis_liens.id_gis',
							'gis_liens.objet="article" AND gis_liens.id_objet=' . intval($id_article)
						)
					) {
						include_spip('action/editer_gis');
						gis_supprimer($old_gis);
					}
					// délier le point du document et le lier à l'article
					include_spip('action/editer_liens');
					objet_dissocier(['gis' => $id_gis], ['document' => $id_document]);
					objet_associer(['gis' => $id_gis], ['article' => $id_article]);
					set_request('id_gis', $id_gis);
				}
			}
		}
	}
	if ($etape == 2) {
		// ça ne devrait pas arriver mais on ne sait jamais...
		if (!_request('titre')) {
			$erreurs['titre'] = _T('info_obligatoire');
		}
		if (_request('supprimer_document')) {
			include_spip('action/dissocier_document');
			supprimer_lien_document($id_document, 'article', $id_article, true);
			set_request('aller_a_etape', 1);
			// supprimer le fichier temporaire du cache de bigup
			// bof, on doit pouvoir faire simple ?
			include_spip('inc/Bigup');
			$bigup = new \Spip\Bigup\Bigup(
				\Spip\Bigup\Identifier::depuisRequest()
			);
			$bigup->supprimer_fichiers();
		}
		if (test_plugin_actif('cextras')) {
			// vérifier les saisies des extras automagiquement
			include_spip('cextras_pipelines');
			set_request('id_rubrique', $id_rubrique);
			$objet = 'article';
			if ($saisies = champs_extras_objet($table = table_objet_sql($objet))) {
				include_spip('inc/autoriser');
				include_spip('inc/saisies');
				// restreindre les saisies selon les autorisations
				$saisies = champs_extras_autorisation('modifier', $objet, $saisies, ['id' => $id_article]);
				$erreurs = array_merge($erreurs, saisies_verifier($saisies));
			}
		}
	}

	return $erreurs;
}

function formulaires_editer_media_traiter_dist() {

	$res = [];
	$id_article = _request('id_article');

	include_spip('action/editer_objet');
	include_spip('action/editer_liens');

	// mettre à jour l'article et le publier
	objet_modifier('article', $id_article);
	objet_instituer('article', $id_article, ['statut' => 'publie']);

	// créer le point ou le mettre à jour
	$id_gis = _request('id_gis');
	if (!$id_gis and _request('lat')) {
		$id_gis = objet_inserer('gis');
	}
	if ($id_gis and intval($id_gis)) {
		objet_modifier('gis', $id_gis, [
			'lat' => _request('lat'),
			'lon' => _request('lon'),
			'zoom' => _request('zoom'),
			'titre' => _request('titre'),
			'objet' => 'article',
			'id_objet' => $id_article]);
	}

	include_spip('inc/invalideur');
	suivre_invalideur("id='id_article/$id_article'");

	// redirection
	include_spip('inc/headers');
	$res['message_ok'] = _T('info_modification_enregistree');
	$res['redirect'] = generer_url_entite($id_article, 'article');

	return $res;
}
