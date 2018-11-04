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

use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\math\Vector3;

class MakeShopEvent implements Listener{
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function CreateShop(DataPacketReceiveEvent $event){
		$player = $event->getPlayer();
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket and $packet->formId == 4343){
			$data = json_decode($packet->formData, true);
			if($data[0] == null or $data[1] == null){
				$player->sendMessage("{$this->plugin->prefix}모든 정보를 정확히 입력해주세요.");
			}else{
				$nbt = new CompoundTag("", [
					new ListTag("Pos", [
						new DoubleTag("", $player->x),
						new DoubleTag("", $player->y),
						new DoubleTag("", $player->z)
                    ]),
					new ListTag("Motion", [
						new DoubleTag("", 0),
						new DoubleTag("", 0),
						new DoubleTag("", 0)
                    ]),
					new ListTag("Rotation",[
						new FloatTag(0, $player->getYaw()),
						new FloatTag(0, $player->getPitch())
                    ]),
					new CompoundTag("Skin", [
						"Data" => new StringTag("Data", $player->getSkin()->getSkinData()),
						"Name" => new StringTag("Name", $player->getSkin()->getSkinId())
					])
                ]);
				$npc = Entity::createEntity("MarketEntity", $player->getLevel(), $nbt);
				$buyM = (int)$data[0];
				$sellM = (int)$data[1];
				$nbuyM = $this->plugin->getBuyM($buyM);
				$nsellM = $this->plugin->getSellM($sellM);
				$item = $player->getInventory()->getItemInHand();
				$npc->setNameTag("{$item->getName()}\n§l§a구매가 : §b{$nbuyM}\n§l§a판매가 : §b{$nsellM}");
				$npci = $npc->getInventory();
				$npci->setItemInHand($item);
				$npc->setNameTagVisible(true);
				$npc->setNameTagAlwaysVisible(true);
				$npc->spawnToAll();
				$player->sendMessage("{$this->plugin->prefix}상점을 생성했습니다.");
				$compound = $item->getNamedTag();
				$id = $item->getId();
				$damage = $item->getDamage();
				$this->plugin->ip["{$id}~:{$damage}~:{$compound}"] = "{$buyM}~:{$sellM}";
				$this->plugin->onSave();
			}
		}
	}
}