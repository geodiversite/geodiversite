<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// Activer HTML5 depuis le squelette
$GLOBALS['meta']['version_html_max'] = 'html5';

if (!isset($GLOBALS['z_blocs'])) {
	$GLOBALS['z_blocs'] = [
		'content',
		'extra',
		'head',
		'head_js',
		'header',
		'footer',
		'breadcrumb'
	];
}

// urls prorpes en minuscules
define('_url_minuscules', 1);

// extensions autorisées à l'upload
if (!defined('_GEOL_FICHIERS_IMAGES')) {
	define('_GEOL_FICHIERS_IMAGES', ['gif', 'jpg' , 'png']);
}
if (!defined('_GEOL_FICHIERS_AUDIOS')) {
	define('_GEOL_FICHIERS_AUDIOS', ['mp3']);
}
if (!defined('_GEOL_FICHIERS_VIDEOS')) {
	define('_GEOL_FICHIERS_VIDEOS', ['mp4']);
}
if (!defined('_GEOL_FICHIERS_TEXTES')) {
	define('_GEOL_FICHIERS_TEXTES', ['kml']);
}

define('_GEOL_UPLOAD_EXTENSIONS', implode(',', array_map(fn($extension) => '.' . $extension, array_merge(
	_GEOL_FICHIERS_IMAGES,
	_GEOL_FICHIERS_AUDIOS,
	_GEOL_FICHIERS_VIDEOS,
	_GEOL_FICHIERS_TEXTES
))));

// largeur de la prévisu lors de l'upload
if (!defined('_GEOL_PREVISU_LARGEUR')) {
	define('_GEOL_PREVISU_LARGEUR', 710);
}

define('_TAILLE_MAX_GRAVATAR', 200);

// autoriser le prive uniquement pour les admins
if (!function_exists('autoriser_ecrire')) {
	function autoriser_ecrire($faire, $type, $id, $qui, $opt) {
		return $qui['statut'] == '0minirezo';
	}
}

// permettre aux rédacteurs de publier dans le secteur des médias
function autoriser_rubrique_publierdans($faire, $type, $id, $qui, $opt) {
	if (($qui['statut'] == '0minirezo') and (!$qui['restreint'] or !$id or in_array($id, $qui['restreint']))) {
		// cas par défaut de SPIP
		return true;
	} elseif (
		$id_secteur = sql_getfetsel('id_secteur', 'spip_rubriques', 'id_rubrique=' . intval($id))
		and $id_secteur == lire_config('geol/secteur_medias', 1)
		and in_array($qui['statut'], ['0minirezo', '1comite'])
	) {
		// surcharge geodiv
		return true;
	} else {
		return false;
	}
}

// permettre aux auteurs de modifier leurs articles, mêmes publiés
function autoriser_article_modifier($faire, $type, $id, $qui, $opt) {
	$r = sql_fetsel('id_rubrique,statut', 'spip_articles', 'id_article=' . sql_quote($id));

	return
		$r
		and in_array($qui['statut'], ['0minirezo']) or
		(
			autoriser('publierdans', 'rubrique', $r['id_rubrique'], $qui, $opt)
			and auteurs_objet('article', $id, 'id_auteur=' . $qui['id_auteur']) // surcharge geodiv
			or (
				(!isset($opt['statut']) or $opt['statut'] !== 'publie')
				and in_array($qui['statut'], ['0minirezo', '1comite'])
				and in_array($r['statut'], ['prop', 'prepa', 'poubelle'])
				and auteurs_objet('article', $id, 'id_auteur=' . $qui['id_auteur'])
			)
		);
}

// surcharger autoriser_rubrique_modifier_dist pour y reproduire autoriser_rubrique_publierdans_dist
// puisque diogene surcharge autoriser_rubrique_publierdans et permet donc aux rédacteurs de modifier les rubriques
function autoriser_rubrique_modifier($faire, $type, $id, $qui, $opt) {
	return
		($qui['statut'] == '0minirezo')
		and (
			!$qui['restreint'] or !$id
			or in_array($id, $qui['restreint'])
		);
}

// surcharger autoriser_modererforum_article pour ne pas envoyer de notifs avec un lien vers le privé aux rédacteurs
function autoriser_modererforum($faire, $type, $id, $qui, $opt) {
	return autoriser_ecrire($faire, $type, $id, $qui, $opt);
}

// surcharger l'autorisation de selecteur_generique pour l'autocomplete de spipicious
// puisqu'on bloque l'accès à l'espace privé pour les rédacteurs
function autoriser_tag_autocomplete_dist($faire, $type, $id, $qui, $opt) {
	return isset($qui['id_auteur']) and $qui['id_auteur'] > 0;
}