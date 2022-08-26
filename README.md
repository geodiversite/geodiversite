# Géodiversité

Géodiversité, ou geodiv pour les intimes, est un squelette pour SPIP qui permet de mettre en place une galerie de médias géoréférencés.


## Quelques sites qui utilisent geodiv

- https://www.geodiversite.net/
- https://www.lestaxinomes.org/
- https://peluche.umontpellier.fr/
- https://trektic.org/
- https://cornes.debru.me/
- https://www.portal.zoo.bio.br/
- https://inventaire.eau-et-rivieres.org/
- https://geodiversidade.net/
- https://nounours.umontpellier.fr/
- https://www.biodivaquart.fr/
- https://litternature.umontpellier.fr/

## Installation

Plusieurs méthodes d'installation sont disponibles :

- le script [`install_geodiv.sh`](https://github.com/geodiversite/geodiversite_install_sh) pour installer Gédiversité par git ;
- le plugin [geodiversite_install](https://github.com/geodiversite/geodiversite_install) permet d'installer Géodiversité et ses dépendances à l'aide de SVP depuis l'espace privé de SPIP.

## Fichier `.htaccess`

Géodiversité utilise des urls personnalisées, voici les règles à ajouter à votre fichier `.htaccess` pour les prendre en charge :

	# urls geodiv
	RewriteRule ^media([0-9]+)?$		spip.php?page=article&id_article=$1 [QSA,L]
	RewriteRule ^cat([0-9]+)$		spip.php?page=rubrique&id_rubrique=$1 [QSA,L]
	RewriteRule ^tag([0-9]+)$		spip.php?page=mot&id_mot=$1 [QSA,L]
	RewriteRule ^auteur([0-9]+)$		spip.php?page=auteur&id_auteur=$1 [QSA,L]
	RewriteRule ^album([0-9]+)$		spip.php?page=album&id_collection=$1 [QSA,L]
	RewriteRule ^newsletter([0-9]+)$	spip.php?page=newsletter&id_newsletter=$1 [QSA,L]

## Anciennes versions

- la version compatible avec SPIP 3.2 est dans la branche [v3](https://github.com/geodiversite/geodiversite/tree/v3)
- la version historique (fonctionnant avec les plugins emballe_medias & diogene) compatible avec SPIP 3.2 est dans la branche [v2](https://github.com/geodiversite/geodiversite/tree/v2)
- la version compatible avec SPIP 3.0 est disponible dans la branche [spip30](https://github.com/geodiversite/geodiversite_monolithe/tree/spip30) du dépôt archive.
