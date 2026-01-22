# 🔧 GUIDE DE CORRECTION PRATIQUE - PROJET FAGE

## Corrections immédiates à appliquer

### 1️⃣ CORRIGER LES IDENTIFIANTS HARDCODÉS (CRITIQUE)

**Fichier:** `Backend/views/auth/login.php`

**Avant:**

```php
$is_valid = password_verify($password, $user['password']) ||
            ($password === 'admin123' && $email === 'admin@fage.fr');
```

**Après:**

```php
$is_valid = password_verify($password, $user['password']);
```

**Supprimer également lignes 205-207 et 229-231:**

```php
// ❌ À SUPPRIMER:
<div class="test-credentials">
    <p class="mb-1"><strong>Email :</strong> admin@fage.org</p>
    <p class="mb-0"><strong>Mot de passe :</strong> admin123</p>
</div>

// Et le JavaScript:
if (window.location.search.includes('test')) {
    document.querySelector('input[name="email"]').value = 'admin@fage.org';
    document.querySelector('input[name="password"]').value = 'admin123';
}
```

---

### 2️⃣ CRÉER LE FICHIER `.env`

**Créer** `SAE_FAGE_FINAL/.env`:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=votre_mot_de_passe_securise
DB_NAME=fage_database

# Application
APP_ENV=development
APP_DEBUG=false
APP_URL=http://localhost

# Session
SESSION_TIMEOUT=3600
```

**Ajouter à `.gitignore`:**

```
.env
.env.local
.env.*.local
```

---

### 3️⃣ CORRIGER LE FORMULAIRE FILTRE (Actualité_Ressources.html)

**Ligne 394 - Avant:**

```html
<form class="form-filtre" action="Actualité_Ressource.html" method="get"></form>
```

**Après:**

```html
<form
  class="form-filtre"
  action="Actualité_Ressources.html"
  method="get"
></form>
```

---

### 4️⃣ CORRIGER LE LIEN D'INSCRIPTION (fichier.js)

**Ligne 47 - Avant:**

```javascript
window.location.href = "mettre lien page inscription";
```

**Après:**

```javascript
window.location.href = "Inscription_Asso.html";
```

---

### 5️⃣ CORRIGER LA REDIRECTION SESSION (session_check.php)

**Ligne 33 - Avant:**

```php
header('Location: /auth/login.php');
```

**Après:**

```php
header('Location: ' . dirname($_SERVER['REQUEST_URI']) . '/views/auth/login.php');
// OU plus simple si appelé depuis Backend:
header('Location: views/auth/login.php');
```

---

### 6️⃣ SUPPRIMER LA VARIABLE MORTE

**Fichier:** `Backend/views/auth/login.php`  
**Ligne 25 - À SUPPRIMER:**

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

Cette ligne crée un hash non utilisé ensuite.

---

### 7️⃣ CORRIGER LA REDONDANCE CSS

**Fichier:** `assets/css/stylesheet.css`

**Supprimer la deuxième définition (lignes 79-85):**

```css
/* ❌ À SUPPRIMER */
.text-decoration-none,
.tint-text {
  color: #183146 !important;
  font-weight: 500;
  transition: color 0.3s ease;
}
```

Garder seulement la première (lignes 44-48).

---

### 8️⃣ CORRIGER LA COULEUR SIMILAIRE

**Fichier:** `assets/css/stylesheet.css`  
**Ligne 258 - Avant:**

```css
#afficher-banniere {
  background-color: #173045; /* Trop similaire à #183146 */
}
```

**Après:**

```css
#afficher-banniere {
  background-color: #183146; /* Cohérent avec le thème */
}
```

---

### 9️⃣ CORRIGER LES INCLUDES INCONSISTANTS

**Fichier:** `Backend/views/auth/login.php`

**Ligne 32 - Avant:**

```php
require_once '../../config/Database.php';
```

**Après:**

```php
require_once __DIR__ . '/../../config/Database.php';
```

Cela rend le chemin plus robuste et cohérent.

---

### 🔟 CORRIGER LA SIDEBAR

**Fichier:** `Backend/views/partials/sidebar.php`

**Avant:**

```php
<a href="adherents/list.php" class="nav-link">
<a href="missions/list.php" class="nav-link">
<a href="partenaires/list.php" class="nav-link">
```

**Après (selon la structure réelle):**

```php
<a href="adherents/listeAdherent.php" class="nav-link">
    <i class="bi bi-people"></i>
    <span>Bénévoles</span>
</a>

<a href="missions/addMissions.php" class="nav-link">
    <i class="bi bi-calendar-event"></i>
    <span>Événements</span>
</a>

<a href="partenaires/editPartenaire.php" class="nav-link">
    <i class="bi bi-handshake"></i>
    <span>Partenaires</span>
</a>
```

**Logout - Avant:**

```php
<a href="../../Backend/controllers/AuthController.php?action=logout" class="nav-link text-danger">
```

**Après:**

```php
<a href="../auth/logout.php" class="nav-link text-danger">
    <i class="bi bi-box-arrow-right"></i>
    <span>Déconnexion</span>
</a>
```

---

### 1️⃣1️⃣ IMPLÉMENTER OU SUPPRIMER normal.html

**Option A: Supprimer le lien (si page non prévue)**

Modifier tous les fichiers HTML qui pointent vers `../Backend/normal.html` et supprimer ce lien.

**Option B: Créer la page (si prévue)**

Créer `Backend/normal.html` ou `Backend/views/member/dashboard.php`:

```html
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Espace Membre - FAGE</title>
    <link rel="stylesheet" href="../../assets/css/stylesheet.css" />
  </head>
  <body>
    <div class="container mt-5">
      <h1>Espace Membre</h1>
      <p>Bienvenue dans votre espace personnel!</p>
      <!-- Contenu à ajouter -->
    </div>
  </body>
