<?php

namespace NPCMarket\commands;

use NPCMarket\Main;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class MakeShopCommand{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender instanceof Player){
			$pack = new ModalFormRequestPacket();
			$pack->formId = 4343;
			$pack->formData = json_encode([
				"type" => "custom_form",
				"title" => "{$this->plugin->prefix}",
				"content" => [
					[
					"type" => "input",
					"text" => "§l§0아이템의 구매가를 적어주세요.",
					],
					[
					"type" => "input",
					"text" => "§l§0아이템의 판매가를 적어주세요.",
					]
				]
			]);
			$sender->dataPacket($pack);
			return true;
		}else{
			$sender->sendMessage("{$this->plugin->prefix}인게임에서 사용해주세요.");
			return true;
		}
	}
}