<?php
include_once("Connexion.php");

/**
 * Classe qui sollicite ConnexionBDD pour l'accès à la BDD MySQL
 * Elle contient les méthodes appelées par Controle
 * et les méthodes abstraites que MyAccessBDD doit redéfinir pour construire les requêtes
 */
abstract class AccessBDD
{
    /**
     * @var Connexion
     */
    protected $conn = null;
    
    /**
     * constructeur : récupère les variables d'environnement
     * et récupère l'instance de connexion à la BDD
     */
    protected function __construct()
    {
        try {
            // récupération des variables d'environnement de l'accès à la BD
            $login = htmlspecialchars($_ENV['BDD_LOGIN'] ?? '');
            $pwd = htmlspecialchars($_ENV['BDD_PWD'] ?? '');
            $bd = htmlspecialchars($_ENV['BDD_BD'] ?? '');
            $server = htmlspecialchars($_ENV['BDD_SERVER'] ?? '');
            $port = htmlspecialchars($_ENV['BDD_PORT'] ?? '');
            // création de la connexion à la BDD
            $this->conn = Connexion::getInstance($login, $pwd, $bd, $server, $port);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * demande de traitement de la demande
     * @param string $methodeHTTP
     * @param string $table
     * @param string|null $id
     * @param array|null $champs
     * @return array|int|null
     */
    public function demande(string $methodeHTTP, string $table, ?string $id, ?array $champs) : array|int|null
    {
        if (is_null($this->conn)) {
            return null;
        }
        switch ($methodeHTTP) {
            case 'GET' :
                return $this->traitementSelect($table, $champs);
            case 'POST' :
                return $this->traitementInsert($table, $champs);
            case 'PUT' :
                return $this->traitementUpdate($table, $id, $champs);
            case 'DELETE' :
                return $this->traitementDelete($table, $champs);
            default :
                return null;
        }
    }
    
    abstract protected function traitementSelect(string $table, ?array $champs) : ?array;
    abstract protected function traitementInsert(string $table, ?array $champs) : ?int;
    abstract protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int;
    abstract protected function traitementDelete(string $table, ?array $champs) : ?int;

}
