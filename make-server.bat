git clone git@github.com:pmmp/PocketMine-MP.git -b master
cd PocketMine-MP
git submodule update --init
..\..\..\bin\php\php.exe ..\..\bin\composer.phar make-server
pause
