# p8_todo_and_co
Improve and upgrade an old Symfony 3.1 project, with tests and code quality/performance monitoring, to Symfony 5.3, Twig 3, and Bootstrap 5.

## About The Project

I made this project to improve my skills in Symfony tests and migration versions, in the context of my PHP/Symfony OpenClassRooms formation.
Your comments and suggestions are welcome.

### Built With

*   🐘️ PHP 8.0.9
*   ⛵ phpMyAdmin 5.0.2
*   🐬  MySQL 5.7.31
*   ✒️Apache 2.4.46
*   ⛕️Git 2.31.1.windows.1
*   🌿 Twig 3<p>&nbsp;</p>
*   🖊️ Draw.io for UML
*   🐬 MySQL Workbench for UML

### Code quality

Codacy : [![Codacy Badge](https://app.codacy.com/project/badge/Grade/7951bb4a96c846899510aa3e43ed8f28)](https://www.codacy.com/gh/Drx85/p8_todo_and_co/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Drx85/p8_todo_and_co&amp;utm_campaign=Badge_Grade)

Code Climate : [![Maintainability](https://api.codeclimate.com/v1/badges/c2caaeba8f11e07df94e/maintainability)](https://codeclimate.com/github/Drx85/p8_todo_and_co/maintainability)

### Code coverage (tests)

Application tested with PHPUnit - Codacy : [![Codacy Badge](https://app.codacy.com/project/badge/Coverage/7951bb4a96c846899510aa3e43ed8f28)](https://www.codacy.com/gh/Drx85/p8_todo_and_co/dashboard?utm_source=github.com&utm_medium=referral&utm_content=Drx85/p8_todo_and_co&utm_campaign=Badge_Coverage)

## Getting Started

To get a copy up and running follow these simple steps.

### PREREQUISITES

### Server

*   PHP > 8.0.9
*   SMTP server with mailing service or XAMPP/WAMP for local use (mails will not work in local)
*   MySQL DMBS like phpMyAdmin : https://docs.phpmyadmin.net/fr/latest/setup.html

### Framework and libraries

*   Symfony > 5.3.7
*   Libraries will be installed using Composer
*   CSS/JS libraries are directly called via CDN (Bootstrap 5.1.1, Font Awesome 5, Select 2)

### INSTALLATION

### Clone / Download

1.  Git clone the repository from this page. **See** [GitHub Documentation](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/cloning-a-repository-from-github/cloning-a-repository)

### Config

1.  Open ***.env.example*** file, replace Database field with your own information, and rename it ***.env***
2.  If you are missing any information, please ask you webhost for SMTP and Database credentials

### Install all dependencies
1.  Install Composer if you don't have it yet. **See** [Composer Documentation](https://getcomposer.org/download/)
2.  In your CMD, move on your project directory using cd command :
```sh
cd your/directory
```

3.  Run :
```sh
composer install
```
All dependencies should be installed in a vendor directory.

### Database

1.  Create new Database in your favorite MySQL DMBS running
```sh
php bin/console doctrine:database:create
```

2.  Import database tables running
```sh
php bin/console doctrine:migrations:migrate
```

3.  (Optional) If you want an admin account, you can open src\DataFixtures\UserFixtures.php, go bellow comment, and then set the username + email + password you want. Otherwise, an admin account will be created with theses default values : username : admin, email: your-email@gmail.com, password : demo
4.  Import fixtures running
```sh
php bin/console doctrine:fixtures:load
```

### Server (local only)

1.  To start the server, run
```sh
symfony s:start
```

## Usage

### Online example version

Please see an hosted example version here : *Waiting end of project to share link*

## Contact

Cédric Deperne - [cedric@deperne.fr](mailto:cedric@deperne.fr)

[Project Link](https://github.com/Drx85/p8_todo_and_co)
