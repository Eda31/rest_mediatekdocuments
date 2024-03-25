<?php
/**
 * Classe de connexion et d'exécution des requêtes dans une BDD MySQL
 */
class ConnexionPDO {

    private $conn = null;

    /**
     * constructeur privé : connexion à la BDD
     * @param string $login 
     * @param string $mdp
     * @param string $bd
     * @param string $serveur
     * @param int $port
     */
    public function __construct($login, $mdp, $bd, $serveur, $port){
        try {
            $this->conn = new PDO("mysql:host=$serveur;dbname=$bd;port=$port", $login, $mdp);
            $this->conn->query('SET CHARACTER SET utf8');
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Exécution d'une requête de mise à jour (insert, update, delete)
     * @param string $requete
     * @param array $param
     * @return résultat requête (booléen)
     */
    public function execute($requete, $param=null){
        try{	
            $requetePrepare = $this->conn->prepare($requete);
            if($param != null){
                foreach($param as $key => &$value){				
                    $requetePrepare->bindParam(":$key", $value);				
                }
            }	
            return $requetePrepare->execute();			
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * Exécution d'une requête select retournant 0 à plusieurs lignes
     * @param string $requete
     * @param array $param
     * @return lignes récupérées
     */
    public function query($requete, $param=null){
        try{
            $requetePrepare = $this->conn->prepare($requete);
            if($param != null){
                foreach($param as $key => &$value){
                    $requetePrepare->bindParam(":$key", $value);
                }
            }
            $reponse = $requetePrepare->execute();
            if($reponse == true){
                return $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
            }else{
                return false;
            } 
        }catch(Exception $e){
            return null;
        }		
    }
	
}