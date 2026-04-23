#!/bin/bash

###############################################################################
# Script d'installation automatique de VOLUND
# Gestionnaire de fichiers Preseed Debian
# Pour Debian/Ubuntu avec Nginx et PHP 8.4
###############################################################################

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variables
INSTALL_DIR="/var/www/volund"
NGINX_SITE="volund"

echo -e "${CYAN}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                   VOLUND INSTALLER                           ║"
echo "║         Gestionnaire de Preseed Debian                       ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Vérifier si le script est exécuté en tant que root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Ce script doit être exécuté en tant que root (sudo)${NC}"
   exit 1
fi

echo -e "${GREEN}✓ Vérification des privilèges root${NC}"

# Détecter l'adresse IP du serveur
SERVER_IP=$(hostname -I | awk '{print $1}')
echo -e "${CYAN}🌐 Adresse IP détectée : ${YELLOW}$SERVER_IP${NC}"
echo ""
echo -e "${YELLOW}L'interface sera accessible via : http://$SERVER_IP${NC}"
echo ""
read -p "Voulez-vous utiliser un nom de domaine personnalisé ? (y/N): " USE_DOMAIN

if [[ "$USE_DOMAIN" =~ ^[Yy]$ ]]; then
    read -p "Entrez le nom de domaine: " DOMAIN
else
    DOMAIN="_"  # Nginx utilisera n'importe quel nom/IP
fi

echo -e "${YELLOW}📦 Installation des dépendances...${NC}"

# Mettre à jour les paquets
apt update

# Vérifier si PHP 8.4 est disponible
if ! apt-cache show php8.4-fpm > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  PHP 8.4 n'est pas disponible dans les dépôts par défaut${NC}"
    echo -e "${YELLOW}📥 Ajout du dépôt Ondřej Surý pour PHP 8.4...${NC}"
    
    apt install -y software-properties-common
    add-apt-repository -y ppa:ondrej/php
    apt update
fi

# Installer les paquets nécessaires
echo -e "${YELLOW}📦 Installation de Nginx et PHP 8.4...${NC}"
apt install -y nginx php8.4-fpm php8.4-cli php8.4-mbstring php8.4-common

echo -e "${GREEN}✓ Dépendances installées${NC}"

# Créer le répertoire d'installation
echo -e "${YELLOW}📁 Création du répertoire d'installation...${NC}"
mkdir -p $INSTALL_DIR
mkdir -p $INSTALL_DIR/preseeds

# Copier les fichiers (on suppose qu'ils sont dans le répertoire courant)
if [ -f "volund.html" ]; then
    cp volund.html $INSTALL_DIR/
    echo -e "${GREEN}✓ volund.html copié${NC}"
else
    echo -e "${RED}❌ Fichier volund.html non trouvé${NC}"
fi

if [ -f "api.php" ]; then
    cp api.php $INSTALL_DIR/
    echo -e "${GREEN}✓ api.php copié${NC}"
else
    echo -e "${RED}❌ Fichier api.php non trouvé${NC}"
fi

if [ -f "server-prod.cfg" ]; then
    cp server-prod.cfg $INSTALL_DIR/preseeds/
    echo -e "${GREEN}✓ Exemple server-prod.cfg copié${NC}"
fi

# Copier le dossier themes
if [ -d "themes" ]; then
    cp -r themes $INSTALL_DIR/
    echo -e "${GREEN}✓ Dossier themes copié${NC}"
else
    echo -e "${RED}❌ Dossier themes non trouvé${NC}"
fi

# Copier les logos
if [ -f "logo.png" ]; then
    cp logo.png $INSTALL_DIR/
    echo -e "${GREEN}✓ Logo PNG copié${NC}"
fi

if [ -f "logo.svg" ]; then
    cp logo.svg $INSTALL_DIR/
    echo -e "${GREEN}✓ Logo SVG copié${NC}"
fi

# Définir les permissions
echo -e "${YELLOW}🔐 Configuration des permissions...${NC}"
chown -R www-data:www-data $INSTALL_DIR
chmod -R 755 $INSTALL_DIR
chmod 755 $INSTALL_DIR/preseeds

