# Rapport d'analyse – Plateforme « Les Foulées Picardes »

_Auteur : Codex (assistant IA)_  
_Date : 2025-03-17_  
_Périmètre : audit code + conformité dossier projet (version « période en entreprise » du RE DWWM)_

---

## 1. Vue d'ensemble

- Application Symfony 7.3 / PHP 8.2 déployée pour une association sportive (gestion adhérents, actualités, sorties conviviales, fichiers internes).
- Code structuré selon une architecture MVC classique, enrichie de services maison (`AvatarManager`, `ZipTreeExporter`) et Stimulus/Turbo pour l’interactivité.
- Dossier `docs/dossier_projet.md` déjà aligné sur le plan « projet en entreprise » du référentiel. Le présent rapport fournit la lecture critique à intégrer.

---

## 2. Méthodologie d'audit

1. Lecture du référentiel REV2 DWWM (sections « projet en entreprise ») : attentes sur le dossier, la présentation et les compétences évaluées.
2. Inspection statique du code (entités, contrôleurs, services, assets JS/CSS, templates Twig, config de sécurité) au moyen des chemins fournis.
3. Recensement des écarts notables, risques de sécurité et incohérences fonctionnelles, puis priorisation.
4. Mise en regard des forces/faiblesses avec les rubriques du dossier pro (front, back, BDD, sécurité, veille, jeu d’essai).

---

## 3. Cartographie du projet face au référentiel

| Bloc référentiel (version entreprise) | Couverture dans le code | Observations |
| --- | --- | --- |
| Liste compétences mobilisées | `docs/dossier_projet.md:1` | Plan conforme (front/back, sécurité, jeu d’essai, veille). |
| Contexte entreprise, service, gouvernance | `docs/dossier_projet.md:18` | Détails exploitables pour intro dossier. |
| Réalisations front-end | `templates/news/index.html.twig:1`, `templates/social_run/index.html.twig:1` | Parcours bureau (news, sorties) + rôle `ROLE_COM`/`ROLE_VIE_ASSO`. |
| Réalisations back-end | `src/Controller/MembershipController.php:60`, `src/Service/ZipTreeExporter.php:17` | Gestion adhésions, stockage fichiers, export ZIP. |
| Base de données | Entités `src/Entity/*.php` | Modèle cohérent (Member ↔ MembershipRequest, FileItem arborescent). |
| Sécurité front/back | `src/Service/AvatarManager.php:15`, `src/Security/UserChecker.php:9` | Hashing, throttling, rotation avatars, mais voir faiblesses §5. |
| Jeu d’essai | `docs/dossier_projet.md:240` | Tableau existant, à compléter après corrections/tests. |
| Veille sécurité | `docs/dossier_projet.md:260` | Traçabilité déjà amorcée. |

---

## 4. Points forts identifiés

- **Validation métier riche** : règles sur `Member`, `SocialRun`, `MembershipRequest` (`src/Entity/Member.php:23`, `src/Entity/SocialRun.php:18`) garantissent qualité des données.
- **Expérience bureau maîtrisée** : tableaux responsive, info-box état vide, boutons contextuels (`templates/news/index.html.twig:12`, `templates/social_run/index.html.twig:12`).
- **Gestion d’avatars robuste** : re-encodage JPEG, rotation EXIF, suppression ancienne image (`src/Service/AvatarManager.php:15`). Excellent exemple pour la section « sécurité front ».
- **Workflow d’adhésion complet** : DTO, hash de vérification mail (`src/Controller/MembershipController.php:60`). À valoriser en back-end.
- **Exports sécurisés** : `ZipTreeExporter` évite cycles, limite profondeur (`src/Service/ZipTreeExporter.php:17`).
- **Interop Turbo/Stimulus** : gestion fine des statuts HTTP 422 (`assets/controllers/ajax_form_controller.js:21`, `src/Controller/NewsController.php:42`).
- **Thème CSS cohérent** : base Tailwind personnalisée (`assets/styles/app.css:1`) illustrant la composante front.

---

## 5. Non-conformités et risques

### 5.1 Sécurité / conformité

| Gravité | Description | Localisation | Impact |
| --- | --- | --- | --- |
| Critique | Routes JSON d’administration sans vérification CSRF | `src/Controller/SecretaryController.php:27` et `:47` | Un attaquant déjà logué peut forger une requête pour valider/rejeter des adhésions. |
| Critique | Formulaire création/suppression répertoire fichiers sans contrôle CSRF | `src/Controller/FileController.php:139` et `:180` | Suppression ou création arbitraire via requêtes forgées. |
| Élevée | Route `SocialRunController::index` protégée par `#[IsGranted('git ')]` | `src/Controller/SocialRunController.php:19` | Interdit l’accès au module (même aux rôles autorisés) → non-fonctionnel. |
| Élevée | `FileController::createDirectory` ne nettoie pas le nom soumis | `src/Controller/FileController.php:192` | Introduit des caractères indésirables (pas de `Sanitizer`). |
| Élevée | `ZipTreeExporter` n’utilise pas le paramètre `files_manager_dir` | `src/Service/ZipTreeExporter.php:44` | Fonctions download dossier cassées si paramètre n’est plus `var/storage/docs`. |
| Moyenne | Bulk upload : limite 1 Go sans filtre MIME | `src/Form/FileUploadType.php:18` | Risque d’abus disque / upload script. Ajuster à la politique OVH. |
| Moyenne | Flash JS supprime les messages après 500 ms | `assets/js/flash.js:24` | Premier retour utilisateur quasi invisible. |
| Basse | `MembershipController` contient `dump($e)` en production | `src/Controller/MembershipController.php:81` | Divulgue potentiellement des secrets en log. |

