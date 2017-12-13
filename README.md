wizbii-tech-test
================
Objectifs
========

Le but de cet exercice est de refaire une petite partie de l'API que nous utilisons en interne pour stocker les mesures de type pageview, screenview ou event.
Cette API reprend les principes de l'API de Google Analytics en ajoutant quelques champs customisés nous permettant de mieux analyser le comportement de nos utilisateurs.

Les consignes de cet exercice :
* respecter la doc de l'API présente sur https://analytics.wizbii.com/doc.html
* stocker les données dans une base NoSQL, MongoDB de préférence
* réaliser le code Backend en PHP
* le code doit être disponible sur github ou bitbucket. Pas besoin d'avoir une version déployée et fonctionnelle, c'est un plus mais l'objectif est de discuter sur le code
* le framework symfony doit être utilisé
* le choix des outils, IDE, ... est laissé à ta convenance

Quelques règles métier à implémenter. Pas besoin de toutes les implémenter, le but est de voir comment elles ont été développées, pas de remplacer notre outil existant :)

1. pour éviter qu'une mesure ne soit collectée plusieurs fois par erreur, on estime qu'un évènement ne peut pas se produire plus d'une fois par seconde
2. si les champs mandatory ne sont pas fournis, l'API doit retourner une erreur : à toi de définir le format de cette erreur.
3. si le champ wci référence un utilisateur qui n'existe pas, l'API doit retourner une erreur. Pour cet exercice, cette liste sera mockée
4. si le champ qt est fourni et est supérieur à 3600, l'API doit retourner une erreur. Cette valeur doit être configurable
5. si la valeur du champ v est différente de 1, l'API doit retourner une erreur.


Analyse
==============

* Création de la classe Measurement (de type Document MongoDb)
* Implémentation des règles de l'API :
  * Par des contraintes de validations définies en annotations
  * pour les règles complexes (wct, wui, wuui, etc.), utilisation des "custom validators", librairie de détection de mobile
  * tests unitaires pour les "custom validators" et non pour la classe Measurement (testée en tests d'intégration)
* Création du contrôleur CollectController
  * Extraction des paramètres de la requête et création du ou des documents Measurement
    * GET : objet unique
    * POST : désérialisation JSON du tableau contenant les mesures
  * Validation du ou des documents
  * Mise en forme (sérialisation JSON) des erreurs éventuelles
  * Si validation OK, persistence du document dans la base de donnée.
  * Envoi de la réponse de l'API
* Tests d'intégration :
  * pour les paramètres obligatoires
  * pour les contraintes complexes (interdépendantes)
  * pour les règles métier

Notes sur l'implémentation des règles métier :
---------
* pour la règle 1 :
  * Soit utilisation du RateLimitBundle (pas sûr de pouvoir le paramètrer suffisamment finement)
  * Soit gestion perso
    * création d'un hash à partir des paramètres extraits de la requête (sauf z)
    * stockage dans base de donnée rapide ou cache, avec timestamp
    * à chaque requête, tester si hash présent et suffisant "ancien"
* pour les erreurs, retourner le tableau des erreurs de validation (chaque erreur contenant le nom du paramètre concerné et le message de non validation)
