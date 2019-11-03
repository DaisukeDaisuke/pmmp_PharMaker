# pmmp_PharMaker
Githubダウンロードurlから必要なファイルをインターネットより取得致しまして、Pocketmine-MP.pharを作成致します。

## Usage
```
usage:
　command https://github.com/?????/?????/archive/?????.zip

option:
　[make | m] https://github.com/pmmp/PocketMine-MP/archive/stable.zip よりPocketmine-MP.pharを作成致します。
　[phar | p] 現在の存在する「src」フォルダと「vendor」フォルダよりPocketMine-MP.pharを作成致します。
　[composerinstall | ci] 安全な方法にてcomposerを「bin/composer.phar」にインストールします。
　[composerinstallnv | cinv] composerを検証せずにcomposerを「bin/composer.phar」にインストールします。
```
## Example
### command
```
php maker.php https://github.com/pmmp/PocketMine-MP/archive/stable.zip
```
### console output
```
「composer.phar」を検証せずにダウンロードしております...
Pocketmine-MPをダウンロードしています...(https://github.com/pmmp/PocketMine-MP/archive/stable.zip)
PocketMine-MPを解凍しております...
「bin\composer.phar install」をプログラム内より実行しております...(exec未使用...)
#!/usr/bin/env php
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Package operations: 8 installs, 0 updates, 0 removals
As there is no 'unzip' command installed zip files are being unpacked using the PHP zip extension.
This may cause invalid reports of corrupted archives. Besides, any UNIX permissions (e.g. executable) defined in the archives will be lost.
Installing 'unzip' may remediate them.
  - Installing adhocore/json-comment (v0.0.7): Loading from cache
  - Installing daverandom/callback-validator (dev-master d87a08c): Cloning d87a08cddb
    Failed to download daverandom/callback-validator from source: Failed to clone https://github.com/DaveRandom/CallbackValidator.git, git was not found, check that it is installed and in your PATH env.


    Now trying to download from dist
  - Installing daverandom/callback-validator (dev-master d87a08c): Loading from cache
  - Installing pocketmine/math (0.2.3): Loading from cache
  - Installing pocketmine/binaryutils (0.1.10): Loading from cache
  - Installing pocketmine/nbt (0.2.11): Loading from cache
  - Installing pocketmine/spl (0.3.2): Loading from cache
  - Installing pocketmine/snooze (0.1.1): Loading from cache
  - Installing pocketmine/raklib (0.12.5): Loading from cache
Generating autoload files
Pocketmine-MPの実行に必要なサブモジュールをダウンロード、展開しております...
download... locale
unzip... src/pocketmine/lang/locale
download... vanilla
unzip... src/pocketmine/resources/vanilla
「Pocketmine-MP.phar」を作成しております...
圧縮しています...
終了
cleanup...
```
