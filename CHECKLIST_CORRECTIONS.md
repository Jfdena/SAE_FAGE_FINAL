# 📋 CHECKLIST DÉTAILLÉE DE CORRECTION - FAGE

## 1. SÉCURITÉ - IDENTIFIANTS

### ❌ À CORRIGER EN PRIORITÉ

**Fichier:** `Backend/views/auth/login.php`

#### 1.1 Ligne 44 - Fallback hardcodé

- [ ] Localiser: `($password === 'admin123' && $email === 'admin@fage.fr')`
- [ ] Supprimer cette condition
- [ ] Garder seulement: `password_verify($password, $user['password'])`
- [ ] Tester le login sans fallback

#### 1.2 Ligne 25 - Variable morte

- [ ] Supprimer: `$hashed_password = password_hash($password, PASSWORD_DEFAULT);`
- [ ] Vérifier que rien ne l'utilise (ne devrait pas)

#### 1.3 Lignes 205-207 - Affichage identifiants

- [ ] Supprimer le bloc:

```html
<div class="test-credentials">
  <p class="mb-1"><strong>Email :</strong> admin@fage.org</p>
  <p class="mb-0"><strong>Mot de passe :</strong> admin123</p>
</div>
```

#### 1.4 Lignes 229-231 - Script de test

- [ ] Supprimer le bloc:

```javascript
if (window.location.search.includes("test")) {
  document.querySelector('input[name="email"]').value = "admin@fage.org";
  document.querySelector('input[name="password"]').value = "admin123";
}
```

---

## 2. CONFIGURATION - FICHIER .env

### ✅ À CRÉER

#### 2.1 Créer le fichier

- [ ] Créer `SAE_FAGE_FINAL/.env`
- [ ] Ajouter le contenu:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=your_secure_password
DB_NAME=fage_database

# Application
APP_ENV=development
APP_DEBUG=false
APP_URL=http://localhost

# Session
SESSION_TIMEOUT=3600
```

#### 2.2 Git ignore

- [ ] Ouvrir `.gitignore`
- [ ] Ajouter: `.env`
- [ ] Ajouter: `.env.local`

---

## 3. LIENS HTML - CORRECTION

### 3.1 Actualité_Ressources.html - Ligne 394

- [ ] Ouvrir `HTML/Actualité_Ressources.html`
- [ ] Trouver: `action="Actualité_Ressource.html"`
- [ ] Changer en: `action="Actualité_Ressources.html"`
- [ ] Tester le formulaire de filtre

### 3.2 fichier.js - Ligne 47

- [ ] Ouvrir `assets/Javascript/fichier.js`
- [ ] Trouver: `window.location.href = "mettre lien page inscription"`
- [ ] Changer en: `window.location.href = "Inscription_Asso.html"`
- [ ] Tester le bouton "Inscrire ton Asso"

### 3.3 session_check.php - Ligne 33

- [ ] Ouvrir `Backend/session_check.php`
- [ ] Trouver: `header('Location: /auth/login.php');`
- [ ] Changer en: `header('Location: ../views/auth/login.php');`
- [ ] Tester une session expirée

### 3.4 normal.html - Option A (Supprimer)

- [ ] Supprimer `Backend/normal.html`
- [ ] Rechercher dans tous les HTML: `../Backend/normal.html`
- [ ] Supprimer ou remplacer ces liens

**OU Option B (Implémenter)**

- [ ] Créer `Backend/normal.html` ou `Backend/views/member/dashboard.php`
- [ ] Ajouter le contenu de base (h1, bienvenue, etc.)

---

## 4. INCLUDES/REQUIRES - COHÉRENCE

### 4.1 Backend/views/auth/login.php

- [ ] Ligne 3: ✅ `require_once __DIR__ . '/../../config/Database.php';`
- [ ] Ligne 4: ✅ `require_once __DIR__ . '/../../../vendor/autoload.php';`
- [ ] Ligne 32: ❌ `require_once '../../config/Database.php';`
  - [ ] Changer en: `require_once __DIR__ . '/../../config/Database.php';`
- [ ] Tester que Database.php se charge bien

---

## 5. CSS - REDONDANCE

### 5.1 Supprimer définition dupliquée

- [ ] Ouvrir `assets/css/stylesheet.css`
- [ ] Localiser les deux définitions de `.tint-text` et `.text-decoration-none`:
  - Première: Lignes 44-48
  - Deuxième: Lignes 79-83
- [ ] Garder la première
- [ ] Supprimer la deuxième (lignes 79-83)
- [ ] Tester les styles (liens doivent rester marron)

### 5.2 Corriger couleur similaire

- [ ] Ligne 258: Trouver `#afficher-banniere`
- [ ] Changer: `background-color: #173045;` → `background-color: #183146;`
- [ ] Vérifier visuellemement que c'est cohérent

