<?php
# ⭐ PRIORITAIRE - Membres bureau (accès admin)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Backend/models/Bureau.php
class MembreBureau
{
    private $conn;
    private $table_name = "BENEVOLE";

    public $id_benevole;
    public $nom;
    public $prenom;
    public $email;
    public $telephone;
    public $date_naissance;
    public $date_inscription;
    public $statut;
    public $role_bureau;
    public $password_hash;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Authentification
    public function authenticate($email, $password)
    {
        $query = "SELECT b.*, r.role 
                  FROM " . $this->table_name . " b
                  LEFT JOIN RESPONSABLE r ON b.id_benevole = r.id_benevole
                  WHERE b.email = :email AND b.statut = 'actif'";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Vérifier le mot de passe (à adapter selon ta table)
                // Pour l'instant, on simule une vérification
                if ($password === 'admin123' && $row['role'] !== null) {
                    $this->id_benevole = $row['id_benevole'];
                    $this->nom = $row['nom'];
                    $this->prenom = $row['prenom'];
                    $this->email = $row['email'];
                    $this->role_bureau = $row['role'];
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur authentification : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer tous les membres du bureau
    public function getAllBureauMembers()
    {
        $query = "SELECT b.*, r.role, r.date_debut_mandat, r.date_fin_mandat
                  FROM " . $this->table_name . " b
                  INNER JOIN RESPONSABLE r ON b.id_benevole = r.id_benevole
                  WHERE r.statut = 'actif'
                  ORDER BY b.nom, b.prenom";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération bureau : " . $e->getMessage());
            return [];
        }
    }
}
