<?php

/*
__PocketMine Plugin__
name=SignTp
description=
version=0.2.0
author=DreamWork Studio
class=SignTp
apiversion=12,13
*/

define("SIGNTP_VERSION", "0.2.0");
class SignTp implements Plugin{
    private $api;
	private $lang, $point;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		
	}
	public function init(){
		if(!file_exists(FILE_PATH."plugins/SimpleWarp.php")){
			console("[ERROR] SimpleWarp not found! You MUST install SimpleWarp first!");
		}else{
			console("[SignTp] Loading with SimpleWarp...");
			$this->point = $this->api->plugin->readYAML(FILE_PATH."plugins\\SimpleWarp\\warps.yml");
		}
		if(!file_exists($this->api->plugin->configPath($this)."lang.yml")){
			console("[SignTp] Language not found! Creating one.");
			$this->api->plugin->writeYAML($this->api->plugin->configPath($this)."lang.yml",array(
				"prefix" => "[SignTp]",
				"help" => array(
					"command-st-description" => " :SignTp commands.",
					"command-help" => ">>> SignTp command help <<<\n".
							  "> /st version : Get plugin version.\n".
							  "> /st signhelp : Get help of creating tpsigns.\n".
							  "> /st reconfig : Reload point config.\n".
							  "> /st checkupd : Check the update. Only work if you're console.\n".
							  "> /st ?|help [cmdname] : Get help of this plugin or a command.\n".
							  ">>> Plugin by DreamWork <<<",
					"sign-help" => ">>> SignTp sign help <<<\n".
								   "> Place a sign.Line 1 must be [SignTp], line 2 is the target point's name, line 3 and 4 can be any-typed.\n".
								   "> line 2 is w:world -> teleport to a worlds spawn.\n".
								   "> Then, tap the sign and have fun~~\n".
								   ">>> Plugin by DreamWork <<<",
					"help-version" => ">>> SignTp command help <<<\n".
									  "> Usage: /st version\n".
									  "> Shows plugin version.\n".
									  ">>> Plugin by DreamWork <<<",
					"help-help" => ">>> SignTp command help <<<\n".
								   "> Usage: /st help|? [cmdName]\n".
								   "> See help of this plugin or a command.\n".
								   ">>> Plugin by DreamWork <<<",
					"help-signhelp" => ">>> SignTp command help <<<\n".
									   "> Usage: /st signhelp\n".
									   "> See help of creating tpsigns.\n".
									   ">>> Plugin by DreamWork <<<",
					"help-reconfig" => ">>> SignTp command help <<<\n".
							   "> Usage: /st reconfig\n".
							   "> Reload point data. Only work if you're console.\n".
							   ">>> Plugin by DreamWork <<<",
					"help-checkupd" => ">>> SignTp command help <<<\n".
							   "> Usage: /st checkupd\n".
							   "> Check the update. Only work if you're console.\n".
							   ">>> Plugin by DreamWork <<<",
				),
				"message" => array(
					"version" => "%1 Plugin version : %v",
					"Sign-place" => "%1 Sign placed.",
					"Sign-break" => "%1 Sign broke.",
					"reconfig-ing" => "%1 Reconfiging...",
					"reconfig-done" => "%1 Reconfig Done.",
					"checkupd-ing" => "%1 Checking update...",
					"checkupd-result" => "%1 Server verion : %s , Your version : %v .",
					"checkupd-dl" => "%1 Not the newest version! Visit %u to download.",
					"Tp-complete" => "%1 You are teleported to point '%n'.",
				),
				"err" => array(
					"console-only" => "%1 This command is only for console to use!",
					"Unknown-subcmd" => "%1 Unknown subcommand. Type '/st help' for help.",
					"Empty-name" => "%1 Point's name is empty!",
					"Not-Found" => "%1 Point '%n' not found!",
					"checkupd-connect-err" => "%1 Cannot connect to the server!",
				),
			));
		}
		$this->lang = $this->api->plugin->readYAML($this->api->plugin->configPath($this)."lang.yml");
		console("[SignTp] Language loaded.");
		$this->api->addHandler("tile.update", array($this, "eventHandler"));
		$this->api->addHandler("player.block.touch", array($this, "eventHandler"));
		console("[SignTp] Event loaded.");
		$this->api->console->register("st", $this->lang["help"]["command-st-description"], array($this, "commandst"));
		$this->api->ban->cmdWhitelist("st");
		console("[SignTp] Command loaded.");
		console("[SignTp] Version ".SIGNTP_VERSION." loaded.");
	}
	public function __destruct(){}
	public function eventHandler(&$data, $event){
		switch($event){
			case "tile.update":
				if(!($data->class == TILE_SIGN)) break;
				if(!($data->data['Text1'] == "[SignTp]")) break;
				$this->api->player->get($data->data['creator'])->sendChat(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["message"]["Sign-place"]));break;
			case "player.block.touch":
				$tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
				if($tile === false) break;
				if(!($tile->class == TILE_SIGN)) break;
				if(!($tile->data['Text1'] == "[SignTp]")) break;
				switch($data['type']){
					case "break":
						$data['player']->sendChat(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["message"]["Sign-break"]));break;
					case "place":
						$this->reConfig();
						if($tile->data['Text2'] == ""){$data['player']->sendChat(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Empty-name"]));break;}
						$name = $data['player']->username;
						if(substr($tile->data['Text2'],0,1) == "w:"){
							$target = substr($tile->data['Text2'],2);
							$data['player']->teleport($this->api->level->get($target)->getSpawn());
						}else{
							if(!(isset($this->point[$tile->data['Text2']]))){$data['player']->sendChat(str_replace(array("%1","%n"),array($this->lang["prefix"],$tile->data['Text2']),$this->lang["err"]["Not-Found"]));break;}
							$target = $this->point[$tile->data['Text2']];
							if(!($target[3] == $data['player']->level->getName())){$data['player']->teleport($this->api->level->get($target[3])->getSpawn());}
							$this->api->player->tppos($name, $target[0], $target[1], $target[2]);
						}
						$data['player']->sendChat(str_replace(array("%1","%n"),array($this->lang["prefix"],$tile->data['Text2']),$this->lang["message"]["Tp-complete"]));
						break;
				}
		}
	}
	public function commandst($cmd, $params, $issuer, $alias){
		switch($params[0]){
			default:return(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Unknown-subcmd"]));break;
			case "ver":return(str_replace(array("%1","%v"),array($this->lang["prefix"],SIGNTP_VERSION),$this->lang["message"]["version"]));break;
			case "?":case "help":
				switch($params[1]){
					default:return($this->lang["help"]["command-help"]);break;
					case "version":return($this->lang["help"]["help-version"]);break;
					case "help":case "?":return($this->lang["help"]["help-help"]);break;
					case "signhelp":return($this->lang["help"]["help-signhelp"]);break;
					case "tp":return($this->lang["help"]["help-tp"]);break;
					case "reconfig":return($this->lang["help"]["help-reconfig"]);break;
					case "checkupd":return($this->lang["help"]["help-checkupd"]);break;
				}
			case "signhelp":return($this->lang["help"]["sign-help"]);break;
			case "reconfig":
				if($issuer instanceof Player){return str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["console-only"]);}
				console(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["message"]["reconfig-ing"]));
				$this->reConfig();
				console(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["message"]["reconfig-done"]));
				break;
			case "checkupd":
				if($issuer instanceof Player){return str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["console-only"]);}
				console(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["message"]["checkupd-ing"]));
				$upd = $this->checkUpd();
				if($upd[0] == "error"){
					console("[ERROR] ".$upd[1]);
				}else{
					console(str_replace(array("%1","%s","%v"),array($this->lang["prefix"],$upd[0],SIGNTP_VERSION),$this->lang["message"]["checkupd-result"]));
					if($this->compareVer(SIGNTP_VERSION, $upd[0]) == "2"){
						console(str_replace(array("%1","%u"),array($this->lang["prefix"],str_replace(array("#"),array(""),$upd[1])),$this->lang["message"]["checkupd-dl"]));
					}
				}
				break;
		}
	}
	public function reConfig(){$this->point = $this->api->plugin->readYAML(FILE_PATH."plugins\\SimpleWarp\\warps.yml");}
	public function checkUpd(){
		$i = file_get_contents("http://www.mcbbs.net/thread-226762-1-1.html");
		if(strpos($i,"####")){
			$o = explode("####",$i);
			return array($o[1], $o[2]);
		}else{
			return array("error",str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["checkupd-connect-err"]));
		}
	}
	public function compareVer($ver1, $ver2){
		$ver1e = explode(".",$ver1);
		$ver2e = explode(".",$ver2);
		if((double)$ver1e[0]<(double)$ver2e[0]){
			return "2";
		}elseif((double)$ver1e[1]<(double)$ver2e[1]){
			return "2";
		}elseif((double)$ver1e[2]<(double)$ver2e[2]){
			return "2";
		}elseif((double)$ver1e[2]=(double)$ver2e[2]){
			return "0";
		}else{
			return "1";
		}
	}
}
