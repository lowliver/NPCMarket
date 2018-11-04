<?php

namespace NPCMarket\commands;

use NPCMarket\Main;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class DeleteShopCommand{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender instanceof Player){
			$name = $sender->getName();
			if($this->plugin->pd[$name]["mode"] !== "del"){
				$this->plugin->pd[$name]["mode"] = "del";
				$this->plugin->onSave();
				$sender->sendMessage("{$this->plugin->prefix}제거할 상점을 클릭/터치해주세요.");
				$sender->sendMessage("{$this->plugin->prefix}명령어 재사용으로 제거를 중지할 수 있습니다.");
				return true;
			}else{
				$this->plugin->pd[$name]["mode"] = "no";
				$this->plugin->onSave();
				$sender->sendMessage("{$this->plugin->prefix}상점 제거를 중지했습니다.");
				return true;
			}
		}else{
			$sender->sendMessage("{$this->plugin->prefix}인게임에서 사용해주세요.");
			return true;
		}
	}
}