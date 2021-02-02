<?php
class maker{
	public $canReceiveShutdown = false;//
	public $enableCompressAll = false;

	public function __construct(){
		$this->checkOption();
	}

	public function run(String $pocketmine_mp_zip_url){
		if(!file_exists(__DIR__. DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar")){
			echo "「composer.phar」を検証せずにダウンロードしております...";
			echo PHP_EOL;
			$this->InstallComposerWithoutConfirmation();
		}
		if(!file_exists(__DIR__. DIRECTORY_SEPARATOR . "src")){
			if(!self::isSafetyGithubURL($pocketmine_mp_zip_url)){
				echo "指定致しましたurlは不正にてございます。";
				return;
			}
			echo "Pocketmine-MPをダウンロードしています...(".$pocketmine_mp_zip_url.")";
			echo PHP_EOL;
			self::downloadFile($pocketmine_mp_zip_url, __DIR__. DIRECTORY_SEPARATOR . "PocketMine-MP.zip");
			echo "PocketMine-MPを解凍しております...";
			echo PHP_EOL;
			$this->pocketmine_mp_unzip();
		}
		if(!file_exists(__DIR__. DIRECTORY_SEPARATOR . "vendor")){
			echo "「bin\composer.phar install --no-dev --classmap-authoritative」をプログラム内より実行しております...(exec未使用...)";
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
		$rootpath = __DIR__;
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
				if(strpos($zip->getNameIndex($i),$filename.'src/') === false&&strpos($zip->getNameIndex($i),$filename.'resources/') === false){
 					continue;
				}
				$target = "zip://".$zippath."#".$zip->getNameIndex($i);
				$output = $rootpath.DIRECTORY_SEPARATOR.str_replace($filename,"",$zip->getNameIndex($i));
				if(substr($target, -1) === '/'){//
					continue;
				}
				if(!file_exists(dirname($output))){
					mkdir(dirname($output), 0744, true);
				}
				if(!copy($target,$output)){
					var_dump("error 展開が出来ませんでした... $target --> $output");
				}
			}
			//$zip->extractTo(__DIR__."/",[$filename."src",$filename."composer.json",$filename."composer.lock"]);
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
			$_SERVER['argv'][0] = __DIR__ . DIRECTORY_SEPARATOR . "bin". DIRECTORY_SEPARATOR . "composer.phar";
		}
		$_SERVER['argv'][1] = "install";
		$_SERVER['argv'][2] = "--no-dev";
		$_SERVER['argv'][3] = "--classmap-authoritative";
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

		$rootpath = __DIR__;

		$excludesubmodule = [
			"DevTools",
			"preprocessor",
			"build/php",
		];

		foreach($matches[1] as $key => $path1){
			foreach($excludesubmodule as $submodule) if(strpos($path1,$submodule) !== false) continue 2;
			$array = explode("/", $path1);
			$path = implode(DIRECTORY_SEPARATOR, $array);
			var_dump($path);
			$file = $array[count($array)-1];
			$zipfile = $file.".zip";
			//var_dump($matches1[1][$key],$rootpath.DIRECTORY_SEPARATOR.$zipfile);
			echo "download... ".$file;
			echo PHP_EOL;
			copy($matches1[1][$key]."/archive/master.zip",$rootpath.DIRECTORY_SEPARATOR.$zipfile);

			echo "unzip... ".$path;
			echo PHP_EOL;
			$zippath = $rootpath.DIRECTORY_SEPARATOR.$zipfile;
			$this->unzip($rootpath, $zippath, $path);
		}
	}