</html>
```

---

### 1️⃣2️⃣ AJOUTER VALIDATION AUX FORMULAIRES

**Fichier:** `HTML/Accueil.html` (formulaire newsletter)

**Avant:**

```html
<form class="newsletter-form" id="newsletter-form">
  <!-- Pas d'action -->
</form>
```

**Après:**

```html
<form
  class="newsletter-form"
  id="newsletter-form"
  method="POST"
  action="Backend/controllers/NewsletterController.php"
>
  <input type="email" name="email" placeholder="Votre email" required />
  <button type="submit" class="btn btn-primary">S'inscrire</button>
</form>
```

Et créer `Backend/controllers/NewsletterController.php`:

```php
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email invalide";
        header('Location: ../../HTML/Accueil.html');
        exit;
    }

    // Ajouter à la base de données
    require_once __DIR__ . '/../config/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, NOW())");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Email enregistré avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de l'enregistrement";
    }

    header('Location: ../../HTML/Accueil.html');
    exit;
}
```

---

### 1️⃣3️⃣ AJOUTER ALT TEXT AUX IMAGES

Exemple: `HTML/Accueil.html`

**Avant:**

```html
<img src="../assets/img/logo_navbar.png" height="40" width="auto" />
```

**Après:**

```html
<img
  src="../assets/img/logo_navbar.png"
  height="40"
  width="auto"
  alt="Logo FAGE"
/>
```

**Parcourir tous les `<img>` et ajouter un alt text descriptif.**

---

### 1️⃣4️⃣ AJOUTER ARIA-LABELS

**Fichier:** `HTML/Accueil.html`

**Avant:**

```html
<button class="navbar-toggler" type="button" data-bs-toggle="collapse"></button>
```

**Après:**

```html
<button
  class="navbar-toggler"
  type="button"
  data-bs-toggle="collapse"
  aria-label="Afficher le menu de navigation"
></button>
```

---

### 1️⃣5️⃣ CORRIGER Constraints.php (PDO vers mysqli)

**Fichier:** `Backend/config/Constraints.php`

**Avant:**

```php
public function emailExists($email, $table, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM $table WHERE email = ?";
    $params = [$email];

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);  // ❌ PDO method
        return $stmt->fetchColumn() > 0;  // ❌ PDO method
    } catch (Exception $e) {
        error_log("Erreur emailExists: " . $e->getMessage());
        return false;
    }
}
```

**Après:**

```php
public function emailExists($email, $table, $exclude_id = null) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE email = ?";

    if ($exclude_id !== null) {
        $sql .= " AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $email, $exclude_id);
    } else {
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
    }

    try {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return ($row['count'] ?? 0) > 0;
    } catch (Exception $e) {
        error_log("Erreur emailExists: " . $e->getMessage());
        return false;
    }
}
```

---

## 📋 CHECKLIST DE CORRECTION

- [ ] Supprimer identifiants hardcodés
- [ ] Créer `.env`
- [ ] Corriger formulaire Actualité_Ressources.html
- [ ] Corriger lien inscription fichier.js
- [ ] Corriger session_check.php
- [ ] Supprimer variable $hashed_password
- [ ] Supprimer CSS dupliqué
- [ ] Corriger couleur #173045
- [ ] Corriger includes inconsistants
- [ ] Corriger sidebar navigation
- [ ] Corriger ou supprimer normal.html
- [ ] Ajouter validation formulaires
- [ ] Ajouter alt text images
- [ ] Ajouter aria-labels
- [ ] Corriger Constraints.php
- [ ] Tester tous les liens
- [ ] Tester tous les formulaires
- [ ] Tester la connexion BD
- [ ] Vérifier les chemins d'includes

---

## 🧪 TESTS À EFFECTUER

### 1. Tests de Navigation

- [ ] Tous les liens HTML fonctionnent
- [ ] Tous les boutons redirigent correctement
- [ ] La sidebar fonctionne
- [ ] La déconnexion fonctionne

### 2. Tests de Formulaires

- [ ] Newsletter subscribe fonctionne
- [ ] Filtre Actualité fonctionne
- [ ] Login fonctionne sans identifiants hardcodés
- [ ] Validation côté serveur fonctionne

### 3. Tests de Base de Données

- [ ] Connexion BD établie
- [ ] Requêtes préparées fonctionnent
- [ ] Les données sont enregistrées

### 4. Tests de Sécurité

- [ ] Pas de SQL injection possible
- [ ] Pas d'identifiants en dur
- [ ] Sessions sécurisées
- [ ] Mots de passe hashés

---

## ⏱️ TEMPS ESTIMÉ

| Tâche                    | Durée    |
| ------------------------ | -------- |
| Supprimer identifiants   | 5 min    |
| Créer .env               | 10 min   |
| Corriger formulaires     | 15 min   |
| Corriger liens           | 20 min   |
| Ajouter alt text         | 15 min   |
| Ajouter aria-labels      | 10 min   |
| Corriger Constraints.php | 30 min   |
| Tests complets           | 30 min   |
| **TOTAL**                | **2h15** |

---

## 📞 SUPPORT

Pour chaque correction:

1. Faire une sauvegarde
2. Appliquer la correction
3. Tester localement
4. Committer les changements (git commit)

Bonne correction! 🚀
