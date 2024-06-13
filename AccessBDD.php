<?php
include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD
{
    public $login="root";
    public $mdp="";
    public $bd="mediatek86";
    public $serveur="localhost";
    public $port="3306";
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct()
    {
        try {
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table)
    {
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "exemplaire" :
                    return $this->selectExemplairesRevue();
                case "genre" :
                case "public" :
                case "rayon" :
                case "etat" :
                    // select portant sur une table contenant juste id et libelle
                    return $this->selectTableSimple($table);
                default:
                    // select portant sur une table, sans condition
                    return $this->selectTable($table);
            }
        }else{
            return null;
        }
    }

    /**
     * récupération des lignes concernées
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de recherche
     * @return lignes répondant aux critères de recherches
     */
    public function select($table, $champs){
        if($this->conn != null && $champs != null){
            switch($table){
                case "exemplaire" :
                    return $this->selectExemplairesRevue($champs['id']);
                // cases ajouter
                case "commande":
                    return $this->selectCommandesLivre($champs['id']);
                default:
                    // cas d'un select sur une table avec recherche sur des champs
                    return $this->selectTableOnConditons($table, $champs);
            }
        }else{
                return null;
        }
    }

    /**
     * récupération de toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return lignes triées sur lebelle
     */
    public function selectTableSimple($table)
    {
        $req = "select * from $table order by libelle;";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table
     * @return toutes les lignes de la table
     */
    public function selectTable($table)
    {
        $req = "select * from $table;";
        return $this->conn->query($req);
    }
    
    /**
     * récupération des lignes d'une table dont les champs concernés correspondent aux valeurs
     * @param type $table
     * @param type $champs
     * @return type
     */
    public function selectTableOnConditons($table, $champs){
        // construction de la requête
        $requete = "select * from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-3);
        return $this->conn->query($requete, $champs);
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues(){
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectExemplairesRevue($id){
        $param = array(
                "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function delete($table, $champs){
        if($this->conn != null){
            switch ($table) {
                case "commande":
                    return $this->deleteCommande($champs['id']);
                default :
                    // construction de la requête
                    $requete = "delete from $table where ";
                    foreach ($champs as $key => $value){
                        $requete .= "$key=:$key and ";
                    }
                    // (enlève le dernier and)
                    $requete = substr($requete, 0, strlen($requete)-5);
                    return $this->conn->execute($requete, $champs);
            }
        }else{
            return null;
        }
    }

    /**
     * ajout d'une ligne dans une table
     * code modifier, ajout d'un switch
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            switch ($table) {
                // cases ajouter
                case "commande":
                    return $this->insertOneCommande($champs);
                default:
                    // construction de la requête
                    $requete = "insert into $table (";
                    foreach ($champs as $key => $value){
                        $requete .= "$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete)-1);
                    $requete .= ") values (";
                    foreach ($champs as $key => $value){
                        $requete .= ":$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete)-1);
                    $requete .= ");";
                    return $this->conn->execute($requete, $champs);
                }
        }else{
            return null;
        }
    }

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */
    public function updateOne($table, $id, $champs){
        switch ($table) {
            // case ajouter
            case "commande":
                return $this->updateCommande($id, $champs);
            default :
                if($this->conn != null && $champs != null){
                // construction de la requête
                $requete = "update $table set ";
                foreach ($champs as $key => $value){
                    $requete .= "$key=:$key,";
                }
                // (enlève la dernière virgule)
                $requete = substr($requete, 0, strlen($requete)-1);
                $champs["id"] = $id;
                $requete .= " where id=:id;";
                return $this->conn->execute($requete, $champs);
            }else{
                return null;
            }
        }
    }
    
    // code ajouter
    /*
     * récupération de toute les commandes d'un livre
     * @param string $id id du livre
     * @return lignes de la requete
     */
    public function selectCommandesLivre($id)
    {
        $param = array(
            "id" => $id
        );
        
        $req = "SELECT d.id, d.idLivreDvd, c.dateCommande, c.montant, d.nbExemplaire, d.suivi_id AS SuiviId, s.etape AS EtapeSuivi ";
        $req .= "FROM commande c ";
        $req .= "JOIN commandedocument d ON c.id = d.id ";
        $req .= "JOIN suivi s ON s.id = d.suivi_id ";
        $req .= "WHERE d.idLivreDvd = :id ";
        $req .= "ORDER BY c.dateCommande DESC";
        return $this->conn->query($req, $param);
    }
    
    /*
     * ajout d'une ligne dans les tables commande et commandeDocument
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOneCommande($champs)
    {
        if ($this->conn != null && $champs != null) {
            try {
                // Générer un ID unique pour la commande
                $commandeId = $champs['Id'];

                // 1. Insérer dans la table `commande`
                if (array_key_exists('DateCommande', $champs) && array_key_exists('Montant', $champs)) {
                    $commandeQuery = "INSERT INTO commande (id, dateCommande, montant) VALUES (:Id, :DateCommande, :Montant)";
                    $commandeParams = array(
                        "Id" => $commandeId,
                        "DateCommande" => $champs['DateCommande'],
                        "Montant" => $champs['Montant']
                    );
                    $resultCommande = $this->conn->execute($commandeQuery, $commandeParams);
                    if (!$resultCommande) {
                        return false;
                    }
                }

                // 2. Insérer dans la table `commandeDocument`
                if (
                    array_key_exists('IdLivreDvd', $champs) &&
                    array_key_exists('NbExemplaire', $champs) &&
                    array_key_exists('SuiviId', $champs)
                ) {
                    $commandeDocumentQuery = "INSERT INTO commandeDocument (id, idLivreDvd, nbExemplaire, suivi_id) VALUES (:Id, :IdLivreDvd, :NbExemplaire, :SuiviId)";
                    $commandeDocumentParams = array(
                        "Id" => $commandeId,
                        "IdLivreDvd" => $champs['IdLivreDvd'],
                        "NbExemplaire" => $champs['NbExemplaire'],
                        "SuiviId" => $champs['SuiviId']
                    );
                    $resultCommandeDocument = $this->conn->execute($commandeDocumentQuery, $commandeDocumentParams);
                    if (!$resultCommandeDocument) {
                        return false;
                    }
                }
                return $resultCommandeDocument + $resultCommande;
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Modifie le suivi d'une commande
     * @param string $id id de la commande
     * @param array $champs
     * @return bool
     * @throws Exception
     */
    public function updateCommande($id, $champs)
    {
        if($this->conn != null && $champs != null){
            $champsUpdate["idLivreDvd"] = $champs["IdLivreDvd"];
            $champsUpdate["nbExemplaire"] = $champs["NbExemplaire"];
            $champsUpdate["suivi_id"] = $champs["SuiviId"];
            
            // construction de la requête
            $requete = "update commandedocument set ";
            foreach ($champsUpdate as $key => $value){
                $requete .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $champsUpdate["id"] = $id;
            $requete .= " where id=:id;";
            return $this->conn->execute($requete, $champsUpdate);
        }
        return false;
    }
    
    /**
    * Suppression d'une commande
    * @param string $id id de la commande
    * @return bool true si la suppression a pu se faire (retour != null)
    */
    public function deleteCommande($id)
    {
        if ($this->isCommandeLivree($id)) {
            return false;
        }
        $param = array("id" => $id);
        
        // Supprimer dans la table commandedocument
        $req = "DELETE FROM commandedocument WHERE id = :id";
        $result1 = $this->conn->execute($req, $param);
        
        // Supprimer dans la table commande
        $req = "DELETE FROM commande WHERE id = :id";
        $result2 = $this->conn->execute($req, $param);
        
        return $result1 && $result2;
    }
   
   /**
    * Vérifie si une commande est livrée
    * @param string $id id de la commande
    * @return bool true si la commande est livrée, false sinon
    */
   private function isCommandeLivree($id)
   {
       $param = array("id" => $id);
       $req = "SELECT COUNT(*) FROM commandedocument WHERE id = :id AND suivi_id = 4";
       $result = $this->conn->query($req, $param);
       return $result[0]["COUNT(*)"] > 0;
   }
   
}