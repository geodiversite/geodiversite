<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

define('URLS_GEODIV_EXEMPLE', 'media12');

/**
 * Generer l'url d'un objet SPIP
 * @param int $id
 * @param string $objet
 * @param string $args
 * @param string $ancre
 * @return string
 */
function urls_geodiv_generer_url_objet_dist(int $id, string $objet, string $args = '', string $ancre = ''): string {
	if ($generer_url_externe = charger_fonction_url($objet, 'defaut')) {
		$url = $generer_url_externe($id, $args, $ancre);
		// une url === null indique "je ne traite pas cette url, appliquez le calcul standard"
		// une url vide est une url vide, ne rien faire de plus
		if (!is_null($url)) {
			return $url;
		}
	}

	if ($objet == 'forum') {
		if ($generer_url_externe = charger_fonction('generer_url_forum', 'urls', true)) {
			return $generer_url_externe($id, $args, $ancre);
		}
		return '';
	}

	if ($objet == 'document') {
		include_spip('inc/documents');
		return generer_url_document_dist($id, $args, $ancre);
	}

	if ($objet == 'article') {
		return _DIR_RACINE . 'media' . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
	}

	if ($objet == 'rubrique') {
		return _DIR_RACINE . 'cat' . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
	}

	if ($objet == 'mot') {
		return _DIR_RACINE . 'tag' . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
	}

	if ($objet == 'auteur') {
		return _DIR_RACINE . $objet . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
	}

	if ($objet == 'collection') {
		return _DIR_RACINE . 'album' . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
	}

	return _DIR_RACINE . $objet . $id . ($args ? "?$args" : '') . ($ancre ? "#$ancre" : '');
}

/**
 * Decoder une url
 * retrouve le fond et les parametres d'une URL abregee
 * le contexte deja existant est fourni dans args sous forme de tableau
 *
 * @param string $url
 * @param string $entite
 * @param array $contexte
 * @return array([contexte],[type],[url_redirect],[fond]) : url decodee
 */
function urls_geodiv_decoder_url_dist(string $url, string $entite, array $contexte = []): array {
	$contexte = $GLOBALS['contexte'] ?? []; // recuperer aussi les &debut_xx

	// traiter les injections du type domaine.org/spip.php/cestnimportequoi/ou/encore/plus/rubrique23
	if ($GLOBALS['profondeur_url'] > 0 and $entite == 'sommaire') {
		return [[],'404'];
	}

	// décoder l'url html, page ou standard
	$objets = 'article|breve|rubrique|mot|auteur|site|syndic|media|cat|tag|collection|album';
	if (
		preg_match(
			',^(?:[^?]*/)?(' . $objets . ')([0-9]+)(?:\.html)?([?&].*)?$,',
			$url,
			$regs
		)
		or preg_match(
			',^(?:[^?]*/)?(' . $objets . ')\.php3?[?]id_\1=([0-9]+)([?&].*)?$,',
			$url,
			$regs
		)
		or preg_match(
			',^(?:[^?]*/)?(?:spip[.]php)?[?](' . $objets . ')([0-9]+)(&.*)?$,',
			$url,
			$regs
		)
	) {
		switch ($regs[1]) {
			case 'media':
				$regs[1] = 'article';
				break;
			case 'cat':
				$regs[1] = 'rubrique';
				break;
			case 'tag':
				$regs[1] = 'mot';
				break;
			case 'album':
				$regs[1] = 'collection';
				break;
		}
		$type = preg_replace(',s$,', '', table_objet($regs[1]));
		$_id = id_table_objet($regs[1]);
		$id_objet = $regs[2];
		$contexte[$_id] = $id_objet;
		if ($type == 'syndic') {
			$type = 'site';
		}
		return [$contexte, $type, null, $type];
	}
	// on ne peut plus rien...
	return [];
}
