<?php

namespace NPCMarket\events;

use NPCMarket\Main;
use NPCMarket\entities\MarketEntity;

use onebone\economyapi\EconomyAPI;

use pocketmine\event\Listener;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\Player;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\math\Vector3;

class ItemBuyEvent implements Listener{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function BuyItem(DataPacketReceiveEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket and $packet->formId == 4344){
			$data = json_decode($packet->formData, true);
			if($data[2] == null){
				$player->sendMessage("{$this->plugin->prefix}모든 정보를 정확히 입력해주세요.");
			}else{
				if($data[1] == "0"){
					$economy = EconomyAPI::getInstance();
					$npc = $this->plugin->db[$name]["shop"];
					$item = $npc->getInventory()->getItemInHand();
					$id = $item->getId();
					$damage = $item->getDamage();
					$amount = (int)$data[2];
					$compound = $item->getNamedTag();
					$price = $this->plugin->ip["{$id}~:{$damage}~:{$compound}"];
					$price = explode("~:", $price);
					if($this->plugin->isCanBuying($item)){
						if($economy->myMoney($player) >= $price[0]*$amount){
							$price = $price[0]*$amount;
							$item->setCount($amount);
							$this->plugin->Buy($player, $item, $amount, $price);
						}else{
							$player->sendMessage("{$this->plugin->prefix}돈이 부족하여 구매할 수 없습니다.");
						}
					}else{
						$player->sendMessage("{$this->plugin->prefix}구매가 불가능한 이이템입니다.");
					}
				}
			}
		}
	}
}