echo -e "${GREEN}✓ Permissions configurées${NC}"

# Créer la configuration Nginx
echo -e "${YELLOW}⚙️  Configuration de Nginx...${NC}"

cat > /etc/nginx/sites-available/$NGINX_SITE << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    
    root $INSTALL_DIR;
    index volund.html;
    
    access_log /var/log/nginx/volund-access.log;
    error_log /var/log/nginx/volund-error.log;
    
    charset utf-8;
    client_max_body_size 10M;
    
    # PHP-FPM
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Routes API
    location ^~ /api/ {
        try_files \$uri \$uri/ /api.php?\$query_string;
    }
    
    # Routes Preseed
    location ^~ /preseed/ {
        try_files \$uri /api.php?\$query_string;
    }
    
    # Protection
    location ^~ /preseeds/ {
        deny all;
        return 403;
    }
    
    location ~ /volund_config\\.json\$ {
        deny all;
    }
    
    # Fichiers statiques
    location ~* \\.(css|js|jpg|jpeg|png|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public";
    }
    
    # Page principale
    location / {
        try_files \$uri \$uri/ /volund.html;
    }
    
    # Headers de sécurité
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
EOF

# Activer le site
ln -sf /etc/nginx/sites-available/$NGINX_SITE /etc/nginx/sites-enabled/

# Désactiver le site par défaut s'il existe
if [ -f /etc/nginx/sites-enabled/default ]; then
    rm -f /etc/nginx/sites-enabled/default
    echo -e "${GREEN}✓ Site par défaut désactivé${NC}"
fi

# Tester la configuration Nginx
echo -e "${YELLOW}🔍 Test de la configuration Nginx...${NC}"
if nginx -t; then
    echo -e "${GREEN}✓ Configuration Nginx valide${NC}"
else
    echo -e "${RED}❌ Erreur dans la configuration Nginx${NC}"
    exit 1
fi

# Redémarrer les services
echo -e "${YELLOW}🔄 Redémarrage des services...${NC}"
systemctl restart php8.4-fpm
systemctl restart nginx

# Activer les services au démarrage
systemctl enable nginx
systemctl enable php8.4-fpm

echo -e "${GREEN}✓ Services redémarrés${NC}"

# Afficher les informations finales
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║              ✅ INSTALLATION TERMINÉE !                      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${CYAN}📍 Accès à l'interface :${NC}"
echo -e "   ${YELLOW}http://$SERVER_IP${NC}"
if [ "$DOMAIN" != "_" ]; then
    echo -e "   ${YELLOW}http://$DOMAIN${NC}"
fi
echo ""
echo -e "${CYAN}📂 Répertoire d'installation :${NC}"
echo -e "   ${YELLOW}$INSTALL_DIR${NC}"
echo ""
echo -e "${CYAN}📝 Logs :${NC}"
echo -e "   ${YELLOW}/var/log/nginx/volund-access.log${NC}"
echo -e "   ${YELLOW}/var/log/nginx/volund-error.log${NC}"
echo ""
echo -e "${CYAN}🔗 URLs API :${NC}"
echo -e "   ${YELLOW}http://$SERVER_IP/api/stats${NC}"
echo -e "   ${YELLOW}http://$SERVER_IP/api/preseeds${NC}"
echo ""
echo -e "${CYAN}🚀 Pour utiliser un preseed :${NC}"
echo -e "   ${YELLOW}http://$SERVER_IP/preseed/nom-du-fichier.cfg${NC}"
echo ""
echo -e "${CYAN}🔒 Pour activer HTTPS avec Let's Encrypt :${NC}"
echo -e "   ${YELLOW}sudo apt install certbot python3-certbot-nginx${NC}"
if [ "$DOMAIN" != "_" ]; then
    echo -e "   ${YELLOW}sudo certbot --nginx -d $DOMAIN${NC}"
else
    echo -e "   ${YELLOW}sudo certbot --nginx -d votre-domaine.com${NC}"
fi
echo ""
echo -e "${GREEN}Merci d'avoir installé VOLUND ! 🎉${NC}"
echo ""
