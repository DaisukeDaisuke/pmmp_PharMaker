@echo off
@rem Please download "composer.phar" from "https://getcomposer.org/composer-stable.phar".

@rem git clone --recursive git@github.com:pmmp/PocketMine-MP.git
@rem cd PocketMine-MP
@rem .\bin\php\php.exe .\bin\composer.phar make-server
@rem pause

if exist bin\php\php.exe (
	set PHPRC=""
	set PHP_BINARY=bin\php\php.exe
) else (
	set PHP_BINARY=php
)

if exist .\bin\composer.phar (
  git clone --recursive git@github.com:pmmp/PocketMine-MP.git
  cd PocketMine-MP
  ..\%PHP_BINARY% ..\bin\composer.phar make-server
  pause
) else (
  echo Please download ".\bin\composer.phar" from "https://getcomposer.org/composer-stable.phar".
  pause
)

