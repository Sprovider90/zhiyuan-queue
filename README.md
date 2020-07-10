<h1 align="center"> Package Builder </h1>

<p align="center"> :package: A composer package builder.</p>


# Installation


```shell
修改全局composer下的config.json
{
    "config":{
        "secure-http":false,
        "disable-tls": true
        },
    "repositories": {
        "packagist": {
            "type": "composer",
            "url": "https://mirrors.aliyun.com/composer/"
        }
    }
}
$ composer global config repositories.gxsprobuilder git http://gitlab.dexunzhenggu.cn/composer/gxsprobuilder.git
$ composer global require 'Sprovider90/package-builder':dev-master --prefer-source
```

# Usage

```shell
 $ gxs-package-builder help
```

## Create a composer package:
Make sure you have `~/.composer/vendor/bin/` in your path.

```
gxs-package-builder build [target directory] [pro type]
```
example:

```shell
$ gxs-package-builder build ./

# Please enter the name of the package (example: foo/bar): vendor/product
# Please enter the namespace of the package [Vendor\Product]:
# Do you want to test this package ?[Y/n]:
# Do you want to use php-cs-fixer format your code ? [Y/n]:
# Please enter the standard of php-cs-fixer [symfony] ?
# Package vendor/product created in: ./
```
The follow package will be created by your input :gxs-package-builder build [target directory] lib

```
vendor-product
├── .editorconfig
├── .gitattributes
├── .gitignore
├── .php_cs
├── README.md
├── composer.json
├── phpunit.xml.dist
├── src
│   └── .gitkeep
└── tests
    └── .gitkeep
```
The follow package will be created by your input :gxs-package-builder build [target directory] 

```
vendor-product
├── .editorconfig
├── .gitattributes
├── .gitignore
├── .php_cs
├── README.md
├── composer.json
├── phpunit.xml.dist
├── src
│   └── .gitkeep
└── tests
    └── .gitkeep
```
## Update Package Builder

# Contributing

# License

MIT