	public function unzip($rootpath, $zippath, $path){
		$zip = new ZipArchive();
		$res = $zip->open($zippath);
		if($res === true){
			$filename = $zip->getNameIndex(0);
			//$zip->extractTo(__DIR__."/".$path.DIRECTORY_SEPARATOR);
			for($i = 1; $i < $zip->numFiles; $i++) {
				//$zipfilename = "zip://".$zippath."#".$zip->getNameIndex($i);
				$target = "zip://".$zippath."#".$zip->getNameIndex($i);
				$output = $rootpath.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.str_replace($filename,"",$zip->getNameIndex($i));
				if(substr($target, -1) === '/'){
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

	public function makephar(?string $path = null, ?array $list = null, string $file_phar = "PocketMine-MP.phar"){
		if(file_exists($file_phar)){
 			echo "Phar file already exists, overwriting...";
 			echo PHP_EOL;
			Phar::unlinkArchive($file_phar);
	 	}

		if($path === null){
			$path = __DIR__  . DIRECTORY_SEPARATOR;
		}

		if($list === null){
			$list = [
				"src",
				"vendor",
				"resources",
			];
		}

		$files = [];
		$phar = new Phar($file_phar, 0);
		$phar->startBuffering();

		$phar->setSignatureAlgorithm(\Phar::SHA1);

		foreach($list as $value){
			$target = $path.$value;

			if(is_file($target)){
				$files[$value] = $target;
				continue;
			}
		    if(!is_dir($target)) continue;

			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target)) as $path1 => $file){
				if($file->isFile() === false){
					continue;
				}
				$files[str_replace($path, "", $path1)] = $path1;
			}
		}

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

		if($this->enableCompressAll){
			$phar->compressFiles(Phar::GZ);
		}else{
			$size = (1024 * 512);
			foreach($phar as $file => $finfo){
				/** @var \PharFileInfo $finfo */
				//if($finfo->getSize() > (1024 * 512)){//
				if($finfo->getSize() > $size){//
					$finfo->compress(\Phar::GZ);
				}
			}
		}
		/*if($enableCompressAll)){
			echo "compressAll...";
			$phar->compressFiles(Phar::GZ);
		}*/

        if(file_exists( __DIR__ ."/src/PocketMine.php")){
	        $phar->setStub(<<<'STUB'
<?php
$tmpDir = sys_get_temp_dir();
if(!is_readable($tmpDir) or !is_writable($tmpDir)){
	echo "ERROR: tmpdir $tmpDir is not accessible." . PHP_EOL;
	echo "Check that the directory exists, and that the current user has read/write permissions for it." . PHP_EOL;
	echo "Alternatively, set 'sys_temp_dir' to a different directory in your php.ini file." . PHP_EOL;
	exit(1);
}
require("phar://" . __FILE__ . "/src/PocketMine.php");
__HALT_COMPILER();
STUB);
        }else{
	        $phar->setStub('<?php require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER();');
        }

		$phar->stopBuffering();
		echo "終了";
		echo PHP_EOL;
	}

	public function cleanup(){
		//@unlink(__DIR__  . DIRECTORY_SEPARATOR . ".gitmodules");
		//@unlink(__DIR__  . DIRECTORY_SEPARATOR . "composer.json");
		//@unlink(__DIR__  . DIRECTORY_SEPARATOR . "composer.lock");
	}

	public function InstallComposerWithoutConfirmation(){
		if(!file_exists(__DIR__ . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar")){
			@mkdir(__DIR__ . DIRECTORY_SEPARATOR . "bin", 0744, true);
		}
		self::downloadFile("https://getcomposer.org/composer-stable.phar",__DIR__ . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "composer.phar");//
	}

	public function InstallComposerSafely(){
		$this->setcanReceiveShutdown(true);
		echo "Downloading composer installer...".PHP_EOL;
		copy('https://getcomposer.org/installer', 'composer-setup.php');
		echo "Checking the correctness of composer installation...".PHP_EOL;
		if(hash_file('sha384', 'composer-setup.php') === explode(" ", trim(file_get_contents("https://composer.github.io/installer.sha384sum")))[0]){
			echo "installer sha384: verified".PHP_EOL;
		}else{
			echo "installer sha384: corrupt".PHP_EOL;
			echo "error: The composer installer is incorrect.".PHP_EOL;

			unlink('composer-setup.php');
			exit(1);
		}
		echo 'running "./composer-setup.php --install-dir=bin"'.PHP_EOL;
		//composer-setup.php --install-dir=bin
		if(isset($argv[0])){
			 $argv[0] = __DIR__ . DIRECTORY_SEPARATOR . "composer-setup.php";
		}
		$argv[1] = "--install-dir=bin";
		//$_SERVER['argc'] = count($_SERVER['argv']);
		require __DIR__ . DIRECTORY_SEPARATOR . "composer-setup.php";
	}

	public function InstallComposerSafelyShutdown(){
		if(!$this->iscanReceiveShutdown()){
			return;
		}
		$this->setcanReceiveShutdown(false);
		unlink('composer-setup.php');
	}

	public function makeDevTols($url){
		echo "DevToolsをダウンロード致しましております...";
		echo PHP_EOL;
		if(!self::isSafetyGithubURL($url)){
			echo "指定致しましたurlは不正にてございます。";
			echo PHP_EOL;
			return;
		}
		$rootpath = __DIR__;
		$zippath = $rootpath . DIRECTORY_SEPARATOR ."DevTools.zip";
		$path = "DevTools";
		$pharpath = "DevTools.phar";
		$list = [
			"resources",
			"src",
			"plugin.yml",
			"LICENSE",
		];

		self::downloadFile($url,$zippath);
		$this->unzip($rootpath, $zippath, $path);
		$this->makephar($path, $list, $pharpath);
	}

	function checkOption(){
		$args = $_SERVER['argv'];
		$count = count($args)-1;
		for ($i = 1; $i <= $count; $i++) {
			$option = strtolower($args[$i]);
			/*if(($option[1] ?? "") === "-"){
				$option = substr($option,2);
			}
			if(($option[0] ?? "") === "-"){
				$option = substr($option,1);
			}*/

			switch($option){
				case "--pharcompress":
				case "-p":
					$this->enableCompressAll = true;
					break;
				case "-d":
				case "--makes":
					echo "「DevTools」を作成しております...";
					echo PHP_EOL;
					$maker = new maker();
					$maker->makeDevTols("https://github.com/pmmp/DevTools/archive/master.zip");
					break;
				case "-m":
				case "--makem":
					echo "「DevTools」を作成しております...";
					echo PHP_EOL;
					$maker = new maker();
					$maker->makeDevTols("https://github.com/pmmp/DevTools/archive/master.zip");
					break;
			}
		}
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

	public static function isSafetyGithubDevToolsReleaseURL($url): array{
		return [(bool) preg_match('/https\:\/\/github\.com\/(.*)\/(.*)\/releases\/download\/(.*)\/DevTools\.phar/u', $url, $m), $m[3] ?? "null"];
	}

	public static function downloadFile($url,$directory){
		copy($url,$directory);//
	}

	public static function get($url,$data = false,$request = false){
		if(strpos($url, "/") !== false){
			$url = str_replace("https://api.github.com",  "", $url);
		}
		/*
		echo "\n";
		if($request !== false){
			$request.": https://api.github.com".$url."\n";
		}else if($data !== false){
			echo "POST: https://api.github.com".$url."\n";
		}else{
			echo "GET: https://api.github.com".$url."\n";
		}
		*/

		$curl = curl_init("https://api.github.com".$url);

		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false); // オレオレ証明書対策
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION, true);// Locationヘッダを追跡

		if($request !== false) curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$request);
		if($data !== false){
			curl_setopt($curl,CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));
		}

