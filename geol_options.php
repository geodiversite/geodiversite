<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

// Activer HTML5 depuis le squelette
$GLOBALS['meta']['version_html_max'] = 'html5';

if (!isset($GLOBALS['z_blocs'])) {
	$GLOBALS['z_blocs'] = array(
		'content',
		'extra',
		'head',
		'head_js',
		'header',
		'footer',
		'breadcrumb'
	);
}

// urls prorpes en minuscules
define ('_url_minuscules',1);

// largeur de la prévisu lors de l'upload
if (!defined('_GEOL_PREVISU_LARGEUR')) {
	define('_GEOL_PREVISU_LARGEUR', 710);
}

// autoriser le prive uniquement pour les admins
function autoriser_ecrire($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo';
}

// permettre aux rédacteurs de publier dans le secteur des médias
function autoriser_rubrique_publierdans($faire, $type, $id, $qui, $opt) {
	if (($qui['statut'] == '0minirezo') and (!$qui['restreint'] or !$id or in_array($id, $qui['restreint']))) {
		// cas par défaut de SPIP
		return true;
	} elseif (
		$id_secteur = sql_getfetsel('id_secteur', 'spip_rubriques', 'id_rubrique=' . intval($id))
		and $id_secteur == lire_config('geol/secteur_medias', 1)
		and in_array($qui['statut'], array('0minirezo', '1comite'))
	) {
		// surcharge geodiv
		return true;
	} else {
		return false;
	}
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

// surcharger autoriser_ecrire_ticket_dist sinon autoriser_ecrire passe avant
function autoriser_ticket_ecrire($faire, $type, $id, $qui, $opt) {
	include_spip('inc/tickets_autoriser');
	return autoriser_ticket_ecrire_dist($faire, $type, $id, $qui, $opt);
}

// surcharger autoriser_modererforum_article pour ne pas envoyer de notifs avec un lien vers le privé aux rédacteurs
function autoriser_modererforum($faire, $type, $id, $qui, $opt) {
	return autoriser_ecrire($faire, $type, $id, $qui, $opt);
}

define('_TAILLE_MAX_GRAVATAR',200);
