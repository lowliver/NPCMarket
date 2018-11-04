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

class DeleteShopEvent implements Listener{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
		
	public function TouchNPC(EntityDamageEvent $event){
		$npc = $event->getEntity();
		if($npc instanceof MarketEntity){
			if($event instanceof EntityDamageByEntityEvent){
				$player = $event->getDamager();
				if($player instanceof Player){
					$name = $player->getName();
					if($this->plugin->pd[$name]["mode"] == "del"){
						$npc->getInventory()->clearAll();
						$npc->kill();
						$player->sendMessage("{$this->plugin->prefix}상점을 제거했습니다.");
						return true;
					}
				}
			}
		}
	}
}