		curl_setopt($curl,CURLOPT_USERAGENT,      "USER_AGENT");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($curl);

		$errno = curl_errno($curl);
		$error = curl_error($curl);
		if($errno !== CURLE_OK){
			throw new RuntimeException($error, $errno);
		}

		curl_close($curl);
		$test = json_decode($return,true);
		return $test;
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
	echo "　\033[0;32m[d | makes]\033[0m					stableブランチ向けdevtoolsをgithub APIを用いてgithub releaseよりダウンロードします。";
	echo PHP_EOL;
	echo "　\033[0;32m[s | made]\033[0m					masterブランチ向けdevtoolsをgithubよりダウンロード、作成します。";
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
			$maker = new maker();
			$maker->InstallComposerWithoutConfirmation();
		break;
		case "d":
		case "makes":
			echo "「DevTools」をダウンロードしております...";
			echo PHP_EOL;
			$return = maker::get("/repos/pmmp/DevTools/releases/latest");
			if(!isset($return["assets"][0]["browser_download_url"])){
				echo "error: The response from the github API is incorrect and you will not be able to download the release.";
				echo PHP_EOL;
			}
			$downloadurl = $return["assets"][0]["browser_download_url"];
			[$safety, $var] = maker::isSafetyGithubDevToolsReleaseURL($downloadurl);
			if(!$safety){
				echo "error: The download url received from the github API is invalid, so devTools cannot be downloaded.";
				echo PHP_EOL;
			}
			echo "downloading DevTools ".$var."...";
			maker::downloadFile($downloadurl,__DIR__."/DevTools.phar");
			break;
		case "s":
		case "made":
			echo "「DevTools」を作成しております...";
			echo PHP_EOL;
			$maker = new maker();
			$maker->makeDevTols("https://github.com/pmmp/DevTools/archive/master.zip");
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
