csvexcel
========

> CSV⟷Excel Converter

![csvexcel Screencast](https://raw.githubusercontent.com/florianeckerstorfer/csvexcel/master/docs/csvexcel.gif)

Developed by [Florian Eckerstorfer](https://florian.ec) in Vienna, Europe.


Installation
------------

You can install `csvexcel` using Composer or by downloading the PHAR.

### Variant 1: Composer

You can install `csvexcel` using [Composer](https://getcomposer.org):

```shell
composer global require florianeckerstorfer/csvexcel:@stable
```

*Please make sure that the Composer bin directory (`~/.composer/vendor/bin`) is in your path.*

### Variant 2: Download PHAR

Alternatively you can download [`csvexcel.phar`](https://github.com/florianeckerstorfer/csvexcel/releases/download/v0.1/csvexcel.phar) and put in some directory in your `$PATH`. I recommend removing the `.phar` extension.

```shell
mv ~/Downloads/csvexcel.phar /usr/local/bin/csvexcel
```


Usage
-----

Convert a CSV file into Excel; the output file will be named `countries.xlsx`.

```shell
$ csvexcel countries.csv
```

Convert a Excel file into CSV; the output file will be named `countries.csv`.

```shell
$ csvexcel countries.xlsx
```

You can also define the output file.

```shell
$ csvexcel countries.csv ~/my-favourite-countries.xlsx
```


Changelog
----------

### Version 0.1 (21 April 2015)

- Initial release


License
-------

The MIT license applies to florianeckerstorfer/csvexcel. For the full copyright and license information, please view
the LICENSE file distributed with this source code.
