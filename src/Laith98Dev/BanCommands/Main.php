<?php

namespace Laith98Dev\BanCommands;

/*  
 *  A plugin for PocketMine-MP.
 *  
 *	 _           _ _   _    ___   ___  _____             
 *	| |         (_) | | |  / _ \ / _ \|  __ \            
 *	| |     __ _ _| |_| |_| (_) | (_) | |  | | _____   __
 *	| |    / _` | | __| '_ \__, |> _ <| |  | |/ _ \ \ / /
 *	| |___| (_| | | |_| | | |/ /| (_) | |__| |  __/\ V / 
 *	|______\__,_|_|\__|_| |_/_/  \___/|_____/ \___| \_/  
 *	
 *	Copyright (C) 2021 Laith98Dev
 *  
 *	Youtube: Laith Youtuber
 *	Discord: Laith98Dev#0695
 *	Gihhub: Laith98Dev
 *	Email: help@laithdev.tk
 *	Donate: https://paypal.me/Laith113
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 	
 */

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use pocketmine\permission\DefaultPermissions;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\player\Player;

use pocketmine\command\{Command, CommandSender};

class Main extends PluginBase implements Listener 
{
	/** @var Config */
	private $cfg;
	
	public function onEnable(): void{
		@mkdir($this->getDataFolder());
		
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, ["cmds"]);// ok just one time :)
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $cmdLabel, array $args): bool{
		
		switch ($cmd->getName()){
			case "bancommands":
			case "bc":
				if($sender instanceof Player){
					$c = ((isset($args[0]) && in_array(strtolower($args[0]), ["list", "addcmd", "rmcmd", "addworld", "rmcmd", "rmworld"])) ? strtolower($args[0]) : "help");
					switch ($c){
						case "help":
							$sender->sendMessage(TF::YELLOW . "========================");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " help | display list of commands ");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " addcmd <cmd> | add a new command ");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " rmcmd <cmd> | remove an command ");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " addworld <cmd> <world> | add an world to command ");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " rmworld <cmd> <world> | remove an world from command ");
							$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " list <cmd> | display list of command worlds ");
							$sender->sendMessage(TF::YELLOW . "========================");
						break;
						
						case "list":
							if(!isset($args[1])){
								$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " list <cmd> ");
								return false;
							}
							
							$cfg = $this->cfg;
							$all = $cfg->get("cmds", []);
							$cmd_ = strtolower($args[1]);
							
							if(isset($all[$cmd_])){
								$sender->sendMessage(TF::GREEN . "worlds list:");
								foreach ($all[$cmd_] as $c){
									$sender->sendMessage(TF::GREEN . "- " . $c);
								}
							} else {
								$sender->sendMessage(TF::RED . "command not exist!");
							}
						break;
						
						case "addcmd":
							if(!isset($args[1])){
								$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " addcmd <cmd> ");
								return false;
							}
							
							if($this->addCommand($args[1])){
								$sender->sendMessage(TF::YELLOW . "command added!");
								return true;
							}
						break;
						
						case "rmcmd":
							if(!isset($args[1])){
								$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " rmcmd <cmd> ");
								return false;
							}
							
							if($this->removeCommand($args[1])){
								$sender->sendMessage(TF::YELLOW . "command removed!");
								return true;
							}
						break;
						
						case "addworld":
							if(!isset($args[1]) || !isset($args[2])){
								$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " addworld <cmd> <world>");
								return false;
							}
							
							if($this->addWorld($args[1], $args[2])){
								$sender->sendMessage(TF::YELLOW . "world added!");
								return true;
							}
						break;
						
						case "rmworld":
							if(!isset($args[1]) || !isset($args[2])){
								$sender->sendMessage(TF::GREEN . "- /" . $cmdLabel . " rmworld <cmd> <world>");
								return false;
							}
							
							if($this->removeWorld($args[1], $args[2])){
								$sender->sendMessage(TF::YELLOW . "world removed!");
								return true;
							}
						break;
					}
				} else {
					$sender->sendMessage("run command in-game only!");
					return false;
				}
			break;
		}
		
		return true;
	}
	
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event){
        $player = $event->getPlayer();
		$command = $event->getMessage();
		
		$cfg = $this->cfg;
		$all = $cfg->get("cmds", []);
		
		foreach ($all as $cmd => $worlds){
			
			if(in_array($player->getWorld()->getFolderName(), $worlds) && strtolower(explode(" ", $command, 2)[0]) == $cmd){
				// if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				if(!$player->hasPermission("bc.whitelist")){
					$player->sendMessage(TF::RED . "Command Banned!");
					$event->cancel();
				}
			}
			
			// if(in_array($player->getWorld()->getFolderName(), $worlds) && ($command == "/" . $cmd || strpos($command, $cmd) == true)){
				// if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
					// $player->sendMessage(TF::RED . "Command Banned!");
					// $event->cancel();
				// }
			// }
		}
    }
	
	public function addCommand(string $cmd): bool{
		$cfg = $this->cfg;
		$all = $cfg->get("cmds", []);
		$cmd_ = strtolower($cmd);
		
		if(!isset($all[$cmd_])){
			$all[$cmd_] = [];
			$cfg->set("cmds", $all);
			$cfg->save();
			return true;
		}
		
		return false;
	}
	
	public function removeCommand(string $cmd): bool{
		$cfg = $this->cfg;
		$all = $cfg->get("cmds", []);
		$cmd_ = strtolower($cmd);
		
		if(isset($all[$cmd_])){
			unset($all[$cmd_]);
			$cfg->set("cmds", $all);
			$cfg->save();
			return true;
		}
		
		return false;
	}
	
	public function addWorld(string $cmd, string $world): bool{
		$cfg = $this->cfg;
		$all = $cfg->get("cmds", []);
		$cmd_ = strtolower($cmd);
		
		if(isset($all[$cmd_])){
			if(!in_array($world, $all[$cmd_])){
				$all[$cmd_][] = $world;
				$cfg->set("cmds", $all);
				$cfg->save();
				return true;
			}
		}
		
		return false;
	}
	
	public function removeWorld(string $cmd, string $world): bool{
		$cfg = $this->cfg;
		$all = $cfg->get("cmds", []);
		$cmd_ = strtolower($cmd);
		
		if(isset($all[$cmd_])){
			if(in_array($world, $all[$cmd_])){
				unset($all[$cmd_][array_search($world, $all[$cmd_])]);
				$cfg->set("cmds", $all);
				$cfg->save();
				return true;
			}
		}
		
		return false;
	}
}
