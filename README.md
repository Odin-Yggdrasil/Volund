# VOLUND

![VOLUND Logo](logo.png)

**Gestionnaire de Preseed Debian avec interface web moderne**

VOLUND est un outil web moderne pour créer, éditer et gérer des fichiers de configuration Preseed Debian. Inspiré de la mythologie nordique et du dieu forgeron Völundr, VOLUND forge vos configurations système avec précision.

## ✨ Fonctionnalités

- 🎨 **Interface Cyberpunk moderne** avec système de thèmes
- 📝 **Éditeur de preseed** intégré avec coloration syntaxique
- ⚡ **Configuration rapide** via formulaires
- 🎨 **Thèmes personnalisables** (détection automatique)
- 📊 **Statistiques** de déploiement
- 💾 **Sauvegarde automatique** des modifications
- 🌐 **API REST** complète
- 🔒 **Sécurisé** avec validation des entrées

## 🎨 Thèmes inclus

- **Cyberpunk 2077** - Rouge néon avec effets futuristes
- **Light** - Thème clair professionnel
- **Dark** - Style GitHub Dark
- **Nordic** - Palette Nord scandinave
- **Volund Mythic** - Mythologie nordique avec runes et or

## 🚀 Installation rapide

### Prérequis

- Debian 11/12 ou Ubuntu 20.04+
- Nginx
- PHP 8.4+ (avec php-fpm)
- 50 Mo d'espace disque

### Installation automatique

```bash
# Télécharger la dernière version
wget https://github.com/votre-username/volund/archive/main.zip
unzip main.zip
cd volund-main

# Lancer l'installation
sudo ./install.sh
```

Le script d'installation va :
1. Installer les dépendances (Nginx, PHP 8.4)
2. Créer les répertoires nécessaires
3. Configurer Nginx
4. Définir les permissions
5. Démarrer les services

## 📖 Utilisation

### Accès à l'interface

Ouvrez votre navigateur : `http://votre-serveur` ou `http://votre-ip`

### Créer un preseed

1. Cliquez sur **"NOUVEAU"**
2. Donnez un nom à votre preseed
3. Éditez la configuration
4. Cliquez sur **"SAUVEGARDER"**

### Configuration rapide

1. Cliquez sur **"Config Rapide"** dans la sidebar
2. Remplissez les formulaires
3. Cliquez sur **"APPLIQUER"**

### Utiliser un preseed

Les preseeds sont accessibles directement via URL :

```bash
# Au boot de l'installateur Debian
auto url=http://votre-serveur/preseed/mon-preseed.cfg
```

## 🎨 Créer un thème personnalisé

VOLUND détecte automatiquement tous les thèmes dans le dossier `themes/`.

Consultez [THEMES.md](THEMES.md) pour le guide complet.

## 🔧 API REST

VOLUND expose une API REST complète :

```
GET    /api/preseeds           - Liste tous les preseeds
POST   /api/preseeds           - Créer un preseed
GET    /api/preseed/{name}     - Récupérer un preseed
PUT    /api/preseed/{name}     - Modifier un preseed
DELETE /api/preseed/{name}     - Supprimer un preseed
GET    /api/stats              - Statistiques
GET    /api/themes             - Liste des thèmes disponibles
```

## 📁 Structure du projet

```
volund/
├── volund.html              # Interface web principale
├── api.php                  # Backend API REST
├── install.sh               # Script d'installation
├── themes/                  # Thèmes CSS
├── preseeds/               # Fichiers preseed
└── README.md               # Ce fichier
```

## 🤝 Contribuer

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT.
