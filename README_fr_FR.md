Lozeil
======

[Lozeil](http://lozeil.org) est une **application web PHP de gestion de trésorerie.** Son objectif est d'améliorer et simplifier la manière dont vous gérez votre argent.
Cela implique l'importation du journal de banque à partir de formats standards, du contrôle en 'temps réel', des statistiques, des simulations... <br /> 
Lozeil possède un système d'apprentissage qui va garder trace des choix que vous faites pour ensuite les faire à votre place de manière _intelligente/automatique_.

## Installation

### Pré-requis
* Serveur supportant PHP
* Base de données Mysql

### Mettre en place votre propre Lozeil

D'abord, faites un clone du repo git en lançant :
```bash
git clone git://github.com/noparking/lozeil.git
```

Installez Lozeil en lançant bot.php situé dans cli/, donnez les informations de la base de données et créez un utilisateur par défaut :
```bash
php bot.php --setup
```

Vous êtes prêt! Lancez index.php pour tester votre installation.

## Utilisation

Dans la mesure où ces étapes ne sont pas indépendantes, elles doivent être faites dans l'ordre<br />
La navigation est réalisée grâce au menu supérieur, accessible grâce à l'onglet 'Plus' .

### Importer votre journal de banque
* Format supportés: OFX, QIF


La seule étape à réaliser avant de pouvoir utiliser Lozeil est d'exporter votre journal de banque dans un des formats supporté, directement depuis le site de celle-ci.<br />
Dîtes à Lozeil quelle banque vous voulez manipuler en utilisant le menu supérieur, gérer les banques. Ajoutez la en utilisant le formulaire et n'oubliez pas de cocher la case correspondante, sinon celle-ci ne sera pas prise en considération.<br />
La prochaine étape est d'importer votre journal dans Lozeil. Pour se faire, allez dans le menu supérieur et choisissez votre fichier en cliquant sur 'Importer votre journal de banque'. Sélectionnez votre banque et 'Ok'.<br />
Vous êtes maintenant redirigé vers vos enregistrements. Vous pouvez également apprécier la timeline qui vous donne un bon recul sur ce qu'il se passe dans votre trésorerie.

Si vous utilisez une autre source de paiement, vous serez aussi capable de l'importer en utilisant 'Importer depuis une autre source'
* Sources supportées: Paybox .csv

### Catégorisez et affinez
Allez dans 'gérer les catégories' à l'aide du menu supérieur. Dans cette section vous pouvez définir autant de catégories que vous le souhaitez ainsi qu'un taux de TVA par défaut  (e.g., télécommunication - 19.6, frais bancaires - 0.0, salaires - 0.0, activité A - 19.6, activité B - 5.5... etc)<br />
Vous aurez aussi besoin de créer une catégorie de TVA par défaut, qui sera utilisée pour le calcul automatique de la TVA.<br />
Une fois terminé, retournez sur votre tableau de trésorerie et catégorisez manuellement autant d'enregistrements que vous le pouvez en utilisant les cases à cocher et le menu déroulant au bas du tableau ou un par un en utilisant l’icône crayon.
Cette étape est essentielle car c'est comme cela que Lozeil apprend à vous connaitre.<br />

De temps en temps, votre banque n'est pas aussi précise que ce que vous espéreriez. Certains enregistrements sont enfaite la fusion de plusieurs enregistrements (e.g. Remises de chèques). Pour résoudre ce problème utilisez l'icône de séparation qui apparaît au survole d'une ligne.

### Construisez votre budget prévisionnel
A partir de votre budget actuel vous pouvez construire votre prévisionnel. Pour se faire, utilisez l'icône 'plus' qui apparaît au survole d'une ligne. Dîtes à Lozeil la fréquence de cet enregistrement et il créera les enregistrements correspondants qui ne seront pas affiliés à la banque.

### Statistiques
Les statistiques sont accessibles en passant par 'consulter les statistiques', vous pouvez avoir une supervision précise de vos mouvements d'argent triés par catégories et banques évoluant par jour, semaine ou mois.

### Simulations
Voyez comment des nouveaux revenus/dépenses pourraient impacter votre trésorerie et votre budget prévisionnel.

### Admirez l'apprentissage de Lozeil
Pour cela, vous devez importer votre journal de banque à une date postérieure de celle de votre dernier import, possédant de nouvelles transactions. Vous remarquerez que la catégorisation est réalisée automatiquement. Si certaines lignes ne le sont pas, c'est tout à fait normal, cela est dû au fait qu'elle est ambiguë ou inconnue. Continuez à faire la catégorisation manuellement pour entrainer Lozeil.

### Rapprochement bancaire
La dernière étape dans le processus de Lozeil est le rapprochement bancaire. Pour se faire, vous devez fusionner les mêmes enregistrements provenant de votre banque et de votre prévisionnel. Les filtres vous aideront à les trouver. Une fois que vous en avez trouvé un, glissez-déposez une ligne au dessus de l'autre pour les fusionner.<br />

## Lancer les tests unitaires

Pour lancer les tests unitaires vous devez mettre à jour le submodule simpletest
```bash
git submodule init
```
```bash
git submodule update
```

Une fois terminé, vous êtes prêt ! Les tests sont situés dans tests/unit/.

## Liens

* Dépôt: git://github.com/noparking/lozeil.git
* Simpletest: <https://github.com/simpletest/simpletest>
* Lozeil.org: <http://lozeil.org>
* Bug: > to-do lien vers bug tracker..