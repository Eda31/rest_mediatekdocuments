<?php
header('Content-Type: application/json');
include_once("Controle.php");
$controle = new Controle();

// Contrôle de l'authentification
if(!isset($_SERVER['PHP_AUTH_USER']) || (isset($_SERVER['PHP_AUTH_USER']) && 
        !(($_SERVER['PHP_AUTH_USER']=='admin' && ($_SERVER['PHP_AUTH_PW']=='adminpwd'))))){
    $controle->unauthorized();
    
}else{
    
    // récupération des données
    // Nom de la table au format string
    $table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_STRING) ??
             filter_input(INPUT_POST, 'table', FILTER_SANITIZE_STRING);
    // id de l'enregistrement au format string
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING) ??
          filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
    // nom et valeur des champs au format json
    $champs = filter_input(INPUT_GET, 'champs', FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES) ??
               filter_input(INPUT_POST, 'champs', FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
    if($champs != ""){
        $champs = json_decode($champs, true);
    }

    // traitement suivant le verbe HTTP utilisé
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        $controle->get($table, $champs);
    }else if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $controle->post($table, $champs);
    }else if($_SERVER['REQUEST_METHOD'] === 'PUT'){
        $controle->put($table, $id, $champs);
    }else if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        $controle->delete($table, $champs);
    }

}