### 5.3 Refactor CSS variables (Optionnel - Bonus)

- [ ] Ajouter en début du fichier:

```css
:root {
  --fage-primary: #183146;
  --fage-secondary: #0d6efd;
  --fage-bg: #f8f9fa;
}
```

- [ ] Remplacer tous les `#183146` par `var(--fage-primary)`
- [ ] Remplacer tous les `#0d6efd` par `var(--fage-secondary)`

---

## 6. SIDEBAR - NAVIGATION

### 6.1 Fichier Backend/views/partials/sidebar.php

#### 6.1.1 Lien Bénévoles

- [ ] Ligne 13: `<a href="adherents/list.php"`
- [ ] Vérifier le vrai fichier: `adherents/listeAdherent.php`
- [ ] Corriger: `<a href="adherents/listeAdherent.php"`

#### 6.1.2 Lien Événements

- [ ] Ligne 21: `<a href="missions/list.php"`
- [ ] Vérifier le vrai fichier: `missions/addMissions.php` ou `editMissions.php`
- [ ] Corriger en conséquence

#### 6.1.3 Lien Partenaires

- [ ] Ligne 27: `<a href="partenaires/list.php"`
- [ ] Vérifier le vrai fichier: `partenaires/editPartenaire.php`
- [ ] Corriger en conséquence

#### 6.1.4 Lien Actualités

- [ ] Ligne 45: `<a href="communication/actualites.php"`
- [ ] Vérifier si ce dossier/fichier existe
- [ ] Si non: Remplacer par un lien valide ou commenter

#### 6.1.5 Lien Statistiques

- [ ] Ligne 47: `<a href="statistiques/index.php"`
- [ ] Vérifier si ce dossier/fichier existe
- [ ] Si non: Remplacer ou commenter

#### 6.1.6 Déconnexion

- [ ] Ligne 51: `<a href="../../Backend/controllers/AuthController.php?action=logout"`
- [ ] Créer le fichier `Backend/views/auth/logout.php`:

```php
<?php
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
```

- [ ] Corriger le lien: `<a href="../auth/logout.php"`

---

## 7. ACCESSIBILITÉ - ALT TEXT

### 7.1 HTML/Accueil.html

- [ ] Chercher tous les `<img`
- [ ] Ajouter `alt="..."` descriptif
- [ ] Exemple: `alt="Logo FAGE"`, `alt="Photo de Laurent"`, etc.

### 7.2 HTML/Actualité_Ressources.html

- [ ] Même chose: tous les `<img>` doivent avoir un alt
- [ ] Exemple: `alt="Congrès National de la FAGE"`, `alt="Vidéo AGORAE 2024"`

### 7.3 Vérifier les autres fichiers HTML

- [ ] HTML/Dons_Engagement.html
- [ ] HTML/Inscription_Asso.html
- [ ] HTML/A_propos.html
- [ ] Etc.

---

## 8. ACCESSIBILITÉ - ARIA-LABELS

### 8.1 Boutons sans texte

- [ ] Chercher tous les `<button>`
- [ ] Si pas de texte visible: ajouter `aria-label="..."`
- [ ] Exemple:

```html
<!-- ❌ Avant -->
<button class="navbar-toggler">
  <span class="navbar-toggler-icon"></span>
</button>

<!-- ✅ Après -->
<button class="navbar-toggler" aria-label="Afficher le menu de navigation">
  <span class="navbar-toggler-icon"></span>
</button>
```

### 8.2 Tous les fichiers HTML

- [ ] Rechercher: `<button`, `<a role="button"`, `<input type="button"`
- [ ] Ajouter aria-label si nécessaire

---

## 9. VALIDATION FORMULAIRES

### 9.1 Formulaire Newsletter (HTML/Accueil.html)

- [ ] Ajouter `method="POST"` et `action="Backend/controllers/NewsletterController.php"`
- [ ] Créer le fichier `Backend/controllers/NewsletterController.php`
- [ ] Ajouter validation email
- [ ] Ajouter enregistrement en BD

### 9.2 Formulaire Login (Backend/views/auth/login.php)

- [ ] ✅ Déjà avec `method="POST" action=""`
- [ ] Vérifier qu'il valide correctement

### 9.3 Formulaire Filtre (HTML/Actualité_Ressources.html)

- [ ] Ajouter action/method
- [ ] Créer le traitement côté serveur si nécessaire

---

## 10. BASE DE DONNÉES

### 10.1 Fichier Database.php - Correction

