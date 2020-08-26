[ENGLISH VERSION](https://github.com/xryst/zorge/blob/master/README.md)

# Micro Framework своими руками

## Компоненты

Я собрал Micro Framework из следующих компонентов:
* Для **Routing**: [**FastRoute**](https://github.com/nikic/FastRoute)
* Для работы с **HTTP**: [**symfony/http-foundation**](https://github.com/symfony/http-foundation)
* Для **Dependency Injection**: [**PHP-DI**](https://github.com/PHP-DI/PHP-DI)
* В качество **Template Engine**: [**Twig**](https://github.com/twigphp/Twig)
* Для работы с **Базами Данных**: [**PDO**](https://www.php.net/manual/en/book.pdo.php)

## Что делать после клонирования репозитория?

1. Запустить `composer update`
2. Создать в папке **config** создать файл _config.php- взяв за основу _config.example.php_
3. Создать в корне папку **cache** (`chmod 744 cache`)

![](https://miro.medium.com/max/700/1*JwJspWRLr_jPv9yvyBd1lA.png)

## Структура Framework-а

* **cache**
* **config**
  * config.php - _Основные настрйки, которые нужно скрывать_
  * di.config.php - _Настройки Dependency Injection_
* **public**
  * index.php
* **src**
  * Controller
    * MainController.php -_Родительский класс для всех Controller-ов_
  * Core
    * Core.php - _Родительский класс для всех Controller-ов и Model-ей_
  * Model
    * MainModel.php - _Родительский класс для всех Model-ей_
* **Utilities**
  * Database.php - _Класс подключения к Базе Данных_
* **View** HTML-шаблоны

## Родительские методы (Controller-ы и Model-и)
* **$this->model({CLASSNAME})** — _обращение к моделям (Controller-ы и Model-и)_
* **$this->db()** — _Работа с Базой Данных (Controller-ы и Model-и)_
* **$this->session()** — _Работа с Session-ами (Controller-ы и Model-и)_
* **$this->http()** — _Работа с HTTP Request и HTTP Response (Controller-ы)_
* **$this->tmp()** — _Работа с Template Engine (Controller-ы)_

Надеюсь данный Framework пригодится Вам в работе или для прохождения собеседованияq