# FiasDbf2Xml

Скрипт для конвертирования файлов Федеральной Информационной Адресной Системы (ФИАС) в формате DBF в формат XML.

## Использование

    $ php fiasDbf2Xml.php <путь>

Если в качестве пути указан конкретный файл с расширением DBF, то будет сконвертирован только он. Если указан путь к директории, то будут сконвертированы все файлы с расширением DBF в этой директории.

На каждую таблицу ФИАС создается один файл XML, даже если она была представлена несколькими DBF файлами.

## Требования

Для работы необходимо php-расширение [dbase](http://www.php.net/dbase). Установите его с помощью команды:

    $ pecl install dbase

## Ссылки

+ [ФИАС](http://fias.nalog.ru)
+ [basicdata.ru - ФИАС в формате SQL](http://basicdata.ru)

---
# FiasDbf2Xml

Command line script to convert russian FIAS \*.DBF files to FIAS \*.XML files.

## Usage

    $ php fiasDbf2Xml.php <path>

Specify a DBF file as a _path_ to convert only one file. If _path_ is a directory all nested DBF files will be converted.

Each FIAS table will be located in one XML file, even in case of multiple DBF files.

## Requirements

[Dbase](http://www.php.net/dbase) php extension is required. It can be installed with:

    $ pecl install dbase

## Links

+ [FIAS](http://fias.nalog.ru)
+ [basicdata.ru - FIAS SQL](http://basicdata.ru)
