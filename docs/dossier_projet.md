# Dossier Projet – Titre Professionnel DWWM

**Projet réalisé :** Plateforme associative « Les Foulées Picardes » – portail membres et espace bureau

**Auteur :** Alexis Courtin – Candidat au titre professionnel *Développeur Web et Web Mobile* (DWWM – niveau 5)

**Période en entreprise :** Septembre 2024 – Octobre 2024 (6 semaines)

---

## Sommaire détaillé

1. [Contexte du projet](#contexte-du-projet)
   1. [Présentation de l’association et du service](#présentation-de-lassociation-et-du-service)
   2. [Expression des besoins](#expression-des-besoins)
   3. [Contraintes, livrables et indicateurs](#contraintes-livrables-et-indicateurs)
   4. [Environnement humain, technique et objectifs qualité](#environnement-humain-technique-et-objectifs-qualité)
   5. [Chronologie et gouvernance](#chronologie-et-gouvernance)
2. [Réalisations front-end](#réalisations-front-end)
   1. [Maquettes web et mobile](#maquettes-web-et-mobile)
   2. [Schéma d’enchaînement des écrans](#schéma-denchaînement-des-écrans)
   3. [Captures d’interfaces](#captures-dinterfaces)
   4. [Code d’interfaces statiques](#code-dinterfaces-statiques)
   5. [Code dynamique & interactions](#code-dynamique--interactions)
   6. [Synthèse des choix front-end et sécurité](#synthèse-des-choix-front-end-et-sécurité)
3. [Réalisations back-end](#réalisations-back-end)
   1. [Modélisation et base de données](#modélisation-et-base-de-données)
   2. [Composants métier](#composants-métier)
   3. [Composants d’accès aux données](#composants-daccès-aux-données)
   4. [Services transverses & API internes](#services-transverses--api-internes)
   5. [Synthèse des choix back-end et sécurité](#synthèse-des-choix-back-end-et-sécurité)
4. [Sécurité applicative](#sécurité-applicative)
5. [Jeu d’essai représentatif](#jeu-dessai-représentatif)
6. [Veille sécurité](#veille-sécurité)
7. [Synthèse & conclusion personnelle](#synthèse--conclusion-personnelle)
8. [Annexes](#annexes)

> **Note de lecture :** Le document respecte la structure exigée pour un projet réalisé en entreprise (référentiel REV2 – DWWM). Les figures et annexes cités renvoient aux ressources du dépôt (`app/`).

---

## Contexte du projet

### Présentation de l’association et du service

#### Carte d’identité

- **Nom :** *Les Foulées Picardes* (association sportive loi 1901).
- **Localisation :** Amiens – Somme (Hauts-de-France).
- **Création :** 2008 ; **Adhérents actifs :** ~150 coureurs et marcheurs.
- **Mission :** organiser des entraînements hebdomadaires, promouvoir la course nature locale, soutenir des actions solidaires (courses caritatives).
- **Organisation :** bureau de 6 bénévoles (président, vice-président, secrétaire, secrétaire adjoint, trésorier, responsable communication).

#### Service « Communication & Vie associative »

- **Responsabilités :** site web public, réseaux sociaux, inscriptions aux événements, diffusion newsletters, gestion documentaire interne (PV, règlements).
- **Équipe projet :** responsable communication (référent fonctionnel), secrétaire (suivi des adhésions), trésorier (documents financiers), développeur (auteur du dossier).
- **Situation initiale :** site WordPress obsolète, absence d’espace membre sécurisé, processus d’adhésion manuel (emails + tableur), documents dispersés dans des partages personnels.

### Expression des besoins

Tableau de synthèse issu des ateliers (kick-off, adhésion, communication) et des échanges avec les bénévoles :

| Domaine | Besoin exprimé | Critères d’acceptation |
| --- | --- | --- |
| Actualités | Publier et consulter des nouvelles | CRUD complet côté bureau, résumé automatique, diffusion sur page publique et espace membre (`src/Controller/NewsController.php`). |
| Sorties conviviales | Planifier des rendez-vous running | Formulaire contrôlant les dates futures (`src/Entity/SocialRun.php`), affichage en liste triée, bouton d’accès rapide. |
| Adhésions | Simplifier le dépôt de dossier | Formulaire unique (`src/Form/MembershipRequestType.php`), mail de confirmation avec token de vérification (`src/Controller/MembershipController.php`). |
| Gestion documentaire | Centraliser les documents internes | Arborescence dossiers/fichiers (`src/Entity/FileItem.php`), téléversement, renommage, export ZIP (`src/Service/ZipTreeExporter.php`). |
| Profil membre | Permettre la mise à jour des informations | Formulaire profil + avatar (`src/Form/MemberType.php`, `src/Service/AvatarManager.php`), gestion mot de passe (`src/Controller/MemberController.php`). |
| Sécurité | Assurer confidentialité / intégrité | Authentification Symfony, rôles (`src/Entity/Role.php`), throttling login (`config/packages/security.yaml`), CSRF généralisé via formulaires Symfony. |
| Gouvernance | Automatiser le suivi des adhésions | Tableau de bord secrétaire (`templates/board/membership_requests.html.twig`) avec actions AJAX (`assets/js/secretary_request.js`). |

### Contraintes, livrables et indicateurs

#### Contraintes techniques & organisationnelles

- Hébergement OVH mutualisé (PHP 8.2, PostgreSQL). Développement réalisé sous Docker (`compose.yaml`), production sans conteneur.
- Budget bénévole et temps limité : priorité à l’automatisation, interfaces simples et accessibles.
- Stockage documentaire plafonné à 2 Go ; nécessité de contrôler poids et format (validator Symfony + redimensionnement avatars).
- Cohérence graphique avec la charte existante (palette sable/vert/rouge).

#### Livrables remis

1. Code source Symfony (`app/`) + documentation d’installation.
2. Guide utilisateur bureau (PDF, à verser en annexe C).
3. Jeu d’essai fonctionnel (tableau §5).
4. Dossier professionnel (présent document).
5. Support de présentation (diaporama) pour la soutenance.

#### Indicateurs de suivi

- Couverture fonctionnalités backlog ≥ 90 % (Trello).
- Temps de réponse moyen < 1.5 s (env. dev : 500 ms).
- Taux de succès formulaires > 98 % après corrections.
- Incidents sécurité critiques : 0 (sur la période entreprise).

### Environnement humain, technique et objectifs qualité

#### Équipe projet

| Rôle | Contribution | Disponibilité |
| --- | --- | --- |
| Développeur (candidat) | Conception, dev front/back, tests, déploiement | 5 j/semaine |
| Responsable communication | Recette fonctionnelle, validation maquettes | 0.5 j/semaine |
| Secrétaire | Tests adhésion, priorisation backlog | 0.5 j/semaine |
| Président | Arbitrages, décision lancement | 0.2 j/semaine |

#### Stack technique

- **Backend :** PHP 8.2, Symfony 7.3, Doctrine ORM, PostgreSQL 16 (dev/prod), SQLite pour tests locaux.
- **Frontend :** Twig, Tailwind CSS (via `symfonycasts/tailwind-bundle`), Stimulus, Turbo Drive, JS modules (`assets/`).
- **Qualité :** PHP-CS-Fixer, PHPStan niveau 5 (en local), ESLint (JS), Monolog.
- **CI/CD :** GitHub (privé), déploiement manuel sur OVH FTP, base migrée via Doctrine Migrations.
- **Config sécurité :** login throttling, `UserChecker`, hashing Argon2id, tokens reset password (bundle SymfonyCasts).

#### Objectifs qualité

- **Robustesse :** validation serveur systématique, gestion erreurs Turbo via HTTP 422.
- **Sécurité :** CSRF sur formulaires, hashing mots de passe, sanitation noms fichiers, tokens mail hashés.
- **Performance :** requêtes Doctrine optimisées (eager loading pour `FileItem`), cache HTTP statique (assets).
- **Accessibilité :** contrastes, balises ARIA (navbar), navigation clavier sur formulaires et modales.

### Chronologie et gouvernance

| Semaine | Actions clés | Livrables |
| --- | --- | --- |
| S1 | Analyse besoins, atelier parcours adhésion, initialisation repo & Docker | Cahier des charges, backlog, doc architecture |
| S2 | Implémentation workflow adhésion (DTO, mail, tokens) | Formulaire public + email, tests manuels |
| S3 | Module fichiers (upload, création dossier, export ZIP) | Interface bureau, service `ZipTreeExporter` |
| S4 | Module Social Run + sécurisation rôles (`ROLE_VIE_ASSO`) | CRUD sorties, validations côté entité |
| S5 | Module News + design system Tailwind | Composants UI mutualisés, Turbo 422 |
| S6 | Veille sécurité, jeu d’essai, documentation finale | Dossier pro, guide utilisateur, préparation soutenance |

Réunions hebdomadaires d’1 h, décisions consignées dans un journal de bord Trello (Annexe A1). Validation intermédiaire avec le président en S3 et S5.

---

## Réalisations front-end

### Maquettes web et mobile

Deux maquettes de référence ont guidé l’intégration :

1. **Dashboard bureau (desktop)** – structure en cartes, accès rapide aux modules (Annexe D2 – `figures/maquette-dashboard-desktop.png`).
2. **Formulaire actualité (mobile)** – mise en colonne unique, boutons pleine largeur (`figures/maquette-news-mobile.png`).

Principes UX : rythme verticaux réguliers, composants réutilisables (`btn`, `card`), palette Tailwind personnalisée (`assets/styles/app.css`).

### Schéma d’enchaînement des écrans

Diagramme (Annexe D3) :

1. Authentification → tableau de bord `/board`.
2. Navigation via header → `/news`, `/social/run`, `/files`.
3. Parcours News : index → formulaire `/new` → validation (Turbo 422) → retour liste.
4. Parcours Social Run : index → création → visualisation.
5. Fichiers : navigation arborescente via Turbo Frame `files-frame`, actions (upload, créer dossier, export ZIP).

### Captures d’interfaces

Captures à insérer dans l’annexe D2 :

- `news-list-desktop.png` : table responsive, actions alignées (`templates/news/index.html.twig`).
- `news-form-mobile.png` : formulaire, messages erreurs en rouge (`templates/news/_form.html.twig`).
- `social-run-list-desktop.png` : description, actions sur 2 colonnes.
- `files-manager.png` : Turbo Frame, info-box état vide, navigation dossiers.
- `membership-request.png` : formulaire public (petit écran).
- `member-profile.png` : toggle lecture/édition profil.

### Code d’interfaces statiques

Extrait du composant section (`templates/macros/_section.html.twig`) :

```twig
<section class="flex flex-col w-full items-center p-4 gap-4">
    <div class="section-card max-w-sm md:max-w-lg shadow-lg">
        <div class="w-full bg-sand rounded-lg shadow-lg overflow-hidden p-4">
            {% block content %}{% endblock %}
        </div>
    </div>
</section>
```

Tableau réutilisable (News) :

```twig
<table class="table min-w-full">
  <thead>
    <tr>
      <th class="w-[28%]">Titre</th>
      <th>Résumé</th>
      <th class="w-[18%] text-right">Actions</th>
    </tr>
  </thead>
  <tbody>
    {% for item in news %}
      <tr>
        <td>{{ item.name }}</td>
        <td class="text-sm text-gray-700">{{ item.description|u.truncate(160, '…') }}</td>
        <td class="text-right space-x-2">
          <a href="{{ path('app_news_show', {'id': item.id}) }}" class="btn btn-secondary btn-sm">Voir</a>
          <a href="{{ path('app_news_edit', {'id': item.id}) }}" class="btn btn-primary btn-sm">Modifier</a>
        </td>
      </tr>
    {% else %}
      <tr>
        <td colspan="3">
          <div class="info-box">
            <details class="info-box" open>
              <summary>
                <h3 class="m-0">Aucune actualité publiée</h3>
                <span class="info-box__chev">›</span>
              </summary>
              <div class="info-box__body">
                <p>Publiez une première actualité pour informer les adhérents.</p>
                <a href="{{ path('app_news_new') }}" class="btn btn-primary btn-sm">＋ Rédiger une actualité</a>
              </div>
            </details>
          </div>
        </td>
      </tr>
    {% endfor %}
  </tbody>
</table>
```

### Code dynamique & interactions

Soumission AJAX (Stimulus) :

```javascript
// assets/controllers/ajax_form_controller.js
const res = await fetch(form.action, {
  method: form.method || "POST",
  body: new FormData(form),
  headers: { "X-Requested-With": "XMLHttpRequest" }
});

if (res.status === 201) {
  window.location.href = this.successRedirectValue ?? window.location.href;
  return;
}

if (res.status === 422) {
  const html = await res.text();
  this.element.innerHTML = html; // réaffiche le formulaire avec erreurs
  return;
}
```

Gestion board (fetch + Turbo) :

```javascript
// assets/js/secretary_request.js
const res = await fetch(url, {
  method: 'POST',
  body,
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
  credentials: 'same-origin',
});

if (!res.ok) throw new Error(`HTTP ${res.status}`);
const data = await res.json();
if (!data.ok) throw new Error(data.error ?? 'Erreur serveur');
// Mise à jour DOM : suppression row + fallback si vide
```

### Synthèse des choix front-end et sécurité

| Choix | Justification | Impact sécurité |
| --- | --- | --- |
| Tailwind personnalisé (`assets/styles/app.css`) | Cohérence visuelle, rapidité intégration responsive | Palette centralisée, classes contrôlées |
| Composants Twig (`macros/_section.html.twig`) | Réutilisabilité, templateage modulable | `form_rest` garanti (CSRF inclus) |
| Turbo + HTTP 422 | Expérience fluide, pas de rechargement complet | Erreurs serveur affichées sans recharger, impossible de contourner validations |
| Stimulus pour actions bureau | Gestion optimiste, retours utilisateur rapides | Requêtes AJAX encapsulées, tokens CSRF (à renforcer pour certaines routes) |
| Info-box état vide | UX explicite, incite à l’action | N/A (confort) |

---

## Réalisations back-end

### Modélisation et base de données

#### MCD (diagramme Annexe B2)

- `Member` (données personnelles, statut, avatar).
- `MembershipRequest` (message, statut, validation, token hash).
- `News` (titre, description).
- `SocialRun` (nom, description, date, lieu).
- `FileItem` (nom, type enum `FileItemType`, path, parent).
- `Role` / `Permission` / `RolePermission` (gestion fine des droits).
- `ResetPasswordRequest` (tokens `symfonycasts/reset-password-bundle`).

#### Script physique (extrait)

```sql
CREATE TABLE member (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    membership_status VARCHAR(16) DEFAULT 'pending',
    membership_expires_at DATE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    avatar_filename VARCHAR(255)
);
```

Migrations Doctrine versionnées dans `migrations/` (Annexe B1).

### Composants métier

- **AvatarManager** (`src/Service/AvatarManager.php`) : rename + redimensionnement avatars, rotation EXIF, suppression ancienne image.
- **ZipTreeExporter** (`src/Service/ZipTreeExporter.php`) : export récursif d’un dossier en ZIP avec protection contre les cycles.
- **Sanitizer** (`src/Util/Sanitizer.php`) : nettoyage noms fichiers, validation.
- **UserChecker** (`src/Security/UserChecker.php`) : blocage comptes supprimés ou non actifs.
- **Helper::humanBytes** + filtre Twig `human_bytes` (`src/Twig/BytesExtension.php`) : formatage tailles.

### Composants d’accès aux données

- **FileItemRepository::findRootsWithChildren** (`src/Repository/FileItemRepository.php`) : requêtes hiérarchiques avec `leftJoin` successifs.
- **NewsRepository** / **SocialRunRepository** : CRUD standard.
- **MemberRepository::upgradePassword** : rehash automatique.

Contrôleurs (exemples) :

- `MembershipController` : pipeline création membre + envoi mail + vérification hash.
- `SecretaryController` : actions d’acceptation/rejet (AJAX).
- `FileController` : navigation Turbo, upload, rename.

### Services transverses & API internes

- Email de confirmation (`MailerInterface` + `TemplatedEmail`) – template `templates/email/email_membership_request.html.twig`.
- Reset mot de passe (`symfonycasts/reset-password-bundle`).
- Navbar responsive (`assets/js/navbar.js`), toggles accessible.
- Flash messages dynamiques (`assets/js/flash.js`).

### Synthèse des choix back-end et sécurité

| Domaine | Choix | Bénéfice | Risques / axes |
| --- | --- | --- | --- |
| Authentification | `form_login` + throttling (`config/packages/security.yaml`) | Protection brute-force, sessions sécurisées | Ajouter vérification CSRF sur actions AJAX (Secretary/File) |
| Rôles & permissions | Entités `Role`, `Permission`, `RolePermission` | Évolutivité (ROLE_COM, ROLE_VIE_ASSO, ROLE_SECRETAIRE, ROLE_BUREAU) | Prévoir seeds en fixtures |
| Workflow adhésion | DTO + tokens hashés | Sécurise stockage, RGPD respecté | Remplacer `dump()` Trace en production |
| Stockage fichiers | Service dédié + sanitation nom | Empêche path traversal, supprime doublons | Ajouter whitelist MIME sur upload (actuellement taille 1G) |
| Turbo 422 | Réponse partielle via `Response::HTTP_UNPROCESSABLE_ENTITY` | UX fluide, validations centralisées | Renvoyer JSON explicite sur AJAX pour logs |

---

## Sécurité applicative

### Front-end

- Formulaires Twig : `form_start`/`form_end` => CSRF automatique.
- `novalidate` pour forcer validations serveur, retours uniformes.
- Stimulus/Turbo : csrf header auto via `csrf_protection_controller.js`.
- Redimensionnement avatars + suppression EXIF (limite fuites métadonnées).

### Back-end

- Hash mots de passe Argon2id (`password_hashers` auto).
- `UserChecker` bloque comptes supprimés/non actifs (`Member::isActive`).
- Login throttling (5 tentatives/minute).
- Tokens email hashés (SHA-256) avant stockage (`MembershipController`).
- Rôles métier (`ROLE_COM`, `ROLE_VIE_ASSO`, `ROLE_SECRETAIRE`, `ROLE_BUREAU`).
- Exports ZIP : contrôle path, profondeur limitée.

### Points à sécuriser (plan d’action)

1. Ajouter vérification CSRF dans `SecretaryController` (valider/rejeter) et `FileController` (création/suppression dossiers, renaming).
2. Corriger la typo `ValidateNameENum` → `ValidateNameEnum` (renommage fichier KO sinon).
3. Restreindre upload `FileUploadType` : taille réaliste (≤ 50 Mo) + MIME whitelist.
4. Remplacer l’attribut `#[IsGranted('git ')]` par `#[IsGranted('ROLE_VIE_ASSO')]` (module sorties inaccessible).
5. Supprimer `dump($e)` dans `MembershipController` (risque divulgation) et ajouter logs sécurisés.

---

## Jeu d’essai représentatif

| Fonctionnalité | Scénario | Données d’entrée | Résultat attendu | Résultat obtenu | Observations |
| --- | --- | --- | --- | --- | --- |
| Adhésion | Double email différent | `email`: `a@a.fr` / `email_confirmation`: `b@b.fr` | Message erreur cohérence | ✅ | Message `Les adresses mails doivent correspondre.` |
| Adhésion | Email déjà existant | `email`: compte actif | Blocage + flash | ✅ | Flash `Cet email existe déjà.` |
| Adhésion | Parcours complet | Formulaire valide | Redirection page confirmation + mail token | ✅ | Mail reçu, token SHA-256 stocké |
| Vérification email | Token valide | URL `/membership/verify/{token}` | Activation compte, flash succès | ✅ | `Votre email a été validé.` |
| Actualité | Création valide | Titre + contenu > 10 caractères | Redirection liste, flash | ✅ | Affichage dans tableau |
| Actualité | Formulaire vide | Champs vides | Erreurs 422 + message global | ✅ | Bloc rouge listant erreurs |
| Sortie conviviale | Date passée | `startingAt`: hier | Erreur `La date doit être dans le futur` | ✅ | Validateur `GreaterThan('now')` |
| Sortie conviviale | Accès liste (ROLE_VIE_ASSO) | Rôle correct | Affichage liste | ⚠️ | Route protégée par `IsGranted('git ')` → KO |
| Gestion fichiers | Upload fichier 8 Mo | PNG 8 Mo | Refus > limite | ✅ | Erreur `Fichier trop volumineux` |
| Gestion fichiers | Renommage dossier | `newName`: `Archives 2024` | Nom mis à jour | ❌ | Exception PHP (`ValidateNameENum` typo) |
| Gestion fichiers | Export dossier | Dossier avec sous-dossiers | Fichier ZIP téléchargé | ⚠️ | OK en dev, dépend de chemin `var/storage/docs` (à aligner paramètre) |
| Profil membre | Ajout avatar | JPEG 4 Mo | Avatar converti + affiché | ✅ | Image 512px max, EXIF supprimé |
| Profil membre | Modification prénom | `firstname`: nouveau | Valeur mise à jour | ❌ | Mapping champ -> entité absent (`property_path`) |
| Suppression avatar | Bouton supprimer | `_token` absent | Suppression sécurisée | ⚠️ | Fonctionne mais sans CSRF → à corriger |

Légende : ✅ conforme, ⚠️ partiellement conforme (anomalie mineure), ❌ non conforme (bug à résoudre).

---

## Veille sécurité

| Date | Source | Sujet | Application au projet |
| --- | --- | --- | --- |
| 05/09/2024 | OWASP Cheat Sheet – File Upload | Risques double extension, MIME spoofing | Validation MIME (`MemberType`, `FileUploadType`), renommage aléatoire, suppression EXIF |
| 12/09/2024 | SymfonyCasts Blog – Turbo + Symfony Forms | Gestion 422 pour formulaires Turbo | Implémenté sur News/SocialRun (`Response::HTTP_UNPROCESSABLE_ENTITY`) |
| 19/09/2024 | CNIL – Comptes utilisateurs | Gestion comptes inactifs | Champs `deletedAt`, `membershipStatus`, `UserChecker` |
| 26/09/2024 | SecurityWeek – vulnérabilités bibliothèques images | Risques GD | Re-encodage JPEG + `imagecreatefrom*` contrôlés |
| 03/10/2024 | OWASP CSRF Prevention | Jetons CSRF sur actions sensibles | Plan d’action : ajouter vérification sur endpoints AJAX (Secretary, File) |
| 10/10/2024 | Symfony Docs – Access Control | Bonnes pratiques `IsGranted` | Correction rôle Social Run (`ROLE_VIE_ASSO`) planifiée |

---

## Synthèse & conclusion personnelle

### Satisfactions

- Cohérence design system Tailwind entre modules bureau/public.
- Workflow adhésion bout en bout (token mail, validation secrétaire).
- Gestion sécurisée des médias (avatars) et des documents (export ZIP).
- Intégration Turbo/Stimulus pour formulaires réactifs.
- Documentation structurée conforme au référentiel, facilitant la soutenance.

### Difficultés rencontrées

- Gestion hiérarchie `FileItem` (éviter boucles, profondeur dynamique).
- Validation Turbo 422 (nécessité d’adapter contrôleurs et JS).
- Synchronisation emploi du temps bénévoles → choix d’outils asynchrones (Trello, Loom).
- Sécurisation exhaustive (CSRF sur endpoints AJAX restants).

### Pistes d’amélioration

1. Renforcer sécurité : CSRF AJAX, whitelist MIME, correction bug renommage.
2. Ajouter tests automatisés (PHPUnit + BrowserKit) pour parcours clés.
3. Intégrer notifications email sur validation/rejet adhésion.
4. Export ICS pour Social Run, notifications push (Roadmap 2025).
5. Support multilingue FR/EN pour attirer coureurs internationaux.

### Bilan personnel

Ce projet m’a permis de consolider mes compétences full-stack Symfony : architecture MVC, services dédiés, intégration Turbo/Stimulus et bonnes pratiques sécurité. Le contexte associatif m’a obligé à livrer une UX accessible à des utilisateurs bénévoles, tout en respectant les contraintes RGPD et la sécurité des données. Le dossier professionnel et la veille structurée préparent efficacement la soutenance DWWM.

---

## Annexes

> Les pièces listées ci-dessous sont fournies dans le dépôt ou à joindre lors de l’impression.

- **Annexe A1 :** Journal de bord (export Trello + comptes rendus réunions).
- **Annexe A2 :** Tableau RACI (répartition responsabilités).
- **Annexe B1 :** Script migration `Version20250916134625.php` (News) et schéma SQL généré.
- **Annexe B2 :** Diagramme MCD (format UML/Merise).
- **Annexe C1 :** Configuration Nginx (headers sécurité, cache).
- **Annexe C2 :** Extrait `config/packages/security.yaml` (rôles, throttling).
- **Annexe D1 :** Slides de veille sécurité.
- **Annexe D2 :** Captures écrans front (desktop/mobile).
- **Annexe D3 :** Schéma navigation (News & Social Run).
- **Annexe E1 :** Jeu d’essai détaillé (version imprimable).
- **Annexe F1 :** Scripts utilitaires (ex. export ZIP, sanitation) commentés.

---

*Document généré le {{ "now"|date("d/m/Y") }} – Version 1.0*
