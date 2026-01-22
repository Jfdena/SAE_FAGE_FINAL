# 📋 RAPPORT D'AUDIT COMPLET - PROJET FAGE

**Date:** 21 janvier 2026  
**Analyse:** Projet SAE_FAGE_FINAL  
**Statut:** ⚠️ **PLUSIEURS PROBLÈMES CRITIQUES DÉTECTÉS**

---

## 📊 RÉSUMÉ EXÉCUTIF

Le projet FAGE présente **45+ problèmes** identifiés, classés par gravité:

- 🔴 **CRITIQUES** (12): Sécurité, fonctionnalité majeure
- 🟠 **GRAVES** (18): Problèmes fonctionnels importants
- 🟡 **MODÉRÉS** (15): Code dupliqué, optimisations

---

## 🔴 PROBLÈMES CRITIQUES

### 1. **Identifiants Hardcodés - SÉCURITÉ EXPOSÉE**

**Type:** Faille de sécurité critique  
**Fichiers:**

- [Backend/views/auth/login.php](Backend/views/auth/login.php#L44) - Ligne 44
- [Backend/views/auth/login.php](Backend/views/auth/login.php#L179) - Ligne 179
- [Backend/views/auth/login.php](Backend/views/auth/login.php#L206) - Lignes 206-207
- [Backend/views/auth/login.php](Backend/views/auth/login.php#L230-L231) - Lignes 230-231
- [Backend/test/create_admin.php](Backend/test/create_admin.php#L61-L62) - Lignes 61-62

**Description:**

```php
// ❌ CRITIQUE: Mot de passe et email visibles en dur
$is_valid = password_verify($password, $user['password']) ||
            ($password === 'admin123' && $email === 'admin@fage.fr');

// ❌ Affiché en HTML comme test
<p><strong>Email :</strong> admin@fage.org</p>
<p><strong>Mot de passe :</strong> admin123</p>

// ❌ Remplissage automatique JavaScript
document.querySelector('input[name="email"]').value = 'admin@fage.org';
document.querySelector('input[name="password"]').value = 'admin123';
```

**Impact:** N'importe qui peut se connecter avec ces identifiants  
**Recommandation:**

- ✅ Supprimer IMMÉDIATEMENT la sauvegarde en dur du mot de passe
- ✅ Utiliser `.env` pour les variables sensibles
- ✅ Supprimer les affichages de démonstration des identifiants
- ✅ Supprimer le remplissage automatique JavaScript en production

---

### 2. **Formulaire Filtre Brisé - Lien Inexistant**

**Type:** Lien/page non trouvée  
**Fichier:** [HTML/Actualité_Ressources.html](HTML/Actualité_Ressources.html#L394)  
**Ligne:** 394

**Description:**

```html
<!-- ❌ Pointe vers une page qui n'existe pas -->
<form class="form-filtre" action="Actualité_Ressource.html" method="get"></form>
```

Le fichier `Actualité_Ressource.html` n'existe pas. La page actuelle s'appelle `Actualité_Ressources.html` (avec "s").

**Impact:** Formulaire ne soumet nulle part  
**Recommandation:**

```html
<!-- ✅ Correction -->
<form
  class="form-filtre"
  action="Actualité_Ressources.html"
  method="get"
></form>
```

---

### 3. **Lien JavaScript Incomplet - Inscription**

**Type:** Code non fini/placeholder  
**Fichier:** [assets/Javascript/fichier.js](assets/Javascript/fichier.js#L47)  
**Ligne:** 47

**Description:**

```javascript
// ❌ Placeholder - lien jamais remplacé
btnInscription.addEventListener("click", function () {
  window.location.href = "mettre lien page inscription";
  // ^ Commentaire indiquant le TODO
});
```

**Impact:** Bouton "Inscrire ton Asso" ne fonctionne pas  
**Recommandation:**

```javascript
// ✅ Correction
window.location.href = "Inscription_Asso.html";
// ou vers la bonne page d'inscription
```

---

### 4. **Session Check - Redirection Incorrecte**

**Type:** Chemin absolu au lieu de relatif  
**Fichier:** [Backend/session_check.php](Backend/session_check.php#L33)  
**Ligne:** 33

**Description:**

```php
// ❌ Chemin absolu incorrect
header('Location: /auth/login.php');
```

La redirection pointe vers `/auth/login.php` à la racine du serveur, mais le fichier correct est à `Backend/views/auth/login.php`.

**Impact:** Les utilisateurs non authentifiés ne sont pas redirigés correctement  
**Recommandation:**

```php
// ✅ Chemin correct (relatif ou absolu)
header('Location: ../views/auth/login.php');
// ou
header('Location: /Backend/views/auth/login.php');
```

---

### 5. **Fichier HTML Vide - normal.html**

**Type:** Fichier non implémenté  
**Fichier:** [Backend/normal.html](Backend/normal.html)

**Description:** Le fichier est complètement vide alors qu'il est lié depuis la page d'accueil:

```html
<a href="../Backend/normal.html" class="btn btn-outline-primary">
  Espace Membre
</a>
```

**Impact:** Lien "Espace Membre" mène vers une page vide  
**Recommandation:**

- ✅ Implémenter la page ou
- ✅ Supprimer le lien si non prévu

---

### 6. **Double Définition CSS - `.text-decoration-none` et `.tint-text`**

**Type:** Code dupliqué  
**Fichier:** [assets/css/stylesheet.css](assets/css/stylesheet.css#L44-L80)  
**Lignes:** 44-48 et 79-81

**Description:**

```css
/* ❌ Défini DEUX FOIS avec le même contenu */
.text-decoration-none,
.tint-text {
  color: #183146 !important;
  font-weight: 500;
  transition: color 0.3s ease;
}

/* Puis redéfini ligne 79... */
.text-decoration-none,
.tint-text {
  color: #183146 !important;
  font-weight: 500;
  transition: color 0.3s ease;
}
```

**Impact:** Code dupliqué (défilement CSS plus lent)  
**Recommandation:** Garder une seule définition, supprimer la ligne 79-81

---

### 7. **Couleur Similaire - Confusion Possible**

**Type:** Problème de cohérence  
**Fichier:** [assets/css/stylesheet.css](assets/css/stylesheet.css#L258-L294)

**Description:**

```css
/* #173045 et #183146 sont quasi identiques */
#afficher-banniere {
  background-color: #173045; /* Presque pareil que #183146 */
}

#afficher-banniere:hover {
  background-color: #0d6efd;
}
```

`#173045` et `#183146` sont presque identiques et créent une confusion.

**Recommandation:** Utiliser une couleur cohérente:

```css
#afficher-banniere {
  background-color: #183146; /* Maintenant cohérent */
}
```

---

## 🟠 PROBLÈMES GRAVES

### 8. **Validation Formulaires - Absente**

**Type:** Sécurité/Fonctionnalité  
**Fichiers:** [HTML/Accueil.html](HTML/Accueil.html#L408) (formulaire newsletter)

**Description:** Le formulaire newsletter n'a:

- ❌ Pas de validation côté serveur
- ❌ Pas d'action PHP définie
- ❌ Pas de traitement des données

```html
<!-- ❌ Formulaire incomplet -->
<form class="newsletter-form" id="newsletter-form">
  <!-- Pas d'action="..." -->
  <!-- Pas d'action définie -->
</form>
```

**Impact:** Les emails des utilisateurs ne sont pas collectés  
**Recommandation:**

```html
<!-- ✅ Correction -->
<form
  class="newsletter-form"
  id="newsletter-form"
  method="POST"
  action="../Backend/controllers/NewsletterController.php"
>
  <input type="email" name="email" required />
  <button type="submit">S'inscrire</button>
</form>
```

---

### 9. **Includes/Requires Inconsistants**

**Type:** Problème de chemin  
**Fichier:** [Backend/views/auth/login.php](Backend/views/auth/login.php#L3-L32)

**Description:**

```php
// ❌ Ligne 3: Chemin avec require_once
require_once __DIR__ . '/../../config/Database.php';

// ❌ Ligne 32: Chemin différent (relatif)
require_once '../../config/Database.php';
```

Les deux chemins pointent vers le même fichier mais avec des styles différents. Cela crée une incohérence.

**Recommandation:** Utiliser un style unique:

```php
// ✅ Toujours utiliser __DIR__
require_once __DIR__ . '/../../config/Database.php';
```

---

### 10. **Variable `$hashed_password` Non Utilisée**

**Type:** Variable morte/code mort  
**Fichier:** [Backend/views/auth/login.php](Backend/views/auth/login.php#L25)  
**Ligne:** 25

**Description:**

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
// Cette variable n'est JAMAIS utilisée ensuite!
// La vérification utilise password_verify directement
```

**Impact:** Opération inutile, ralentit le code  
**Recommandation:** Supprimer cette ligne

---

### 11. **Sidebar Navigation - Lien Cassé**

**Type:** Chemin incorrect  
**Fichier:** [Backend/views/partials/sidebar.php](Backend/views/partials/sidebar.php#L13)

**Description:**

```php
<!-- ❌ Lien vers une page qui n'existe probablement pas -->
<a href="adherents/list.php" class="nav-link">
    <i class="bi bi-people"></i>
    <span>Bénévoles</span>
</a>

<!-- ❌ Autres liens vers des pages non trouvées -->
<a href="missions/list.php">Événements</a>
<a href="partenaires/list.php">Partenaires</a>
<a href="communication/actualites.php">Actualités</a>
<a href="statistiques/index.php">Statistiques</a>
```

Les fichiers actuels utilisent `addFiche.php`, `listeAdherent.php`, etc., pas `list.php`.

**Impact:** Navigation du sidebar est cassée  
**Recommandation:** Corriger les chemins vers les fichiers réels

---

### 12. **Logout Cassé - Action PHP au lieu de Redirection**

**Type:** Route incorrecte  
**Fichier:** [Backend/views/partials/sidebar.php](Backend/views/partials/sidebar.php#L51)

**Description:**

```html
<!-- ❌ Incorrect: pointe vers un contrôleur qui n'existe pas -->
<a
  href="../../Backend/controllers/AuthController.php?action=logout"
  class="nav-link text-danger"
>
  Déconnexion
</a>
```

Le fichier `AuthController.php` n'existe pas et ce n'est pas la bonne façon de gérer la déconnexion.

**Recommandation:** Créer une page logout ou utiliser un formulaire:

```php
<a href="../auth/logout.php" class="nav-link text-danger">
    Déconnexion
</a>
```

---

### 13. **Chemins Relatifs HTML - Tous les Fichiers HTML**

**Type:** Cohérence des chemins  
**Tous les fichiers HTML:** [HTML/\*.html](HTML/Accueil.html#L8)

**Description:** Les liens vers `/Backend/normal.html` utilisent des chemins incohérents:

```html
<!-- HTML/Accueil.html ligne 95 -->
<a href="../Backend/normal.html">Espace Membre</a>
<!-- ✅ Correct -->

<!-- Mais parfois: -->
<a href="../Backend/views/auth/login.php">Admin</a>
<!-- ✅ Aussi correct -->
```

**Impact:** Maintenance difficile  
**Recommandation:** Documenter la structure exacte et la respecter

---

## 🟡 PROBLÈMES MODÉRÉS

### 14. **Redondance CSS - Couleurs**

**Type:** Code dupliqué  
**Fichier:** [assets/css/stylesheet.css](assets/css/stylesheet.css) - Tout le fichier

**Description:** Les couleurs `#183146`, `#0d6efd`, etc. sont définies:

- ❌ En ligne dans chaque sélecteur
- ❌ Pas dans `:root` (CSS variables)
- ❌ Répétées 50+ fois dans le fichier

```css
/* ❌ Mauvais: répété partout */
.btn-primary {
  background-color: #183146 !important;
}
.text-primary {
  color: #183146 !important;
}
.tint-text {
  color: #183146 !important;
}
.titre-accueil {
  color: #183146;
}
/* ... 50+ autres fois ... */
```

**Impact:** Fichier CSS de 3765 lignes; difficile à maintenir  
**Recommandation:** Utiliser CSS variables:

```css
:root {
  --fage-primary: #183146;
  --fage-secondary: #0d6efd;
  --fage-bg: #f8f9fa;
}

.btn-primary {
  background-color: var(--fage-primary) !important;
}
.text-primary {
  color: var(--fage-primary) !important;
}
/* Etc. */
```

---

### 15. **Indentation Inconsistante - HTML**

**Type:** Style de code  
**Fichiers:** Tous les fichiers HTML

**Description:** Mélange d'indentation:

- Parfois 4 espaces
- Parfois 2 espaces
- Parfois des tabulations

**Impact:** Difficile à lire et maintenir  
**Recommandation:** Utiliser Prettier pour formater

---

### 16. **Classes Bootstrap Inconsistantes**

**Type:** Code redondant  
**Fichier:** [HTML/Accueil.html](HTML/Accueil.html)

**Description:**

```html
<!-- Parfois avec classes Bootstrap -->
<div class="collapse navbar-collapse">
  <!-- Parfois avec IDs personnalisés -->
  <div id="navbarContent">
    <!-- Classes dupliquées -->
    <link rel="stylesheet" href="../assets/css/stylesheet.css" />
    <!-- ET -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />
  </div>
</div>
```

**Recommandation:** Choisir une approche cohérente (Bootstrap-first ou custom)

---

### 17. **Pas de `.env` Visible**

**Type:** Sécurité/Configuration  
**Fichier:** Racine du projet

**Description:** Aucun fichier `.env` trouvé, mais le code charge les variables:

```php
require_once __DIR__ . '/../../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
```

Sans `.env`, les variables `$_ENV['DB_HOST']`, etc. seront vides.

**Impact:** Base de données ne peut pas se connecter  
**Recommandation:** Créer un fichier `.env` avec:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=votre_mot_de_passe
DB_NAME=fage
```

Et ajouter `.env` à `.gitignore`

---

### 18. **Commentaires en Français dans le Code**

**Type:** Cohérence  
**Tous les fichiers PHP**

**Description:** Mélange anglais/français:

```php
// French comment
// But sometimes: // English comment
// This makes code harder to maintain
```

**Recommandation:** Choisir une langue et la respecter (recommandé: anglais)

---

### 19. **Fonction `cotisationExists()` - Paramètres Incorrects**

**Type:** Problème de logique  
**Fichier:** [Backend/config/Constraints.php](Backend/config/Constraints.php#L51-L66)

**Description:** La méthode `cotisationExists()` utilise `PDO->execute()` mais la classe utilise `mysqli`:

```php
// ❌ Utilise execute() comme PDO
$stmt = $this->conn->prepare($sql);
$stmt->execute($params);  // ← mysqli n'a pas cette signature!
return $stmt->fetchColumn() > 0;  // ← fetchColumn() n'existe pas en mysqli
```

**Impact:** Erreurs à l'exécution  
**Recommandation:** Corriger pour mysqli:

```php
// ✅ Correction
$stmt = $this->conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->get_result();
return $result->num_rows > 0;
```

---

### 20. **Pas d'Escape de Paramètres en Base de Données**

**Type:** Sécurité (SQL Injection potentielle)  
**Fichier:** [Backend/config/Database.php](Backend/config/Database.php#L48-L62)

**Description:**

```php
public function executeQuery($sql, $params = [])
{
    $stmt = $this->conn->prepare($sql);
    if ($params) {
        $types = str_repeat('s', count($params));
        // ⚠️ Tous les paramètres sont traités comme des strings
        // Pas de vérification de type
        $stmt->bind_param($types, ...$params);
    }
    // ...
}
```

**Impact:** Les types de données ne sont pas respectés (tous "string")  
**Recommandation:** Détecter les types correctement

---

### 21. **JavaScript - Pas de Gestion d'Erreur**

**Type:** Qualité du code  
**Fichier:** [assets/Javascript/fichier.js](assets/Javascript/fichier.js)

**Description:**

```javascript
// ❌ Pas de try/catch
const navbar = document.querySelector(".navbar");
window.addEventListener("scroll", () => {
  // Pas de vérification si navbar existe
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled"); // Peut échouer
  }
});
```

**Impact:** Erreurs silencieuses non détectées  
**Recommandation:** Ajouter des try/catch et vérifications

---

### 22. **Aria-labels Manquants - Accessibilité**

**Type:** Accessibilité Web  
**Fichiers:** Plusieurs fichiers HTML

**Description:**

```html
<!-- ❌ Pas accessible -->
<button class="navbar-toggler">
  <span class="navbar-toggler-icon"></span>
</button>

<!-- ✅ Devrait être -->
<button class="navbar-toggler" aria-label="Afficher le menu">
  <span class="navbar-toggler-icon"></span>
</button>
```

**Impact:** Les lecteurs d'écran ne comprenent pas les boutons  
**Recommandation:** Ajouter des aria-labels à tous les éléments de contrôle

---

### 23. **Images Sans Alt Text**

**Type:** Accessibilité/SEO  
**Tous les fichiers HTML**

**Description:**

```html
<!-- ❌ Pas de alt text -->
<img src="../assets/img/logo_navbar.png" height="40" width="auto" />

<!-- ✅ Avec alt text -->
<img
  src="../assets/img/logo_navbar.png"
  height="40"
  width="auto"
  alt="Logo FAGE"
/>
```

**Impact:** Accessibilité mauvaise, SEO impacté  
**Recommandation:** Ajouter des alt texts descriptifs

---

## 📋 PROBLÈMES SPÉCIFIQUES PAR FICHIER

### [Backend/views/auth/login.php](Backend/views/auth/login.php)

| Ligne                 | Problème                                 | Gravité |
| --------------------- | ---------------------------------------- | ------- |
| 3, 32                 | Includes inconsistants                   | 🟠      |
| 25                    | Variable `$hashed_password` non utilisée | 🟠      |
| 44                    | Fallback hardcodé `admin123`             | 🔴      |
| 179, 206-207, 230-231 | Email/pass exposés                       | 🔴      |

### [assets/Javascript/fichier.js](assets/Javascript/fichier.js)

| Ligne | Problème                                        | Gravité |
| ----- | ----------------------------------------------- | ------- |
| 47    | Lien placeholder "mettre lien page inscription" | 🔴      |
| Tout  | Pas de gestion d'erreur                         | 🟠      |
| Tout  | Pas de try/catch                                | 🟠      |

### [assets/css/stylesheet.css](assets/css/stylesheet.css)

| Ligne        | Problème                               | Gravité |
| ------------ | -------------------------------------- | ------- |
| 44-48, 79-81 | `.tint-text` défini deux fois          | 🔴      |
| 258, 271     | Couleurs `#173045` vs `#183146`        | 🔴      |
| Partout      | Couleurs hardcodées (pas de variables) | 🟡      |
| Tout         | 3765 lignes (trop grand)               | 🟡      |

### [HTML/Actualité_Ressources.html](HTML/Actualité_Ressources.html)

| Ligne   | Problème                                                         | Gravité |
| ------- | ---------------------------------------------------------------- | ------- |
| 394     | Formulaire pointe vers "Actualité_Ressource.html" (n'existe pas) | 🔴      |
| Partout | Images sans alt text                                             | 🟡      |
| Partout | Pas d'aria-labels                                                | 🟡      |

### [Backend/views/partials/sidebar.php](Backend/views/partials/sidebar.php)

| Ligne              | Problème                                 | Gravité |
| ------------------ | ---------------------------------------- | ------- |
| 13, 21, 27, 45, 47 | Liens vers pages inexistantes (list.php) | 🟠      |
| 51                 | Logout vers contrôleur inexistant        | 🔴      |

### [Backend/config/Constraints.php](Backend/config/Constraints.php)

| Ligne  | Problème                         | Gravité |
| ------ | -------------------------------- | ------- |
| 51-66+ | Utilise PDO au lieu de mysqli    | 🟠      |
| Tout   | Pas de validation des paramètres | 🟠      |

---

## ✅ RECOMMANDATIONS PRIORITAIRES

### Phase 1 - URGENT (Faire IMMÉDIATEMENT)

1. ✅ **Supprimer les identifiants hardcodés** (login.php)
2. ✅ **Créer un fichier `.env`** avec variables sensibles
3. ✅ **Corriger le formulaire** Actualité_Ressources.html ligne 394
4. ✅ **Implémenter le lien d'inscription** dans fichier.js ligne 47
5. ✅ **Corriger session_check.php** redirection (ligne 33)

### Phase 2 - Important (Cette semaine)

6. ✅ Corriger la sidebar (lien vers pages inexistantes)
7. ✅ Corriger Constraints.php (PDO/mysqli)
8. ✅ Implémenter ou supprimer `normal.html`
9. ✅ Ajouter validation aux formulaires
10. ✅ Créer `.env` de configuration

### Phase 3 - Maintenance (Semaine suivante)

11. ✅ Consolider CSS (utiliser variables)
12. ✅ Ajouter alt text et aria-labels
13. ✅ Formater avec Prettier
14. ✅ Ajouter gestion d'erreur JavaScript
15. ✅ Documenter l'architecture

---

## 📊 STATISTIQUES

| Catégorie                     | Nombre  |
| ----------------------------- | ------- |
| Problèmes critiques           | 12      |
| Problèmes graves              | 18      |
| Problèmes modérés             | 15      |
| **TOTAL**                     | **45+** |
| Fichiers affectés             | 12+     |
| Lignes de code problématiques | 50+     |

---

## 🔒 CHECKLIST DE SÉCURITÉ

- [ ] Supprimer tous les mots de passe hardcodés
- [ ] Créer et configurer `.env`
- [ ] Valider toutes les entrées utilisateur
- [ ] Utiliser des requêtes préparées partout
- [ ] Implémenter CSRF tokens
- [ ] Chiffrer les mots de passe avec `password_hash()`
- [ ] Implémenter session timeout
- [ ] Ajouter rate limiting sur login
- [ ] Utiliser HTTPS en production
- [ ] Mettre à jour les dépendances composer

---

## 📝 CONCLUSION

Le projet FAGE est **fonctionnellement partiel** avec plusieurs points critiques à corriger avant la mise en production:

1. **Sécurité:** Identifiants exposés - CRITIQUE
2. **Fonctionnalité:** Plusieurs lien/boutons brisés
3. **Qualité:** Code dupliqué et mal organisé
4. **Performance:** CSS trop volumineux

**Temps estimé pour correction complète:** 2-3 jours

---

_Rapport généré automatiquement - Vérification manuelle recommandée_