### 5.2 Bugs fonctionnels

| Gravité | Description | Localisation | Effet utilisateur |
| --- | --- | --- | --- |
| Critique | Typo `ValidateNameENum` (N majuscule) | `src/Util/Sanitizer.php:25` | 500 lors d’un renommage de fichier/dossier. |
| Élevée | Formulaire profil n’emploie pas `property_path` | `src/Form/MemberType.php:40` et `:43` | Impossible de sauvegarder prénom/nom (symfony mappe vers `firstname`/`lastname`). |
| Moyenne | Gabarit board comporte `#}` résiduel | `templates/board/index.html.twig:17` | Rendu HTML corrompu, effet visuel lors capture dossier. |
| Moyenne | Bouton suppression avatar sans CSRF | `templates/member/index.html.twig:82` + `src/Controller/MemberController.php:104` | Suppression possible via requêtes forgées / robot. |
| Basse | `FileController::list` duplique `getChildren()` | `src/Controller/FileController.php:101` | Lecture inutile, impact minime. |

---

## 6. Recommandations prioritaires

1. **Sécuriser les actions sensibles**
   - Ajouter `if (!$this->isCsrfTokenValid(...))` dans `SecretaryController::validate/reject` (`src/Controller/SecretaryController.php:27` et `:47`).
   - Vérifier `_token` dans `FileController::createDirectory/delete` (`src/Controller/FileController.php:139`, `:185`).
   - Ajouter champ caché `_token` dans le formulaire suppression avatar + contrôle côté serveur (`templates/member/index.html.twig:82`, `src/Controller/MemberController.php:112`).

2. **Réparer les bugs bloquants**
   - Corriger typo vers `ValidateNameEnum` (`src/Util/Sanitizer.php:25`).
   - Fixer l’attribut `#[IsGranted('ROLE_VIE_ASSO')]` attendu (`src/Controller/SocialRunController.php:19`).
   - Mapper `firstName`/`lastName` via `'property_path' => 'firstName'` et `'property_path' => 'lastName'` (`src/Form/MemberType.php:40`, `:43`).

3. **Fiabiliser le module fichiers**
   - Utiliser `Sanitizer::validateName`/`sanitizeName` dans `createDirectory` (`src/Controller/FileController.php:192`).
   - Référencer `files_manager_dir` (paramètre) dans `ZipTreeExporter::absoluteStoragePath` (`src/Service/ZipTreeExporter.php:44`).

4. **Nettoyage visuel et UX**
   - Purger la séquence `#}` dans `templates/board/index.html.twig:17`.
   - Porter la durée d’affichage des flashs (≥3 s) (`assets/js/flash.js:24`).

5. **Durcissement uploads**
   - Ajuster `FileUploadType` : taille max réaliste (50 Mo ?) + whitelist MIME (`src/Form/FileUploadType.php:18`).

---

## 7. Pistes d’amélioration (phase itérative)

- **Tests automatisés** : couvrir parcours adhésion + news via PHPUnit/BrowserKit (`tests/bootstrap.php`).
- **Notifications** : informer par email lors validation/rejet adhésion (`src/Controller/SecretaryController.php:27`).
- **Logs audit** : enregistrer actions sensibles (upload/suppression) via Monolog (`config/packages/monolog.yaml`).
- **Charte sécurité** : ajouter headers (CSP, HSTS) dans Nginx – à documenter en annexe C1 du dossier (`docs/dossier_projet.md:320`).
- **Accessibilité** : compléter attributs ARIA sur boutons modals/hamburger (`templates/partials/_navbar.html.twig:4`).

---

## 8. Intégration au dossier professionnel

1. **Réalisations back-end** : mettre en avant les correctifs (CSRF, roles) comme éléments « après revue ».
2. **Sécurité applicative** : détailler contrôle tokens, gestion uploads (AvatarManager), throttle de connexion (`config/packages/security.yaml:4`).
3. **Jeu d’essai** : compléter le tableau (`docs/dossier_projet.md:240`) avec cas de régression après chaque bug corrigé (renommage fichier, validation CSRF, accès sorties).
4. **Veille** : ajouter note sur faille CSRF (+ lien OWASP) dans tableau veille (`docs/dossier_projet.md:260`).
5. **Annexes** : exporter snippets corrigés (avant/après) pour annexes B1/B2.

---

## 9. Prochaines étapes proposées

1. Appliquer les correctifs critiques (CSRF, roles, typo Enum) puis rejouer les parcours fonctionnels.
2. Lancer `php bin/phpunit` pour documenter la réussite tests dans le dossier.
3. Mettre à jour `docs/dossier_projet.md` avec les enseignements de cet audit et préparer les captures pour la présentation orale.

---

*Fin du rapport.*
