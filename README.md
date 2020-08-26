[RUSSIAN VERSION](https://github.com/xryst/zorge/blob/master/README_RU.md)

# Micro Framework with my own hands

## Components

I have compiled Micro Framework from the following components:

* For **Routing**: [FastRoute](https://github.com/nikic/FastRoute)
* For working with **HTTP**: [symfony/http-foundation](https://github.com/symfony/http-foundation)
* For **Dependency Injection**: [PHP-DI](https://github.com/PHP-DI/PHP-DI)
* For **Template Engine**: [Twig](https://github.com/twigphp/Twig)
* For work with **Databases**: [PDO](https://www.php.net/manual/en/book.pdo.php)

## What to do after cloning a repository?

1. Run `composer update`
2. Create in folder **config** file _config.php_ using _config.example.php_
3. Create, in core, folder **cache** (`chmod 744 cache`)

![](https://miro.medium.com/max/700/1*JwJspWRLr_jPv9yvyBd1lA.png)

## Framework Structure

* **cache**
* **config**
  * config.php - _Basic settings to hide_
  * di.config.php - _Dependency Injection settings_
* **public**
  * index.php
* **src**
  * Controller
    * MainController.php -_Parrent class for Controller's_
  * Core
    * Core.php - _Parrent class for Controller's and Model's_
  * Model
    * MainModel.php - _Parrent class for Model's_
* **Utilities**
  * Database.php - _Database connection class_
* **View** HTML-шаблоны

## Parrent method's (Controller's and Model's)

* **$this->model({CLASSNAME})** — _referring to models (Controller's and Model's)_
* **$this->db()** — _work with Databases (Controller's and Model's)_
* **$this->session()** — _work with Session-s (Controller's and Model's)_
* **$this->http()** — _work with HTTP Request and HTTP Response (Controller's)_
* **$this->tmp()** — _work with Template Engine (Controller's)_

I hope this Framework will be useful for you in your work or for an interview