<?php

namespace NPCMarket\commands;

use NPCMarket\Main;

use onebone\economyapi\EconomyAPI;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class SellAllCommand{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender instanceof Player){
			$inv = $sender->getInventory();
			$economy = EconomyAPI::getInstance();
			$before_money = $economy->myMoney($sender);
			for($i=0; $i<=35; $i++){
				$item = $inv->getItem($i);
				$id = $item->getId();
				$damage = $item->getDamage();
				$count = $item->getCount();
				$compound = $item->getNamedTag();
				if($id !== 0 and $this->plugin->isCanSelling($item)){
					$price = $this->plugin->ip["{$id}~:{$damage}~:{$compound}"];
					$price = explode("~:", $price);
					$price = $price[1]*$count;
					$this->plugin->SellAll($sender, $item, $count, $price);
				}
			}
			$after_money = $economy->myMoney($sender);
			$sender->sendMessage("§l§7아이템을 §a모두 §7판매했습니다.\n§l§7판매 전 : §a{$before_money}원 §7/ 판매 후 : §a{$after_money}원");
			return true;
		}
	}
}