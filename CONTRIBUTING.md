# Guide de Contribution à VOLUND

Merci de votre intérêt pour contribuer à VOLUND ! Ce document vous guidera pour contribuer efficacement au projet.

## Comment contribuer

### Signaler un bug

1. Vérifiez que le bug n'a pas déjà été signalé dans les [Issues](https://github.com/votre-username/volund/issues)
2. Créez une nouvelle issue avec :
   - Un titre clair et descriptif
   - Une description détaillée du problème
   - Les étapes pour reproduire
   - Le comportement attendu vs. le comportement actuel
   - Votre environnement (OS, version PHP, version Nginx)
   - Screenshots si pertinent

### Proposer une nouvelle fonctionnalité

1. Créez une issue avec le tag `enhancement`
2. Décrivez clairement la fonctionnalité
3. Expliquez pourquoi elle serait utile
4. Proposez une implémentation si possible

### Soumettre une Pull Request

1. **Fork** le projet
2. **Créez une branche** depuis `main` :
   ```bash
   git checkout -b feature/ma-fonctionnalite
   ```
3. **Faites vos modifications**
4. **Testez** vos changements
5. **Commit** avec des messages clairs :
   ```bash
   git commit -m "feat: ajouter support pour X"
   ```
6. **Push** vers votre fork :
   ```bash
   git push origin feature/ma-fonctionnalite
   ```
7. **Ouvrez une Pull Request**

## Guidelines de code

### Style PHP

- Utilisez PHP 8.4+ avec types stricts : `declare(strict_types=1);`
- Suivez PSR-12 pour le style de code
- Utilisez des types de retour explicites
- Commentez le code complexe

### Style HTML/CSS

- Indentation : 4 espaces
- Noms de classes en `kebab-case`
- CSS organisé par sections
- Variables CSS pour les couleurs

### Style JavaScript

- Utilisez `const` et `let`, jamais `var`
- Fonctions async/await pour les appels API
- Commentez les fonctions complexes
- Nommage en `camelCase`

## Créer un nouveau thème

Les thèmes sont une excellente façon de contribuer !

1. Créez un fichier CSS dans `themes/`
2. Nommez-le descriptivem ent : `mon-theme.css`
3. Définissez toutes les variables CSS requises
4. Testez avec différents éléments de l'interface
5. Ajoutez des captures d'écran
6. Documentez votre thème dans la PR

Consultez [THEMES.md](THEMES.md) pour plus de détails.

## Structure des commits

Utilisez le format Conventional Commits :

- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation
- `style:` Changements de style (CSS)
- `refactor:` Refactoring de code
- `test:` Ajout de tests
- `chore:` Tâches de maintenance

Exemples :
```
feat: ajouter thème Matrix vert
fix: corriger le bug de sauvegarde des preseeds
docs: améliorer le guide d'installation
style: créer le thème océan bleu
```

## Tests

Avant de soumettre votre PR :

1. Testez l'installation complète avec `install.sh`
2. Vérifiez que l'interface fonctionne
3. Testez la création/édition/suppression de preseeds
4. Vérifiez que votre thème s'affiche correctement
5. Testez sur différents navigateurs si possible

## Code de conduite

- Soyez respectueux et bienveillant
- Acceptez les critiques constructives
- Concentrez-vous sur ce qui est mieux pour la communauté
- Aidez les nouveaux contributeurs

## Questions

Si vous avez des questions, n'hésitez pas à :
- Ouvrir une issue avec le tag `question`
- Rejoindre les discussions GitHub

## Licence

En contribuant à VOLUND, vous acceptez que vos contributions soient sous licence MIT.

---

**Merci de contribuer à VOLUND ! ⚒️**
