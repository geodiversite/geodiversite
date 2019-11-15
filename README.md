# Géodiversité

Géodiversité, ou geodiv pour les intimes, est un squelette pour SPIP qui permet de mettre en place une galerie de médias géoréférencés.


## Quelques sites qui utilisent geodiv

- https://www.geodiversite.net/
- https://www.lestaxinomes.org/
- https://peluche.umontpellier.fr/
- https://trektic.org/
- https://cornes.debru.me/
- http://www.sequoia-act.net/
- http://www.streetart-brest.fr/
- http://www.portal.zoo.bio.br/
- http://inventaire.eau-et-rivieres.org/
- http://geodiversidade.net/

## Installation

Plusieurs méthodes d'installation sont disponibles :

- le script [`install_geodiv.sh`](https://github.com/geodiversite/geodiversite_install_sh) pour installer Gédiversité par svn ;
- le plugin [geodiversite_install](https://github.com/geodiversite/geodiversite_install) permet d'installer Géodiversité et ses dépendances à l'aide de SVP depuis l'espace privé de SPIP.

## Fichier `.htaccess`

Géodiversité utilise des urls personnalisées, voici les règles à ajouter à votre fichier `.htaccess` pour les prendre en charge :

	# urls geodiv
	RewriteRule ^media([0-9]+)?$		spip.php?page=article&id_article=$1 [QSA,L]
	RewriteRule ^cat([0-9]+)$		spip.php?page=rubrique&id_rubrique=$1 [QSA,L]
	RewriteRule ^tag([0-9]+)$		spip.php?page=mot&id_mot=$1 [QSA,L]
	RewriteRule ^auteur([0-9]+)$		spip.php?page=auteur&id_auteur=$1 [QSA,L]
	RewriteRule ^ticket([0-9]+)$		spip.php?page=ticket&id_ticket=$1 [QSA,L]
	RewriteRule ^album([0-9]+)$		spip.php?page=album&id_collection=$1 [QSA,L]
	RewriteRule ^newsletter([0-9]+)$	spip.php?page=newsletter&id_newsletter=$1 [QSA,L]

## Version pour SPIP 3.0

La version compatible avec SPIP 3.0 est disponible dans la branche [spip30](https://github.com/geodiversite/geodiversite_monolithe/tree/spip30) du dépôt archive.
