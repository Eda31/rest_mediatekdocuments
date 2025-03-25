<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD
{
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */
    protected function traitementSelect(string $table, ?array $champs) : ?array
    {
        switch ($table) {
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "commande" :
                return $this->selectCommandes($champs);
            case "abonnement" :
                return $this->selectAbonnements($champs);
            case "abonnements" :
                return $this->selectAllAbonnements();
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */
    protected function traitementInsert(string $table, ?array $champs) : ?int
    {
        switch ($table) {
            case "livre" :
                return $this->insertOneLivre($champs);
            case "commande" :
                return $this->insertOneCommande($champs);
            case "abonnement" :
                return $this->insertOneAbonnement($champs);
            default:
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int
    {
        switch ($table) {
            case "livre" :
                return $this->updateOneLivre($id, $champs);
            case "commande" :
                return $this->updateOneCommande($id, $champs);
            case "abonnement" :
                return $this->updateOneAbonnement($id, $champs);
            default:
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }
    }
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */
    protected function traitementDelete(string $table, ?array $champs) : ?int
    {
        switch ($table) {
            case "livre" :
                return $this->deleteOneLivre($champs);
            case "commande" :
                return $this->deleteOneCommande($champs);
            case "abonnement" :
                return $this->deleteOneAbonnement($champs);
            default:
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);
        }
    }
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array
    {
        if (empty($champs)) {
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);
        } else {
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value) {
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);
            return $this->conn->queryBDD($requete, $champs);
        }
    }

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value) {
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value) {
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        if (is_null($id)) {
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $champs["id"] = $id;
        $requete .= " where id=:id;";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);
        return $this->conn->updateBDD($requete, $champs);
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array
    {
        $requete = "select * from $table order by libelle;";
        return $this->conn->queryBDD($requete);
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array
    {
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array
    {
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array
    {
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array
    {
        if (empty($champs)) {
            return null;
        }
        if (!array_key_exists('id', $champs)) {
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * demande d'ajout (insert) d'un livre
     * @param array|null $champs
     * @return int|null
     */
    private function insertOneLivre(?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        $paramsDoc =[
            'id' => $champs['Id'],
            'titre' => $champs['Titre'],
            'image' => $champs['Image'],
            'idGenre' => $champs['IdGenre'],
            'idPublic' => $champs['IdPublic'],
            'idRayon' => $champs['IdRayon']
        ];
        $paramsLivreDvd = ['id' => $champs['Id']];
        $paramsLivre = [
            'id' => $champs['Id'],
            'ISBN' => $champs['Isbn'],
            'auteur' => $champs['Auteur'],
            'collection' => $champs['Collection']
        ];
        $insertDoc = $this->insertOneTupleOneTable("document", $paramsDoc);
        $insertLivreDvd = $this->insertOneTupleOneTable("livres_dvd", $paramsLivreDvd);
        $insertLivre = $this->insertOneTupleOneTable("livre", $paramsLivre);
        return $insertDoc + $insertLivreDvd + $insertLivre;
    }
    
    /**
     * supprimer un livre
     * @param array|null $champs
     * @return int|null
     */
    private function deleteOneLivre(?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        $deleteLivre = $this->deleteTuplesOneTable("livre", $champs);
        $deleteLivreDvd = $this->deleteTuplesOneTable("livres_dvd", $champs);
        $deleteDoc = $this->deleteTuplesOneTable("document", $champs);
        return $deleteLivre + $deleteLivreDvd + $deleteDoc;
    }
    
    private function updateOneLivre(?string $id, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        if (is_null($id)) {
            return null;
        }
        // construction de la requête
        $requete = "update document set ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $champs["id"] = $id;
        $requete .= " where id=:id;";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    /*
     * récupération de toute les commandes d'un livre ou dvd
     * @param string $id id du livre ou dvd
     * @return lignes de la requete
     */
    public function selectCommandes(?array $champs) : ?array
    {
        if (empty($champs)) {
            return null;
        }
        if (!array_key_exists('id', $champs)) {
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "SELECT cd.id, cd.idLivreDvd, c.dateCommande, c.montant, cd.nbExemplaire, cd.idsuivi AS SuiviId, s.etape AS EtapeSuivi ";
        $requete .= "FROM commande c JOIN commandedocument cd ON c.id = cd.id ";
        $requete .= "JOIN suivi s ON s.id = cd.idsuivi ";
        $requete .= "WHERE cd.idLivreDvd = :id ";
        $requete .= "ORDER BY c.dateCommande DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * demande d'ajout (insert) d'une commande
     * @param array|null $champs
     * @return int|null
     */
    private function insertOneCommande(?array $champs) : ?int
    {
        if (empty($champs)) {
            return 'null';
        }
        $paramsCom = [
            'id' => $champs['Id'],
            'dateCommande' => $champs['DateCommande'],
            'montant' => $champs['Montant']
        ];
        
        $paramsCommandeDoc = [
            'id' => $champs['Id'],
            'nbExemplaire' => $champs['NbExemplaire'],
            'idLivreDvd' => $champs['IdLivreDvd'],
            'idsuivi' => $champs['SuiviId']
        ];
        $insertCom = $this->insertOneTupleOneTable("commande", $paramsCom);
        $insertCommandeDoc = $this->insertOneTupleOneTable("commandedocument", $paramsCommandeDoc);
        return $insertCommandeDoc && $insertCom;
    }
    
    /**
     * Modifie le suivi d'une commande
     * @param string $id id de la commande
     * @param array $champs
     * @return bool
     * @throws Exception
     */
    public function updateOneCommande($id, $champs)
    {
        if ($this->conn != null && $champs != null) {
            $id = $champs['Id'];
            $paramsCommandeDoc = [
                'id' => $champs['Id'],
                'nbExemplaire' => $champs['NbExemplaire'],
                'idLivreDvd' => $champs['IdLivreDvd'],
                'idsuivi' => $champs['SuiviId']
            ];
            $updateCommandeDoc = $this->updateOneTupleOneTable("commandedocument", $id, $paramsCommandeDoc);
            return $updateCommandeDoc;
        }
        return false;
    }
    
    /**
     * supprime une commande
     * @param type $champs
     * @return bool
     */
    public function deleteOneCommande($champs)
    {
        $id = $champs['Id'];
        if ($this->isCommandeLivree($id)) {
            return false;
        }
        $param = ['id' => $champs['Id']];
        // Supprimer dans la table commandedocument
        $deleteCommandeDoc = $this->deleteTuplesOneTable("commandedocument", $param);
        // Supprimer dans la table commande
        $deleteCom = $this->deleteTuplesOneTable("commande", $param);
        return $deleteCommandeDoc && $deleteCom;
    }
   
    /**
    * Vérifie si une commande est livrée
    * @param string $id id de la commande
    * @return bool true si la commande est livrée, false sinon
    */
   private function isCommandeLivree($id)
   {
       $param = array("id" => $id);
       $req = "SELECT COUNT(*) FROM commandedocument WHERE id = :id AND idsuivi = 4";
       $result = $this->conn->queryBDD($req, $param);
       return $result[0]["COUNT(*)"] > 0;
   }
   
   /*
     * récupération de toute les commandes abonnement
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAbonnements(?array $champs) : ?array
    {
        if (empty($champs)) {
            return null;
        }
        if (!array_key_exists('id', $champs)) {
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
      
        $requete = "SELECT c.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
        $requete .= "FROM `commande` AS c ";
        $requete .= "JOIN abonnement AS a ON c.id = a.id ";
        $requete .= "WHERE a.idRevue = :id ";
        $requete .= "ORDER BY c.dateCommande DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * demande d'ajout (insert) d'un abonnement
     * @param array|null $champs
     * @return int|null
     */
    private function insertOneAbonnement(?array $champs) : ?int
    {
        if (empty($champs)) {
            return 'null';
        }
        $paramsCom = [
            'id' => $champs['Id'],
            'dateCommande' => $champs['DateCommande'],
            'montant' => $champs['Montant']
        ];
        
        $paramsAbo = [
            'id' => $champs['Id'],
            'dateFinAbonnement' => $champs['DateFinAbonnement'],
            'idRevue' => $champs['IdRevue']
        ];
        $insertCom = $this->insertOneTupleOneTable("commande", $paramsCom);
        $insertCommandeDoc = $this->insertOneTupleOneTable("abonnement", $paramsAbo);
        return $insertCommandeDoc && $insertCom;
    }
    
    /**
     * Modifie un abonnement
     * @param string $id id de l'abonnement
     * @param array $champs
     * @return bool
     * @throws Exception
     */
    public function updateOneAbonnement($id, $champs)
    {
        if ($this->conn != null && $champs != null) {
            $id = $champs['Id'];
            $paramsAbo = [
                'id' => $champs['Id'],
                'dateFinAbonnement' => $champs['DateFinAbonnement'],
                'idRevue' => $champs['IdRevue']
            ];
            $paramsCom = [
                'id' => $champs['Id'],
                'dateCommande' => $champs['DateCommande'],
                'montant' => $champs['Montant']
            ];
            $updateCom = $this->updateOneTupleOneTable("commande", $id, $paramsCom);
            $updateAbo = $this->updateOneTupleOneTable("abonnement", $id, $paramsAbo);
            return $updateAbo && $updateCom;
        }
        return false;
    }

    /**
     * supprime un abonnement
     * @param type $champs
     * @return bool
     */
    public function deleteOneAbonnement($champs)
    {
        $param = ['id' => $champs['Id']];
        // Supprimer dans la table commandedocument
        $deleteAbo = $this->deleteTuplesOneTable("abonnement", $param);
        // Supprimer dans la table commande
        $deleteCom = $this->deleteTuplesOneTable("commande", $param);
        return $deleteAbo && $deleteCom;
    }
    
    /**
    * Récupère tous les abonnements
    * @return array|null Liste des abonnements ou null si erreur
    */
   public function selectAllAbonnements(): ?array
    {
        $requete = "
            SELECT a.id, a.dateFinAbonnement, a.idRevue, d.titre AS titreRevue
            FROM abonnement a
            JOIN revue r ON a.idRevue = r.id
            JOIN document d ON r.id = d.id
            WHERE DATEDIFF(a.dateFinAbonnement, CURDATE()) <= 30
            ORDER BY a.dateFinAbonnement ASC;
        ";
        return $this->conn->queryBDD($requete);
    }

}
