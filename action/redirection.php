<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Script pour rediriger vers l'url publique d'un objet
 *
 **/
function action_redirection_dist() {
	$type = _request('type');
	$id = intval(_request('id'));
	$url = generer_objet_url_absolue($id, $type, '', '', true);
	redirige_par_entete($url);
}
