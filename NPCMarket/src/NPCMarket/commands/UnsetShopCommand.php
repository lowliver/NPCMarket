<?php

namespace NPCMarket\commands;

use NPCMarket\Main;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class UnsetShopCommand{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender instanceof Player){
			$item = $sender->getInventory()->getItemInHand();
			$id = $item->getId();
			$damage = $item->getDamage();
			$compound = $item->getNamedTag();
			if(isset($this->plugin->ip["{$id}~:{$damage}~:{$compound}"])){
				unset($this->plugin->ip["{$id}~:{$damage}~:{$compound}"]);
				$sender->sendMessage("{$this->plugin->prefix}상점 데이터를 제거했습니다.");
				$this->plugin->onSave();
				return true;
			}else{
				$sender->sendMessage("{$this->plugin->prefix}존재하지 않는 상점입니다.");
				return true;
			}
		}else{
			$sender->sendMessage("{$this->plugin->prefix}인게임에서 사용해주세요.");
			return true;
		}
	}
}