<?php
class maker{
	public $canReceiveShutdown = false;//

	public function run(String $pocketmine_mp_zip_url){
		if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar")){
			echo "「composer.phar」を検証せずにダウンロードしております...";
			echo PHP_EOL;
			$this->InstallComposerWithoutConfirmation();
		}
		if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . "src")){
			if(!self::isSafetyGithubURL($pocketmine_mp_zip_url)){
				echo "指定致しましたurlは不正にてございます。";
				return;
			}
			echo "Pocketmine-MPをダウンロードしています...(".$pocketmine_mp_zip_url.")";
			echo PHP_EOL;
			$this->downloadFile($pocketmine_mp_zip_url,dirname(__FILE__) . DIRECTORY_SEPARATOR . "PocketMine-MP.zip");
			echo "PocketMine-MPを解凍しております...";
			echo PHP_EOL;
			$this->pocketmine_mp_unzip();
		}
		if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . "vendor")){
			echo "「bin\composer.phar install」をプログラム内より実行しております...(exec未使用...)";
			echo PHP_EOL;
			$this->ComposerRun();
		}else{
			$this->run1();
		}
	}

	public function run1(){
		if(file_exists(".gitmodules")){
			echo "Pocketmine-MPの実行に必要なサブモジュールをダウンロード、展開しております...";
			echo PHP_EOL;
			$this->submodule_add();
		}
		echo "「Pocketmine-MP.phar」を作成しております...";
		echo PHP_EOL;
		$this->makephar();
		echo "cleanup...";
		echo PHP_EOL;
		$this->cleanup();
	}

	public function pocketmine_mp_unzip(){
		$rootpath = dirname(__FILE__);
		$zippath = $rootpath."/PocketMine-MP.zip";
		
		$zip = new ZipArchive();
		$res = $zip->open($zippath);
		if($res === true){
			$filename = $zip->getNameIndex(0);
			copy("zip://".$zippath."#".$filename."composer.json", $rootpath."/composer.json");
			copy("zip://".$zippath."#".$filename."composer.lock", $rootpath."/composer.lock");
			copy("zip://".$zippath."#".$filename.".gitmodules", $rootpath."/.gitmodules");

			$targetpath = "zip://".$zippath."#".$filename."src";
			for($i = 0; $i < $zip->numFiles; $i++){
				$zipfilename = "zip://".$zippath."#".$zip->getNameIndex($i);
				if(strpos($zip->getNameIndex($i),$filename.'src/') === false){
 					continue;
				}
				$target = "zip://".$zippath."#".$zip->getNameIndex($i);
				$output = $rootpath.DIRECTORY_SEPARATOR.str_replace($filename,"",$zip->getNameIndex($i));
				if(substr($target, -1) == '/'){//
					continue;
				}
				if(!file_exists(dirname($output))){
					mkdir(dirname($output), 0744, true);
				}
				if(!copy($target,$output)){
					var_dump("error 展開が出来ませんでした... $target --> $output");
				}
			}
			//$zip->extractTo(dirname(__FILE__)."/",[$filename."src",$filename."composer.json",$filename."composer.lock"]);
			$zip->close();
			unlink($zippath);
		}else{
			$zip->close();
			echo "zip解凍エラー";
			echo PHP_EOL;
			@unlink($zippath);
			exit(1);
		}
	}

	public function ComposerRun(){
		$this->setcanReceiveShutdown(true);
		if(isset($_SERVER['argv'][0])){
			$_SERVER['argv'][0] = dirname(__FILE__) . DIRECTORY_SEPARATOR . "bin". DIRECTORY_SEPARATOR . "composer.phar";
		}
		$_SERVER['argv'][1] = "install";
		$_SERVER['argc'] = count($_SERVER['argv']);
		require "bin" . DIRECTORY_SEPARATOR . "composer.phar";
	}

	public function submodule_add(){
		$gitmodules = file_get_contents(".gitmodules");

		preg_match_all(
		 	'/	path = (.*)[\n|\r\n|\r]?/u',
			$gitmodules,
			$matches,
			PREG_PATTERN_ORDER
		);
		preg_match_all(
			'/	url = (.*).git[\n|\r\n|\r]?/u',
			$gitmodules,
			$matches1,
			PREG_PATTERN_ORDER
		);

		$rootpath = dirname(__FILE__);

		$targetsubmodule = [
			"src/pocketmine/lang/locale" => 0,
			"src/pocketmine/resources/vanilla" => 0,
		];

		foreach($matches[1] as $key => $path1){
			if(!isset($targetsubmodule[$path1])) continue;
			$array = explode("/", $path1);
			$path = implode(DIRECTORY_SEPARATOR, $array);
			$file = $array[count($array)-1];
			$zipfile = $file.".zip";
			//var_dump($matches1[1][$key],$rootpath.DIRECTORY_SEPARATOR.$zipfile);
			echo "download... ".$file;
			echo PHP_EOL;
			copy($matches1[1][$key]."/archive/master.zip",$rootpath.DIRECTORY_SEPARATOR.$zipfile);

			echo "unzip... ".$path;
			echo PHP_EOL;
			$zippath = $rootpath.DIRECTORY_SEPARATOR.$zipfile;

			$zip = new ZipArchive();
			$res = $zip->open($zippath);
			if($res === true){
				$filename = $zip->getNameIndex(0);
				//$zip->extractTo(dirname(__FILE__)."/".$path.DIRECTORY_SEPARATOR);
				for($i = 1; $i < $zip->numFiles; $i++) {
					//$zipfilename = "zip://".$zippath."#".$zip->getNameIndex($i);
					$target = "zip://".$zippath."#".$zip->getNameIndex($i);
					$output = $rootpath.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.str_replace($filename,"",$zip->getNameIndex($i));
					if(substr($target, -1) == '/'){
						continue;
					}
					//var_dump($target,$output);
					if(!file_exists(dirname($output))){
						mkdir(dirname($output), 0744, true);
					}
					if(!copy($target,$output)){
						var_dump("error 展開が出来ませんでした... $target --> $output");
					}
				}
				$zip->close();
				unlink($zippath);
			}else{
				$zip->close();
				echo "zip解凍エラー";
				echo PHP_EOL;
				@unlink($zippath);
				exit(1);
			}
		}
	}

	public function makephar(){
		$file_phar = "PocketMine-MP.phar";
		if(file_exists($file_phar)){
 			echo "Phar file already exists, overwriting...";
 			echo PHP_EOL;
			Phar::unlinkArchive($file_phar);
	 	}
		$files = [];
		$phar = new Phar($file_phar, 0);
		$phar->startBuffering();
		$path = dirname(__FILE__)  . DIRECTORY_SEPARATOR;
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path."src")) as $file){
			if($file->isFile() === false){
				continue;
			}
			$files[str_replace($path,"",$file->getPathname())] = $file->getPathname();
		}

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path."vendor")) as $file){
			if($file->isFile() === false){
				continue;
			}
			$files[str_replace($path,"",$file->getPathname())] = $file->getPathname();
		}
		//var_dump($files);
		echo "圧縮しています...";
		echo PHP_EOL;
		$phar->buildFromIterator(new \ArrayIterator($files));
		$size = (1024 * 512);
		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			//if($finfo->getSize() > (1024 * 512)){//
			if($finfo->getSize() > $size){//
				$finfo->compress(\Phar::GZ);
			}
		}
		$phar->stopBuffering();
		$phar->setStub('<?php require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER();');