- [ ] Vérifier que les variables `$_ENV` sont chargées
- [ ] Ajouter gestion d'erreur
- [ ] Tester la connexion

### 10.2 Fichier Constraints.php - Correction

- [ ] Chercher `fetch_assoc()` vs `execute()` vs `fetchColumn()`
- [ ] Corriger pour mysqli (pas PDO)
- [ ] Ajouter `bind_param()` au lieu de passer params à execute()

### 10.3 SQL Injection - Vérifier

- [ ] Tous les prepare() doivent être suivis de bind_param()
- [ ] Pas d'interpolation directe dans les requêtes

---

## 11. TESTS - FONCTIONNALITÉ

### 11.1 Navigation

- [ ] [ ] Tous les liens HTML fonctionnent
- [ ] [ ] Page d'accueil chargée
- [ ] [ ] Menu mobile fonctionne
- [ ] [ ] Lien Admin redirige vers login
- [ ] [ ] Tous les liens du header/footer valides

### 11.2 Formulaires

- [ ] [ ] Formulaire login fonctionne
- [ ] [ ] Formulaire newsletter fonctionne
- [ ] [ ] Formulaire filtre fonctionne
- [ ] [ ] Messages d'erreur apparaissent

### 11.3 Admin/Backend

- [ ] [ ] Login possible sans identifiants hardcodés
- [ ] [ ] Sidebar navigation fonctionne
- [ ] [ ] Déconnexion fonctionne
- [ ] [ ] Page protégée par session

### 11.4 Base de Données

- [ ] [ ] Connexion réussit
- [ ] [ ] INSERT fonctionne
- [ ] [ ] SELECT fonctionne
- [ ] [ ] UPDATE/DELETE fonctionnent

---

## 12. TESTS - SÉCURITÉ

### 12.1 SQL Injection

- [ ] [ ] Tester: `'; DROP TABLE--` dans un formulaire
- [ ] [ ] Devrait être safe grâce aux prepared statements

### 12.2 XSS

- [ ] [ ] Tester: `<script>alert('test')</script>` dans un formulaire
- [ ] [ ] Vérifier que htmlspecialchars() est utilisé

### 12.3 Session

- [ ] [ ] Vérifier session timeout fonctionne
- [ ] [ ] Vérifier CSRF tokens (bonus)

### 12.4 Identifiants

- [ ] [ ] ✅ Pas d'identifiants en dur trouvés
- [ ] [ ] ✅ Mots de passe hashés en BD
- [ ] [ ] ✅ .env contenait dans gitignore

---

## 13. PERFORMANCE

### 13.1 CSS

- [ ] [ ] Supprimer CSS dupliqué
- [ ] [ ] Utiliser CSS variables
- [ ] [ ] Minifier si possible

### 13.2 JavaScript

- [ ] [ ] Ajouter gestion d'erreur
- [ ] [ ] Ajouter try/catch
- [ ] [ ] Vérifier que les sélecteurs existent

### 13.3 Images

- [ ] [ ] Vérifier taille des images
- [ ] [ ] Compresser si > 100KB
- [ ] [ ] Utiliser formats modernes (WebP)

---

## 14. DOCUMENTATION

### 14.1 README.md

- [ ] [ ] Créer ou mettre à jour
- [ ] [ ] Ajouter instructions installation
- [ ] [ ] Ajouter structure du projet
- [ ] [ ] Ajouter commandes utiles

### 14.2 Commentaires dans le code

- [ ] [ ] Ajouter commentaires pour sections complexes
- [ ] [ ] Choisir une langue (recommandé: anglais)

### 14.3 Architecture

- [ ] [ ] Documenter les chemins
- [ ] [ ] Documenter les conventions

---

## ✅ CHECKLIST FINALE

### Avant de déployer:

- [ ] Tous les identifiants supprimés
- [ ] Fichier .env créé et dans gitignore
- [ ] Tous les liens testés
- [ ] Formulaires testés
- [ ] BD connectée et requêtes OK
- [ ] Sessions sécurisées
- [ ] Alt text sur images
- [ ] Aria-labels sur boutons
- [ ] Pas d'erreurs console JS
- [ ] Pas d'erreurs PHP

### Après déploiement:

- [ ] Vérifier en production que tout fonctionne
- [ ] Vérifier les logs d'erreur
- [ ] Vérifier les performances
- [ ] Communiquer avec l'équipe

---

## 📞 AIDE & QUESTIONS

Si vous avez des questions sur une correction:

1. Consulter RAPPORT_AUDIT_COMPLET.md pour les détails
2. Consulter GUIDE_CORRECTIONS.md pour le code
3. Chercher dans RESUME_EXECUTIF.md pour les priorités

Bonne correction! 🚀
