<?php

namespace NPCMarket\events;

use NPCMarket\Main;
use NPCMarket\entities\MarketEntity;

use pocketmine\event\Listener;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\Player;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

use pocketmine\item\Item;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class TouchShopEvent implements Listener{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
		
	public function TouchNPC(EntityDamageEvent $event){
		$npc = $event->getEntity();
		if($npc instanceof MarketEntity){
			$event->setCancelled();
			if($event instanceof EntityDamageByEntityEvent){
				$player = $event->getDamager();
				if($player instanceof Player){
					$name = $player->getName();
					$item = $npc->getInventory()->getItemInHand();
					$id = $item->getId();
					$damage = $item->getDamage();
					$compound = $item->getNamedTag();
					if($this->plugin->pd[$name]["mode"] == "no" and isset($this->plugin->ip["{$id}~:{$damage}~:{$compound}"])){
						$arr = $this->plugin->ip["{$id}~:{$damage}~:{$compound}"];
						$arr = explode("~:", $arr);
						$arr[0] = $this->plugin->getBuyM((int)$arr[0]);
						$arr[1] = $this->plugin->getSellM((int)$arr[1]);
						$pack = new ModalFormRequestPacket();
						$pack->formId = 4344;
						$pack->formData = json_encode([
							"type" => "custom_form",
							"title" => "{$this->plugin->prefix}",
							"content" => [
								[
								"type" => "label",
								"text" => "§l§f{$item->getName()}\n§l§a구매가 : §b{$arr[0]}\n§l§a판매가 : §b{$arr[1]}"
								],
								[
								"type" => "dropdown",
								"text" => "§l§f",
								"options" => ["§l§f구매", "§l§f판매"]
								],
								[
								"type" => "input",
								"text" => "§l§0갯수를 적어주세요."
								]
							]
                        ]);
                        $player->dataPacket($pack);
                        $this->plugin->db[$name]["shop"] = $npc;
					}
				}
			}
		}
	}
}