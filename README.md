# Crochey - Project Management Application

## Description
Crochey is a modern web application for project management, featuring a clean and intuitive interface with both light and dark themes. The application is built with a robust frontend/backend architecture.

## Stack Technique / Technical Stack

### Frontend
- Vue.js 3
- Vite
- Vue Router
- Custom CSS with CSS variables
- Responsive design

### Backend
- Symfony (PHP)
- RESTful API
- Standard HTTP methods (GET, POST, PUT, DELETE)

## Features / Fonctionnalités

- Intuitive card-based user interface
- Advanced project filtering and search
- Tag system for better organization
- Light/Dark theme support
- Responsive design for all devices
- Smooth animations and transitions
- Detailed notes and descriptions management
- Modern interface with custom typography (JetBrains Mono, Outfit, etc.)
- Complete RESTful API for frontend/backend communication
- Secure and documented API endpoints

## Installation / Installation

### Prerequisites / Prérequis
- Node.js (for frontend)
- PHP 8.1 or higher
- Composer
- Symfony CLI

### Frontend Setup / Configuration Frontend
```bash
cd frontend
npm install
npm run dev
```

### Backend Setup / Configuration Backend
```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console server:start
```

## Development / Développement

### Frontend Development
```bash
cd frontend
npm run dev
```

### Backend Development
```bash
php bin/console server:start
```

## Building for Production / Construction pour la Production

### Frontend
```bash
cd frontend
npm run build
```

### Backend
```bash
composer install --no-dev --optimize-autoloader
```

## API Documentation / Documentation API

The API documentation is available at `/api/docs` when running the application locally.

## Contributing / Contribution

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License / Licence

This project is licensed under the MIT License - see the LICENSE file for details.

---

# Crochey - Application de Gestion de Projets

## Description
Crochey est une application web moderne de gestion de projets, proposant une interface propre et intuitive avec des thèmes clair et sombre. L'application est construite avec une architecture frontend/backend robuste.

## Stack Technique

### Frontend
- Vue.js 3
- Vite
- Vue Router
- CSS personnalisé avec variables CSS
- Design responsive

### Backend
- Symfony (PHP)
- API RESTful
- Méthodes HTTP standards (GET, POST, PUT, DELETE)

## Fonctionnalités

- Interface utilisateur intuitive basée sur des cartes
- Filtrage et recherche avancée des projets
- Système de tags pour une meilleure organisation
- Support des thèmes clair/sombre
- Design responsive pour tous les appareils
- Animations fluides et transitions élégantes
- Gestion des notes et descriptions détaillées
- Interface moderne avec typographie personnalisée (JetBrains Mono, Outfit, etc.)
- API RESTful complète pour la communication frontend/backend
- Endpoints API sécurisés et documentés

## Installation

### Prérequis
- Node.js (pour le frontend)
- PHP 8.1 ou supérieur
- Composer
- Symfony CLI

### Configuration Frontend
```bash
cd frontend
npm install
npm run dev
```

### Configuration Backend
```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console server:start
```

## Développement

### Développement Frontend
```bash
cd frontend
npm run dev
```

### Développement Backend
```bash
php bin/console server:start
```

## Construction pour la Production

### Frontend
```bash
cd frontend
npm run build
```

### Backend
```bash
composer install --no-dev --optimize-autoloader
```

## Documentation API

La documentation de l'API est disponible à `/api/docs` lors de l'exécution de l'application en local.

## Contribution

1. Fork le repository
2. Créez votre branche de fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT - voir le fichier LICENSE pour plus de détails. 