//$phar->setStub('<?php define("pocketmine\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER();');
		echo "終了";
		echo PHP_EOL;
	}

	public function cleanup(){
		@unlink(dirname(__FILE__)  . DIRECTORY_SEPARATOR . ".gitmodules");
		@unlink(dirname(__FILE__)  . DIRECTORY_SEPARATOR . "composer.json");
		@unlink(dirname(__FILE__)  . DIRECTORY_SEPARATOR . "composer.lock");
	}

	public function InstallComposerWithoutConfirmation(){
		if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar")){
			mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . "bin", 0744, true);
		}
		$this->downloadFile("https://getcomposer.org/composer.phar",dirname(__FILE__) . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar");//
	}

	public function InstallComposerSafely(){
		$this->setcanReceiveShutdown(true);

		copy('https://getcomposer.org/installer', 'composer-setup.php');
		if(hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1'){
			echo 'Installer verified';
			echo PHP_EOL;
		}else{
			echo 'Installer corrupt';
			echo PHP_EOL;
			unlink('composer-setup.php');
			exit(1);
		}
		//composer-setup.php --install-dir=bin
		if(isset($argv[0])){
			 $argv[0] = dirname(__FILE__) . DIRECTORY_SEPARATOR . "composer-setup.php";
		}
		$argv[1] = "--install-dir=bin";
		//$_SERVER['argc'] = count($_SERVER['argv']);
		require dirname(__FILE__) . DIRECTORY_SEPARATOR . "composer-setup.php";
	}

	public function InstallComposerSafelyShutdown(){
		if(!$this->iscanReceiveShutdown()){
			return;
		}
		$this->setcanReceiveShutdown(false);
		unlink('composer-setup.php');
	}

	public function shutdown(){
		if(!$this->iscanReceiveShutdown()){
			return;
		}
		$this->setcanReceiveShutdown(false);
		$this->run1();
	}

	public function setcanReceiveShutdown(bool $canReceiveShutdown){
		$this->canReceiveShutdown = $canReceiveShutdown;
	}

	public function iscanReceiveShutdown(): bool{
		return $this->canReceiveShutdown;
	}

	public static function isSafetyGithubURL($url): bool{
		return (bool) preg_match('/https\:\/\/github.com\/(.*)\/(.*)\/archive\/(.*).zip/u', $url, $m);
	}

	public function downloadFile($url,$directory){
		copy($url,$directory);//
	}
}

