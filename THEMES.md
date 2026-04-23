# Guide de Création de Thèmes VOLUND

## Créer votre propre thème

VOLUND détecte automatiquement tous les fichiers CSS présents dans le dossier `themes/`. Pour créer un nouveau thème, il suffit de créer un fichier CSS dans ce dossier.

### Étapes

1. **Créer un fichier CSS** dans `/var/www/volund/themes/`
   ```bash
   sudo nano /var/www/volund/themes/montheme.css
   ```

2. **Définir les variables de couleur**
   ```css
   /* Mon Thème Personnalisé */
   
   :root {
       --primary: #votre-couleur;
       --secondary: #votre-couleur;
       --accent: #votre-couleur;
       --danger: #votre-couleur;
       --success: #votre-couleur;
       --bg-main: #votre-couleur;
       --bg-panel: #votre-couleur;
       --bg-card: #votre-couleur;
       --text-primary: #votre-couleur;
       --text-secondary: #votre-couleur;
   }
   ```

3. **Personnaliser les éléments** (optionnel)
   ```css
   body {
       background: linear-gradient(135deg, #color1 0%, #color2 100%);
   }
   
   .panel {
       box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
   }
   ```

4. **Recharger la page VOLUND**
   - Le thème apparaîtra automatiquement dans le sélecteur !

### Variables CSS obligatoires

Ces variables doivent être définies pour que le thème fonctionne correctement :

| Variable | Description | Exemple |
|----------|-------------|---------|
| `--primary` | Couleur principale (bordures, titres) | `#ff0040` |
| `--secondary` | Couleur secondaire (accents) | `#fbc02d` |
| `--accent` | Couleur d'accentuation | `#c2185b` |
| `--danger` | Couleur pour les actions dangereuses | `#d32f2f` |
| `--success` | Couleur pour les succès | `#388e3c` |
| `--bg-main` | Fond principal de la page | `#0a0000` |
| `--bg-panel` | Fond des panneaux | `#1a0505` |
| `--bg-card` | Fond des cartes/éléments | `#200a0a` |
| `--text-primary` | Couleur du texte principal | `#f5f5f5` |
| `--text-secondary` | Couleur du texte secondaire | `#bdbdbd` |

### Exemple : Thème Vert Matrix

```css
/* Thème Matrix Vert */

:root {
    --primary: #00ff41;
    --secondary: #39ff14;
    --accent: #00cc33;
    --danger: #ff0033;
    --success: #00ff41;
    --bg-main: #000000;
    --bg-panel: #001100;
    --bg-card: #002200;
    --text-primary: #00ff41;
    --text-secondary: #00aa2b;
}

body {
    background: #000000;
    font-family: 'Courier New', monospace;
}

.panel {
    background: rgba(0, 17, 0, 0.9);
    border: 2px solid var(--primary);
    box-shadow: 0 0 20px rgba(0, 255, 65, 0.5);
}

.code-editor {
    background: #000000;
    color: #00ff41;
    text-shadow: 0 0 5px #00ff41;
}
```

### Exemple : Thème Bleu Océan

```css
/* Thème Océan Bleu */

:root {
    --primary: #00bcd4;
    --secondary: #03a9f4;
    --accent: #0288d1;
    --danger: #f44336;
    --success: #4caf50;
    --bg-main: #001f3f;
    --bg-panel: #003366;
    --bg-card: #004466;
    --text-primary: #e1f5fe;
    --text-secondary: #b3e5fc;
}

body {
    background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #001f3f 100%);
}

.panel {
    background: rgba(0, 51, 102, 0.8);
    backdrop-filter: blur(10px);
}
```

### Tester votre thème

1. Placez votre fichier CSS dans `/var/www/volund/themes/`
2. Rafraîchissez la page VOLUND (Ctrl+F5)
3. Votre thème apparaît automatiquement dans la liste !

### Permissions

N'oubliez pas de définir les bonnes permissions :

```bash
sudo chown www-data:www-data /var/www/volund/themes/montheme.css
sudo chmod 644 /var/www/volund/themes/montheme.css
```

### Partager votre thème

Vous pouvez partager votre fichier CSS avec d'autres utilisateurs VOLUND. Il suffit qu'ils le placent dans leur dossier `themes/` !

### Thèmes inclus par défaut

- **cyberpunk.css** - Rouge cyberpunk avec effets néon
- **light.css** - Thème clair pour le jour
- **dark.css** - Thème sombre style GitHub
- **nordic.css** - Palette Nord scandinave

### Support

Pour créer des effets avancés (animations, gradients, etc.), consultez les thèmes existants dans `/var/www/volund/themes/` pour voir des exemples de code.
