<?php
/*
 * Index.php : point d'entrée de l'API
 * - contrôle l'authentification
 * - Récupère les variables envoyées (dans l'URL ou le body)
 * - récupère la méthode d'envoi HTTP (GET, POST, PUT, DELETE)
 * - demande au contrôleur de gérer la demande
 */
include_once ("Url.php");
include_once("Controle.php");

// crée l'objet d'accès aux informations de l'URL qui sollicite l'API
$url = Url::getInstance();
// crée l'objet d'accès au contrôleur
$controle = new Controle();

// vérifie l'authentification
if (!$url->authentification()) {
    // l'authentification a échoué
    $controle->unauthorized();
} else {
    // récupère la méthode HTTP utilisée pour accéder à l'API
    $methodeHTTP = $url->recupMethodeHTTP();
    //récupère les données passées dans l'url (visibles ou cachées)
    $table = $url->recupVariable("table");
    $id = $url->recupVariable("id");
    $champs = $url->recupVariable("champs", "json");
    // demande au controleur de traiter la demande
    $controle->demande($methodeHTTP, $table, $id, $champs);
}