function help(){
	echo PHP_EOL;
	echo "\033[1;33musage:\033[0m";
	echo PHP_EOL;
	echo "　command https://github.com/?????/?????/archive/?????.zip";
	echo PHP_EOL;
	echo PHP_EOL;
	echo "\033[1;33moption:\033[0m";
	echo PHP_EOL;
	echo "　\033[0;32m[make | m]\033[0m					https://github.com/pmmp/PocketMine-MP/archive/stable.zip よりPocketmine-MP.pharを作成致します。";
	echo PHP_EOL;
	echo "　\033[0;32m[phar | p]\033[0m					現在の存在する「src」フォルダと「vendor」フォルダよりPocketMine-MP.pharを作成致します。";
	echo PHP_EOL;
	echo "　\033[0;32m[composerinstall | ci]\033[0m			安全な方法にてcomposerを「bin/composer.phar」にインストールします。";
	echo PHP_EOL;
	echo "　\033[0;32m[composerinstallnv | cinv]\033[0m			composerを検証せずにcomposerを「bin/composer.phar」にインストールします。";
	echo PHP_EOL;
}

if(isset($_SERVER['argv'][1])){
	$args1 = $_SERVER['argv'][1];
	if(maker::isSafetyGithubURL($args1)){
		$maker = new maker();
		register_shutdown_function([$maker,"shutdown"]);
		$maker->run($args1);
		return;
	}
	switch($args1){
		case "make":
		case "m":
			$maker = new maker();
			register_shutdown_function([$maker,"shutdown"]);
			$maker->run("https://github.com/pmmp/PocketMine-MP/archive/stable.zip");
		break;
		case "phar":
		case "p":
			echo "「Pocketmine-MP.phar」を作成致しましております...";
			echo PHP_EOL;
			$maker = new maker();
			$maker->makephar();
			return;
		break;
		case "composerinstall":
		case "ci":
			echo "安全な方法にて「composer.phar」をダウンロードしております...";
			echo PHP_EOL;
			$maker = new maker();
			register_shutdown_function([$maker,"InstallComposerSafelyShutdown"]);
			$maker->InstallComposerSafely();
		break;
		case "composerinstallnv":
		case "cinv":
			echo "「composer.phar」を検証せずにダウンロードしております...";
			echo PHP_EOL;
			$this->InstallComposerWithoutConfirmation();
		break;
		case "help":
		case "h":
			help();
		break;
		default:
			help();
		break;
	}
}else{
	help();
}
