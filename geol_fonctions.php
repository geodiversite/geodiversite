<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Transforme une couleur hexa en vectorielle R,G,B
 *
 * @param string $couleur
 * @return string
 */
function geol_couleur_hex_to_dec($couleur) {
	include_spip('inc/filtres_images_mini');
	$couleur = couleur_html_to_hex($couleur);
	$couleur = preg_replace(',^#,', '', $couleur);
	$retour['red'] = hexdec(substr($couleur, 0, 2));
	$retour['green'] = hexdec(substr($couleur, 2, 2));
	$retour['blue'] = hexdec(substr($couleur, 4, 2));

	return implode(',', $retour);
}

/**
 * Définition du id_collection du plugin collections s'il n'est pas dispo
 */
if (!defined('_DIR_PLUGIN_GEOL_ALBUMS')) {
	function critere_id_collection_dist($idb, &$boucles, $crit) {
	}
}

/**
 * Vignette pour une extension de document
 *
 * Recherche les fichiers d'icones au format SVG pour l'extension demandée.
 * On cherche prive/vignettes/ext.png dans le path.
 *
 * Surcharge : dans l'espace public, pour les extensions acceptées par geodiv autres que images,
 * on cherche images/vignettes/ext.png dans le path.
 *
 * @param string $ext
 *     Extension du fichier. Exemple : png
 * @param bool $size
 *     true pour retourner un tableau avec les tailles de la vignette
 *     false pour retourner uniquement le chemin du fichier
 * @param bool $loop
 *     Autoriser la fonction à s'appeler sur elle-même
 *     (paramètre interne).
 * @return array|bool|string
 *     False si l'image n'est pas trouvée
 *     Chaîne (chemin vers l'image) si on ne demande pas de taille
 *     Tableau (chemin, largeur, hauteur) si on demande avec la taille.
 */
function inc_vignette($ext, $size = true, $loop = true) {

	if (!$ext) {
		$ext = 'txt';
	}

	// chercher dans les vignettes personnalisées de geodiv
	// dans l'espace public & pour les fichiers qui proposent une vignette perso
	if (!test_espace_prive() and in_array($ext, array_merge(_GEOL_FICHIERS_AUDIOS, _GEOL_FICHIERS_VIDEOS, _GEOL_FICHIERS_TEXTES))) {
		$v = find_in_path('images/vignettes/' . $ext . '.png');
	}
	// Chercher la vignette correspondant a ce type de document
	// dans les vignettes persos, ou dans les vignettes standard
	if (
		# installation dans un dossier /vignettes personnel, par exemple /squelettes/vignettes
		!isset($v) and !$v = find_in_path('prive/vignettes/' . $ext . '.svg')
	) {
		if ($loop) {
			$f = charger_fonction('vignette', 'inc');
			$v = $f('defaut', false, $loop = false);
		} else {
			$v = false;
		}
	} # pas trouve l'icone de base

	if (!$size) {
		return $v;
	}

	$largeur = $hauteur = 0;
	if ($v and $size = @spip_getimagesize($v)) {
		$largeur = $size[0];
		$hauteur = $size[1];
	}

	return [$v, $largeur, $hauteur];
}
