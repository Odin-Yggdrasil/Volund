# VOLUND v1.0.0 - Release Notes

## 🎉 Première release officielle de VOLUND

VOLUND est un gestionnaire de Preseed Debian moderne avec interface web, inspiré de la mythologie nordique.

## ✨ Fonctionnalités principales

### Interface utilisateur
- Interface web moderne et responsive
- Éditeur de code intégré avec coloration bleue
- Zone d'édition adaptative (90vh)
- Logo VOLUND (forge avec flammes)
- Favicon intégré

### Gestion des Preseeds
- Créer, éditer, supprimer des preseeds
- Sauvegarde automatique
- Accès direct via URL (`/preseed/fichier.cfg`)
- Exemple de preseed inclus (`server-prod.cfg`)

### Configuration rapide
- Formulaires pour configuration facile :
  - Localisation (langue, clavier)
  - Réseau (hostname, domaine)
  - Utilisateur
  - Partitionnement (regular, LVM, chiffré)
  - Paquets
- Génération automatique du preseed

### Système de thèmes
- **Détection automatique** des thèmes CSS
- 5 thèmes inclus :
  - **Cyberpunk 2077** - Rouge néon, formes carrées, effets futuristes
  - **Light** - Thème clair professionnel
  - **Dark** - Style GitHub Dark
  - **Nordic** - Palette Nord scandinave
  - **Volund Mythic** - Mythologie nordique avec runes, or et forge
- Guide complet pour créer des thèmes personnalisés

### API REST
- `GET /api/preseeds` - Lister les preseeds
- `POST /api/preseeds` - Créer un preseed
- `GET /api/preseed/{name}` - Récupérer un preseed
- `PUT /api/preseed/{name}` - Modifier un preseed
- `DELETE /api/preseed/{name}` - Supprimer un preseed
- `GET /api/stats` - Statistiques
- `GET /api/themes` - Liste des thèmes disponibles

### Installation
- Script d'installation automatique (`install.sh`)
- Support Debian 11/12 et Ubuntu 20.04+
- Configuration Nginx optimisée
- PHP 8.4+ avec types stricts

## 📦 Fichiers inclus

- `volund.html` - Interface web
- `api.php` - Backend API
- `install.sh` - Script d'installation
- `volund-nginx.conf` - Configuration Nginx
- `logo.png` / `logo.svg` - Logos
- `themes/` - 5 thèmes CSS
- `server-prod.cfg` - Exemple de preseed
- Documentation complète

## 🎨 Thème Cyberpunk 2077

Le thème par défaut offre :
- Rouge adouci (#d32f2f) au lieu de rouge pétant
- Formes carrées partout (border-radius: 0)
- Grid animée en arrière-plan
- Scanlines subtiles
- Effets néon sur les interactions
- Glitch sur le titre

## 🎨 Thème Volund Mythic

Le thème mythologique nordique inclut :
- Runes qui défilent en arrière-plan (ᚠᚢᚦᚨᚱᚲ)
- Couleur or de Völundr (#d4af37)
- Effet de métal forgé
- Flammes de forge subtiles
- Gravures runiques
- Ambiance mystique nordique

## 🔧 Améliorations techniques

- Pas d'emojis dans l'interface (interface clean)
- Zone d'édition maximale (90vh)
- Texte du code en bleu (#64b5f6)
- Sidebar optimisée (260px)
- Détection automatique des thèmes
- LocalStorage pour préférences utilisateur

## 📚 Documentation

- README.md complet
- THEMES.md - Guide de création de thèmes
- CONTRIBUTING.md - Guide de contribution
- Commentaires dans le code

## 🚀 Installation

```bash
wget https://github.com/votre-username/volund/archive/v1.0.0.tar.gz
tar -xzf v1.0.0.tar.gz
cd volund-1.0.0
sudo ./install.sh
```

## 🐛 Bugs connus

Aucun bug connu pour le moment. Signalez les problèmes sur GitHub Issues.

## 🔮 Prochaines versions

Idées pour les futures versions :
- Support de templates de preseed
- Import/export de configurations
- Validation syntaxique des preseeds
- Mode sombre automatique selon l'heure
- Plus de thèmes communautaires

## 🙏 Remerciements

- Inspiré par Völundr (Wayland le Forgeron)
- Communauté Debian
- Contributeurs de thèmes

---

**VOLUND v1.0.0 - Forgé avec ⚒️ comme Völundr**
