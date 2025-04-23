# 📦 BileMo

Bienvenue sur le projet **BileMo** ! Ce projet est développé avec le framework [Symfony](https://symfony.com/) et utilise PHP 8.1+.

## 🚀 Prérequis

Assurez-vous d'avoir les éléments suivants installés sur votre machine :

- PHP 8.1 ou supérieur
- Composer
- Symfony CLI (optionnel mais recommandé)
- MySQL ou PostgreSQL (selon votre base de données)

## 🛠️ Installation

1. **Clonez le dépôt :**

git clone https://github.com/Dorian-Feschaud/BileMo.git
cd bilemo


2. **Installez les dépendances PHP :**

composer install

3. **Copiez le fichier d'exemple `.env` et configurez vos variables d'environnement :**

cp .env .env.local

Modifiez le fichier `.env.local` pour correspondre à votre environnement (notamment la base de données).

Exemple :

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/nom_de_la_bdd"

4. **Créez la base de données et exécutez les migrations :**

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

5. **Lancer les fixtures :**

php bin/console doctrine:fixtures:load

6. **Lancez le serveur de développement :**

symfony serve

Ou via PHP :

php -S localhost:8000 -t public
