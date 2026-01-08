<?php
// Backend/config/Constraints.php

class Constraints {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ==================== VÉRIFICATIONS D'EXISTENCE ====================

    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists($email, $table, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM $table WHERE email = ?";
        $params = [$email];

        if ($exclude_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur emailExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un partenaire existe déjà (nom + type)
     */
    public function partenaireExists($nom, $type, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM Partenaire WHERE nom = ? AND type = ?";
        $params = [$nom, $type];

        if ($exclude_id !== null) {
            $sql .= " AND id_partenaire != ?";
            $params[] = $exclude_id;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur partenaireExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un bénévole a déjà cotisé pour une année
     */
    public function cotisationExists($id_benevole, $annee, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM cotisation WHERE id_benevole = ? AND annee = ?";
        $params = [$id_benevole, $annee];

        if ($exclude_id !== null) {
            $sql .= " AND id_cotisation != ?";
            $params[] = $exclude_id;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur cotisationExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un bénévole participe déjà à un événement
     */
    public function participationExists($id_benevole, $id_evenement, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM participation WHERE id_benevole = ? AND id_evenement = ?";
        $params = [$id_benevole, $id_evenement];

        if ($exclude_id !== null) {
            $sql .= " AND id_participation != ?";
            $params[] = $exclude_id;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erreur participationExists: " . $e->getMessage());
            return false;
        }
    }

    // ==================== VÉRIFICATIONS DE VALIDITÉ ====================

    /**
     * Vérifier la date de naissance (16-100 ans)
     */
    public function isValidBirthDate($date) {
        if (empty($date)) return true;

        try {
            $birthDate = new DateTime($date);
            $today = new DateTime();
            $age = $birthDate->diff($today)->y;

            return $age >= 16 && $age <= 100;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Vérifier si une date n'est pas dans le futur
     */
    public function isNotFutureDate($date) {
        if (empty($date)) return true;

        try {
            $inputDate = new DateTime($date);
            $today = new DateTime();
            return $inputDate <= $today;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Vérifier les dates d'événement (date_fin >= date_debut)
     */
    public function isValidEventDates($date_debut, $date_fin) {
        if (empty($date_debut) || empty($date_fin)) return false;

        try {
            $debut = new DateTime($date_debut);
            $fin = new DateTime($date_fin);
            return $fin >= $debut;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Vérifier l'année de cotisation (2020-année en cours +1)
     */
    public function isValidCotisationYear($annee) {
        if (empty($annee)) return false;

        $currentYear = (int)date('Y');
        return $annee >= 2020 && $annee <= ($currentYear + 1);
    }

    /**
     * Vérifier un montant (positif, optionnellement > 0)
     */
    public function isValidAmount($montant, $allowZero = false) {
        if ($montant === null || $montant === '') return true;

        if (!is_numeric($montant)) return false;

        $montant = (float)$montant;

        if ($allowZero) {
            return $montant >= 0;
        } else {
            return $montant > 0;
        }
    }

    /**
     * Vérifier un email
     */
    public function isValidEmail($email) {
        if (empty($email)) return true;
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Vérifier un téléphone (format français simplifié)
     */
    public function isValidPhone($phone) {
        if (empty($phone)) return true;

        // Supprimer les espaces, tirets, points
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format français : 10 chiffres commençant par 0
        return preg_match('/^0[1-9][0-9]{8}$/', $phone);
    }

    /**
     * Vérifier si un nom/prenom est valide (lettres, espaces, apostrophes, tirets)
     */
    public function isValidName($name) {
        if (empty($name)) return false;
        return preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $name);
    }

    // ==================== VÉRIFICATIONS D'UNICITÉ ====================

    /**
     * Vérifier l'unicité d'un partenaire (nom + type)
     */
    public function isUniquePartenaire($nom, $type, $exclude_id = null) {
        return !$this->partenaireExists($nom, $type, $exclude_id);
    }

    /**
     * Vérifier l'unicité d'une cotisation (bénévole + année)
     */
    public function isUniqueCotisation($id_benevole, $annee, $exclude_id = null) {
        return !$this->cotisationExists($id_benevole, $annee, $exclude_id);
    }

    /**
     * Vérifier l'unicité d'une participation (bénévole + événement)
     */
    public function isUniqueParticipation($id_benevole, $id_evenement, $exclude_id = null) {
        return !$this->participationExists($id_benevole, $id_evenement, $exclude_id);
    }

    /**
     * Vérifier l'unicité d'un email
     */
    public function isUniqueEmail($email, $table, $exclude_id = null) {
        return !$this->emailExists($email, $table, $exclude_id);
    }

    // ==================== VALIDATION DE FORMULAIRES ====================

    /**
     * Valider les données d'un formulaire avec règles personnalisées
     */
    public function validateFormData($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule => $params) {
                $error = $this->applyRule($field, $value, $rule, $params, $data);
                if ($error) {
                    $errors[] = $error;
                    break; // Une erreur par champ suffit
                }
            }
        }

        return $errors;
    }

    /**
     * Appliquer une règle de validation
     */
    private function applyRule($field, $value, $rule, $params, $allData = []) {
        $fieldLabel = $this->getFieldLabel($field);

        switch ($rule) {
            case 'required':
                if ($params && (empty($value) && $value !== '0')) {
                    return "Le champ '$fieldLabel' est obligatoire";
                }
                break;

            case 'email':
                if (!empty($value) && !$this->isValidEmail($value)) {
                    return "L'email '$fieldLabel' n'est pas valide";
                }
                break;

            case 'phone':
                if (!empty($value) && !$this->isValidPhone($value)) {
                    return "Le téléphone '$fieldLabel' n'est pas valide";
                }
                break;

            case 'name':
                if (!empty($value) && !$this->isValidName($value)) {
                    return "Le champ '$fieldLabel' contient des caractères invalides";
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    return "La date '$fieldLabel' n'est pas valide";
                }
                break;

            case 'not_future':
                if (!empty($value) && !$this->isNotFutureDate($value)) {
                    return "La date '$fieldLabel' ne peut pas être dans le futur";
                }
                break;

            case 'birth_date':
                if (!empty($value) && !$this->isValidBirthDate($value)) {
                    return "La date de naissance doit correspondre à un âge entre 16 et 100 ans";
                }
                break;

            case 'event_dates':
                if (!empty($value) && !empty($allData['date_fin'])) {
                    if (!$this->isValidEventDates($value, $allData['date_fin'])) {
                        return "La date de fin doit être postérieure ou égale à la date de début";
                    }
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $params) {
                    return "Le champ '$fieldLabel' doit faire au moins $params caractères";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $params) {
                    return "Le champ '$fieldLabel' ne doit pas dépasser $params caractères";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "Le champ '$fieldLabel' doit être un nombre";
                }
                break;

            case 'amount':
                $allowZero = $params['allow_zero'] ?? false;
                if (!empty($value) && !$this->isValidAmount($value, $allowZero)) {
                    return $allowZero
                        ? "Le montant '$fieldLabel' doit être positif ou zéro"
                        : "Le montant '$fieldLabel' doit être supérieur à zéro";
                }
                break;

            case 'year':
                if (!empty($value) && !$this->isValidCotisationYear($value)) {
                    $currentYear = date('Y');
                    return "L'année doit être comprise entre 2020 et " . ($currentYear + 1);
                }
                break;

            case 'unique':
                // $params = ['table', 'column', 'exclude_id_field']
                if (!empty($value)) {
                    $exclude_id = isset($params[2]) ? ($allData[$params[2]] ?? null) : null;

                    $sql = "SELECT COUNT(*) FROM {$params[0]} WHERE {$params[1]} = ?";
                    $queryParams = [$value];

                    if ($exclude_id !== null) {
                        $sql .= " AND id != ?";
                        $queryParams[] = $exclude_id;
                    }

                    try {
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute($queryParams);

                        if ($stmt->fetchColumn() > 0) {
                            return "Cette valeur existe déjà pour le champ '$fieldLabel'";
                        }
                    } catch (Exception $e) {
                        return "Erreur lors de la vérification d'unicité";
                    }
                }
                break;

            case 'unique_partenaire':
                if (!empty($value) && !empty($allData['type'])) {
                    $exclude_id = $allData['id'] ?? $allData['id_partenaire'] ?? null;
                    if (!$this->isUniquePartenaire($value, $allData['type'], $exclude_id)) {
                        return "Un partenaire avec ce nom et ce type existe déjà";
                    }
                }
                break;

            case 'unique_cotisation':
                if (!empty($value) && !empty($allData['annee'])) {
                    $exclude_id = $allData['id'] ?? $allData['id_cotisation'] ?? null;
                    if (!$this->isUniqueCotisation($value, $allData['annee'], $exclude_id)) {
                        return "Ce bénévole a déjà cotisé pour cette année";
                    }
                }
                break;
        }

        return null;
    }

    /**
     * Obtenir un libellé lisible pour un champ
     */
    private function getFieldLabel($field) {
        $labels = [
            'nom' => 'Nom',
            'prenom' => 'Prénom',
            'email' => 'Email',
            'telephone' => 'Téléphone',
            'date_naissance' => 'Date de naissance',
            'date_inscription' => "Date d'inscription",
            'montant' => 'Montant',
            'date_paiement' => 'Date de paiement',
            'annee' => 'Année',
            'mode_paiement' => 'Mode de paiement',
            'date_debut' => 'Date de début',
            'date_fin' => 'Date de fin',
            'lieu' => 'Lieu',
            'budget' => 'Budget',
            'nb_participants_prevus' => 'Nombre de participants prévus',
            'contact_email' => 'Email de contact',
            'contact_telephone' => 'Téléphone de contact',
            'date_contribution' => 'Date de contribution',
            'description' => 'Description',
            'details' => 'Détails',
            'type' => 'Type'
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    // ==================== UTILITAIRES ====================

    /**
     * Nettoyer une valeur avant insertion
     */
    public function sanitize($value) {
        if ($value === null) return null;

        if (is_string($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    /**
     * Formater une date pour la base de données
     */
    public function formatDateForDB($date) {
        if (empty($date)) return null;

        try {
            $dateObj = new DateTime($date);
            return $dateObj->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Formater un montant pour la base de données
     */
    public function formatAmountForDB($amount) {
        if ($amount === null || $amount === '') return null;

        $amount = str_replace(',', '.', $amount);
        $amount = preg_replace('/[^0-9.-]/', '', $amount);

        return (float)$amount;
    }

    /**
     * Vérifier si un bénévole peut être supprimé
     */
    public function canDeleteBenevole($id_benevole) {
        try {
            // Vérifier s'il a des cotisations
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cotisation WHERE id_benevole = ?");
            $stmt->execute([$id_benevole]);
            $hasCotisations = $stmt->fetchColumn() > 0;

            // Vérifier s'il a des participations
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM participation WHERE id_benevole = ?");
            $stmt->execute([$id_benevole]);
            $hasParticipations = $stmt->fetchColumn() > 0;

            return !$hasCotisations && !$hasParticipations;
        } catch (Exception $e) {
            error_log("Erreur canDeleteBenevole: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un événement peut être supprimé
     */
    public function canDeleteEvenement($id_evenement) {
        try {
            // Vérifier s'il a des participations
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM participation WHERE id_evenement = ?");
            $stmt->execute([$id_evenement]);
            $hasParticipations = $stmt->fetchColumn() > 0;

            // Vérifier s'il a des documents
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM document_evenement WHERE id_evenement = ?");
            $stmt->execute([$id_evenement]);
            $hasDocuments = $stmt->fetchColumn() > 0;

            return !$hasParticipations && !$hasDocuments;
        } catch (Exception $e) {
            error_log("Erreur canDeleteEvenement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un partenaire peut être supprimé définitivement
     */
    public function canDeletePartenaire($id_partenaire) {
        try {
            // Pour l'instant, toujours possible de supprimer un partenaire
            // (pas de contraintes de clé étrangère avec d'autres tables)
            return true;
        } catch (Exception $e) {
            error_log("Erreur canDeletePartenaire: " . $e->getMessage());
            return false;
        }
    }
}
?>