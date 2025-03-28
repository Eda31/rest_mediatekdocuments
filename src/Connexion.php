<?php
/**
 * Classe de connexion à la BDD MySQL (singleton)
 * et d'exécution des requêtes en retournant :
 * - pour les requêtes LID : contenu du curseur au format tableau associatif
 * - pour les requêtes LMD : nbre d'enregistrements impactés
 * Dans tous les cs, 'null' est renvoyé si la requête échpie.
 */
class Connexion
{
    /**
     * @var Connexion
     */
    private static $instance = null;
    /**
     * @var \PDO
     */
    private $conn = null;

    /**
     * constructeur privé : connexion à la BDD
     * @param string $login
     * @param string $pwd
     * @param string $bd
     * @param string $server
     * @param string $port
     */
    private function __construct(string $login, string $pwd, string $bd, string $server, string $port)
    {
        try {
            $this->conn = new \PDO("mysql:host=$server;dbname=$bd;port=$port", $login, $pwd);
            $this->conn->query('SET CHARACTER SET utf8');
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * méthode statique de création de l'instance unique
     * @param string $login
     * @param string $pwd
     * @param string $bd
     * @param string $server
     * @param string $port
     * @return Connexion instance unique de la classe
     */
    public static function getInstance(string $login, string $pwd, string $bd, string $server, string $port) : Connexion
    {
        if (self::$instance === null) {
            self::$instance = new Connexion($login, $pwd, $bd, $server, $port);
        }
        return self::$instance;
    }

    /**
     * exécute une requête de mise à jour (insert, update, delete)
     * @param string $requete
     * @param array|null $param
     * @return int|null nombre de lignes affectées ou null si erreur
     */
    public function updateBDD(string $requete, ?array $param=null) : ?int
    {
        try {
            $result = $this->prepareRequete($requete, $param);
            $reponse = $result->execute();
            if ($reponse === true) {
                return $result->rowCount();
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * exécute une requête select retournant 0 à plusieurs lignes
     * @param string $requete
     * @param array|null $param
     * @return array|null lignes récupérées ou null si erreur
     */
    public function queryBDD(string $requete, ?array $param=null) : ?array
    {
        try {
            $result = $this->prepareRequete($requete, $param);
            $reponse = $result->execute();
            if ($reponse === true) {
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
	
    /**
     * prépare la requête
     * @param string $requete
     * @param array|null $param
     * @return \PDOStatement requête préparée
     */
    private function prepareRequete(string $requete, ?array $param=null) : \PDOStatement
    {
        try {
            $requetePrepare = $this->conn->prepare($requete);
            if ($param !== null && is_array($param)) {
                foreach ($param as $key => &$value) {
                    $requetePrepare->bindParam(":$key", $value);
                }
            }
            return $requetePrepare;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
}