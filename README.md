# pmmp_PharMaker
Get the necessary files from the Github download URL from the Internet, and create Pocketmine-MP.phar.

## Usage
```
usage:
　command https://github.com/?????/?????/archive/?????.zip

option:
　[make | m] create "Pocketmine-MP.phar" from https://github.com/pmmp/PocketMine-MP/archive/stable.zip
　[phar | p] Create PocketMine-MP.phar from the currently existing "src" and "vendor" folders.
　[composerinstall | ci] Install composer in "bin/composer.phar" in a safe way.
　[composerinstallnv | cinv] Install composer in "bin/composer.phar" without validating composer.
```
## Example
### Command
```
php maker.php https://github.com/pmmp/PocketMine-MP/archive/stable.zip
```
### Console Output
```
Download "composer.phar" without checking safety
downloading Pocketmine-MP from (https://github.com/pmmp/PocketMine-MP/archive/stable.zip)
unzipping PocketMine-MP...
running "bin\composer.phar install --no-dev --classmap-authoritative" without using exec...
Change the current directory to "/storage/emulated/0/www/public/pmmp_PharMaker-master"
#!/usr/bin/env php
Installing dependencies from lock file
Verifying lock file contents can be installed on current platform.
Package operations: 11 installs, 0 updates, 0 removals
As there is no 'unzip' command installed zip files are being unpacked using the PHP zip extension.
This may cause invalid reports of corrupted archives. Besides, any UNIX permissions (e.g. executable) defined in the archives will be lost.
Installing 'unzip' may remediate them.
  - Installing adhocore/json-comment (0.1.0): Extracting archive
  - Installing pocketmine/callback-validator (1.0.3): Extracting archive
  - Installing pocketmine/classloader (0.1.1): Extracting archive
  - Installing pocketmine/math (0.2.5): Extracting archive
  - Installing pocketmine/binaryutils (0.1.12): Extracting archive
  - Installing pocketmine/nbt (0.2.15): Extracting archive
  - Installing pocketmine/snooze (0.1.3): Extracting archive
  - Installing pocketmine/log (0.2.0): Extracting archive
  - Installing pocketmine/log-pthreads (0.1.1): Extracting archive
  - Installing pocketmine/raklib (0.12.9): Extracting archive
  - Installing pocketmine/spl (0.4.1): Extracting archive
Generating optimized autoload files
Download and unpack the submodules needed to run Pocketmine-MP...
download... locale (https://github.com/pmmp/Language/archive/master.zip)
unzip... src/pocketmine/lang/locale
download... vanilla (https://github.com/pmmp/BedrockData/archive/master.zip)
unzip... src/pocketmine/resources/vanilla
creating... "Pocketmine-MP.phar"
added 1344 files...

cleanup...
```
