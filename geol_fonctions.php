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
	$retour = [];
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
 * On cherche prive/vignettes/ext.svg dans le path.
 *
 * @param string $ext
 *     Extension du fichier. Exemple : png
 * @param string $media
 *     Permet de retourner une variante de la vignette adaptee au media
 *     cas des mp4 par exemple que l'on decline quand c'est un audio
 * @param bool $loop
 *     Autoriser la fonction à s'appeler sur elle-même
 *     (paramètre interne).
 * @return ?string
 *     null si l'image n'est pas trouvée
 *     Chaîne (chemin vers l'image) si on ne demande pas de taille
 */
function inc_vignette($ext, $media = '', $loop = true) {

	if (!$ext) {
		$ext = 'txt';
	}

	// chercher dans les vignettes personnalisées de geodiv
	// dans l'espace public & pour les fichiers qui proposent une vignette perso
	if (!test_espace_prive() and in_array(
		$ext,
		array_merge(_GEOL_FICHIERS_AUDIOS, _GEOL_FICHIERS_VIDEOS, _GEOL_FICHIERS_TEXTES)
	)) {
		return find_in_path('images/vignettes/' . $ext . '.png');
	}
	// Chercher la vignette correspondant a ce type de document
	// dans les vignettes persos, ou dans les vignettes standard
	# installation dans un dossier /vignettes personnel, par exemple /squelettes/vignettes
	if (!$media || !($v = find_in_path('prive/vignettes/' . $ext . '-' . $media . '.svg'))) {
		$v = find_in_path('prive/vignettes/' . $ext . '.svg');
	}
	if (!$v) {
		if ($loop) {
			$f = charger_fonction('vignette', 'inc');
			$v = $f('defaut', $media, false);
		} else {
			$v = false;
		}
	} # pas trouve l'icone de base

	return $v ?: null;
}
