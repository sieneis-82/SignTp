<?php

/*
__PocketMine Plugin__
name=SignTp
description=
version=0.0.3
author=DreamWork Studio
class=SignTp
apiversion=11
*/

class SignTp implements Plugin{
    private $api;
	private $version = "0.0.3";
	private $lang, $langFile, $point, $pointFile;
	private $prefix = "[SignTp]";
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		console($this->prefix." Loading plugin version ".$this->version."...");
	}
	public function init(){
		console($this->prefix." Loading language...");
		$this->langFile = new Config($this->api->plugin->configPath($this)."lang.yml", CONFIG_YAML, array(
			"prefix" => "[SignTp]",
			"help" => array(
				"command-st-description" => " :SignTp commands.",
				"command-help" => ">>> SignTp command help <<<\n".
							      "> /st c|create <PointName> [<x> <y> <z> <world>] : Create a point.\n".
								  "> /st d|delete <PointName> : Delete a point.\n".
							      "> /st version : Get plugin version.\n".
								  "> /st signhelp : Get help of creating tpsigns.".
							      "> /st ?|help [cmdname] : Get help of this plugin or a command.\n".
							      ">>> Plugin by DreamWork <<<",
				"sign-help" => ">>> SignTp sign help <<<\n".
							   "> First, use '/st create <Pointname>' to create a point as a target.\n".
							   "> Second, place a sign.Line 1 must be [SignTp], line 2 is the target point's name, line 3 and 4 can be any-typed.\n".
							   "> Then, tap the sign and have fun~~\n".
							   ">>> Plugin by DreamWork <<<",
				"help-version" => ">>> SignTp command help <<<\n".
								  "> Usage: /st version \n".
								  "> See the version of this plugin.\n".
								  ">>> Plugin by DreamWork <<<",
				"help-help" => ">>> SignTp command help <<<\n".
							   "> Usage: /st help|? [cmdName]\n".
							   "> See help of this plugin or a command.\n".
							   "> Example: /st help version \n".
							   ">>> Plugin by DreamWork <<<",
				"help-signhelp" => ">>> SignTp command help <<<\n".
								   "> Usage: /st signhelp\n".
								   "> See help of creating tpsigns.\n".
								   ">>> Plugin by DreamWork <<<",	
				"help-create" => ">>> SignTp command help <<<\n".
								  "> Usage: /st create|c <PointName> [<x> <y> <z> <world>]\n".
								  "> Create a point.\n".
								  "> <PointName>: The target point's name.\n".
								  "> [<x> <y> <z> <world>]: The target's location. You have to type if you're console.\n".
								  ">>> Plugin by DreamWork <<<",
				"help-delete" => ">>> SignTp command help <<<\n".
								  "> Usage: /st delete|d <PointName> [<x> <y> <z> <world>]\n".
								  "> Delete a point.\n".
								  "> <PointName> : The name of the point you'd like to delete.\n".
								  ">>> Plugin by DreamWork <<<",
			),
			"message" => array(
				"version" => "%1 Plugin version : %v",
				"Create-complete" => "%1 You've been CREATED point '%n'",
				"Delete-complete" => "%1 You've been DELETED point '%n'",
				"Tp-complete" => "%1 You are teleported to point '%n'.",
				"Sign-place" => "%1 Sign placed.",
				"Sign-break" => "%1 Sign broke.",
				"Create-complete-console" => "%1 Point '%n' has been CREATED by player %p.",
				"Delete-complete-console" => "%1 Point '%n' has been DELETED by player %p.",
			),
			"err" => array(
				"Unknown-subcmd" => "%1 Unknown subcommand. Type '/st ?' or '/st help' for help.",
				"Empty-name" => "%1 Point's name is empty!",
				"Already-Found" => "%1 Point '%n' already found! Try another name.",
				"Not-Found" => "%1 Point '%n' not found!",
				"Console-create-err" => "%1 Usage:'/st create <PointName> <x> <y> <z> <world>'.",
			),
		));
		$this->lang = $this->api->plugin->readYAML($this->api->plugin->configPath($this)."lang.yml");
		console($this->prefix." Loading points...");
		$this->pointFile = new Config($this->api->plugin->configPath($this)."point.yml", CONFIG_YAML, array());
		/*
		"name" => array(
			"x" => x,
			"y" => y,
			"z" => z,
			"level" => level,
		),
		*/
		$this->reConfig();
		console($this->prefix." Loading base commands...");
		$this->api->console->register("st", $this->lang["help"]["command-st-description"], array($this, "command"));
		console($this->prefix." Loading events...");
		$this->api->addHandler("tile.update", array($this, "eventHandler"));
		$this->api->addHandler("player.block.touch", array($this, "eventHandler"));
		console($this->prefix." Version ".$this->version." successful loaded!");
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
						if($tile->data['Text2'] == ""){$data['player']->sendChat(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Empty-name"]));break;}
						if(!(isset($this->point[$tile->data['Text2']]))){$data['player']->sendChat(str_replace(array("%1","%n"),array($this->lang["prefix"],$tile->data['Text2']),$this->lang["err"]["Not-Found"]));break;}
						$target = $this->point[$tile->data['Text2']];
						$name = $data['player']->username;
						if(!($target["level"] == $data['player']->level->getName())){$data['player']->teleport($this->api->level->get($target["level"])->getSpawn());}
						$this->api->player->tppos($name, $target["x"], $target["y"], $target["z"]);
						$data['player']->sendChat(str_replace(array("%1","%n"),array($this->lang["prefix"],$tile->data['Text2']),$this->lang["message"]["Tp-complete"]));
						break;
				}
		}
	}
	public function command($cmd, $params, $issuer, $alias){
	 	switch($cmd){
			case "st":
				switch($params[0]){
					default:return(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Unknown-subcmd"]));break;
					case "version":return(str_replace(array("%1","%v"),array($this->lang["prefix"],$this->version),$this->lang["message"]["version"]));break;
					case "?":case "help":
						switch($params[1]){
							default:return($this->lang["help"]["command-help"]);break;
							case "version":return($this->lang["help"]["help-version"]);break;
							case "help":case "?":return($this->lang["help"]["help-help"]);break;
							case "signhelp":return($this->lang["help"]["help-signhelp"]);break;
							case "c":case "create":return($this->lang["help"]["help-create"]);break;
							case "d":case "delete":return($this->lang["help"]["help-delete"]);break;
						}
					case "signhelp":return($this->lang["help"]["sign-help"]);break;
					case "create":case "c":
						if($params[1] == ""){return(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Empty-name"]));break;}
						if(isset($this->point[$params[1]])){return(str_replace(array("%1","%n"),array($this->lang["prefix"],$params[1]),$this->lang["err"]["Already-Found"]));break;}
						if(isset($params[2]) && isset($params[3]) && isset($params[4]) && isset($params[5])){
							$data = array(
								"x" => $params[2],
								"y" => $params[3],
								"z" => $params[4],
								"level" => $params[5],
							);
						}else{
							if(!($issuer instanceof Player)){return(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Console-create-err"]));break;}
							$data = array(
								"x" => number_format($issuer->entity->x, 2),
								"y" => number_format($issuer->entity->y, 2),
								"z" => number_format($issuer->entity->z, 2),
								"level" => $issuer->level->getName(),
							);
						}
						$this->pointFile->set($params[1], $data);
						$this->pointFile->save();
						$this->reConfig();
						if($issuer instanceof Player){console(str_replace(array("%1","%n","%p"),array($this->lang["prefix"],$params[1],$issuer->iusername),$this->lang["message"]["Create-complete-console"]));};
						return(str_replace(array("%1","%n"),array($this->lang["prefix"],$params[1]),$this->lang["message"]["Create-complete"]));
						break;
					case "delete":case "d":
						if($params[1] == ""){return(str_replace(array("%1"),array($this->lang["prefix"]),$this->lang["err"]["Empty-name"]));break;}
						if(!isset($this->point[$params[1]])){return(str_replace(array("%1","%n"),array($this->lang["prefix"],$params[1]),$this->lang["err"]["Not-Found"]));break;}
						$data = $this->api->plugin->readYAML($this->api->plugin->configPath($this). "point.yml");
						unset($data[$params[1]]);
						$this->api->plugin->writeYAML($this->api->plugin->configPath($this)."point.yml", $data);
						$this->reConfig();
						if($issuer instanceof Player){console(str_replace(array("%1","%n","%p"),array($this->lang["prefix"],$params[1],$issuer->iusername),$this->lang["message"]["Delete-complete-console"]));};
						return(str_replace(array("%1","%n"),array($this->lang["prefix"],$params[1]),$this->lang["message"]["Delete-complete"]));
						break;
				}
		}
	}
	public function reConfig(){$this->point = $this->api->plugin->readYAML($this->api->plugin->configPath($this)."point.yml");}
}
