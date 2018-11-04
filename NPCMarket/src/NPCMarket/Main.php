<?php

namespace NPCMarket;

use NPCMarket\entities\MarketEntity;

use NPCMarket\commands\MakeShopCommand;
use NPCMarket\commands\DeleteShopCommand;
use NPCMarket\commands\SellAllCommand;
use NPCMarket\commands\UnsetShopCommand;


use NPCMarket\events\MakeShopEvent;
use NPCMarket\events\TouchShopEvent;
use NPCMarket\events\ItemBuyEvent;
use NPCMarket\events\ItemSellEvent;
use NPCMarket\events\DeleteShopEvent;

use onebone\economyapi\EconomyAPI;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\Player;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\utils\Config;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

use pocketmine\item\Item;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
	
	public $prefix;
	
	public function onEnable(){
		$this->prefix = "§l§b[ §fNPCMarket §b] §9";
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getServer()->getPluginManager()->registerEvents(new MakeShopEvent($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new TouchShopEvent($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new DeleteShopEvent($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ItemBuyEvent($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ItemSellEvent($this), $this);
		
		Entity::registerEntity(MarketEntity::class, true);
		
		$this->MakeShopCommand = new MakeShopCommand($this);
		$this->DeleteShopCommand = new DeleteShopCommand($this);
		$this->SellAllCommand = new SellAllCommand($this);
		$this->UnsetShopCommand = new UnsetShopCommand($this);
		
		@mkdir($this->getDataFolder());
		$this->PlayerData = new Config($this->getDataFolder() . "PlayerData.yml", Config::YAML);
		$this->pd = $this->PlayerData->getAll();
		$this->DataBase = new Config($this->getDataFolder() . "DataBase.yml", Config::YAML);
		$this->db = $this->DataBase->getAll();
		$this->ItemPrice = new Config($this->getDataFolder() . "ItemPrice.yml", Config::YAML);
		$this->ip = $this->ItemPrice->getAll();
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$this->pd[$name]["mode"] = "no";
		$this->onSave();
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender->isOp()){
			if($command->getName() == "상점생성"){
				$this->MakeShopCommand->onCommand($sender, $command, $label, $args);
				return true;
			}
			if($command->getName() == "상점제거"){
				$this->DeleteShopCommand->onCommand($sender, $command, $label, $args);
				return true;
			}
			if($command->getName() == "상점데이터제거"){
				$this->UnsetShopCommand->onCommand($sender, $command, $label, $args);
				return true;
			}
		}//isOP 괄호
		if($command->getName() == "판매전체"){
			$this->SellAllCommand->onCommand($sender, $command, $label, $args);
			return true;
		}
	}
	
	public function isCanSelling(Item $item) : bool{
		$id = $item->getId();
		$dam = $item->getDamage();
		$comp = $item->getNamedTag();
		if(isset($this->ip["{$id}~:{$dam}~:{$comp}"])){
			$sellM = $this->ip["{$id}~:{$dam}~:{$comp}"];
			$sellM = explode("~:", $sellM);
			if((int)$sellM[1] >= 0){
				return true;
			}
		}
		return false;
	}
	
	public function isCanBuying(Item $item) : bool{
		$id = $item->getId();
		$dam = $item->getDamage();
		$comp = $item->getNamedTag();
		if(isset($this->ip["{$id}~:{$dam}~:{$comp}"])){
			$sellM = $this->ip["{$id}~:{$dam}~:{$comp}"];
			$sellM = explode("~:", $sellM);
			if((int)$sellM[0] >= 0){
				return true;
			}
		}
		return false;
	}
	
	public function getBuyM(int $money){
		if($money >= 0){
			return $money;
		}else{
			return "구매불가";
		}
	}
	
	public function getSellM(int $money){
		if($money >= 0){
			return $money;
		}else{
			return "판매불가";
		}
	}
	
	public function Buy(Player $player, Item $item, int $count, int $price){
		$economy = EconomyAPI::getInstance();
		$before_money = $economy->myMoney($player);
		$economy->reduceMoney($player, $price);
		$after_money = $economy->myMoney($player);
		$player->getInventory()->addItem($item);
		$player->sendMessage("§l§a{$item->getName()}§7을(를) §a{$count}§7개 구매했습니다.\n§l§7구매 전 : §a{$before_money}원 §7/ 구매 후 : §a{$after_money}원");
	}
	
	public function Sell(Player $player, Item $item, int $count, int $price){
		$economy = EconomyAPI::getInstance();
		$before_money = $economy->myMoney($player);
		$economy->addMoney($player, $price);
		$after_money = $economy->myMoney($player);
		$player->getInventory()->removeItem($item);
		$player->sendMessage("§l§a{$item->getName()}§7을(를) §a{$count}§7개 판매했습니다.\n§l§7판매 전 : §a{$before_money}원 §7/ 판매 후 : §a{$after_money}원");
	}
	
	public function SellAll(Player $player, Item $item, int $count, int $price){
		$economy = EconomyAPI::getInstance();
		$economy->addMoney($player, $price);
		$player->getInventory()->removeItem($item);
	}
	
	public function onSave(){
		$this->PlayerData->setAll($this->pd);
		$this->PlayerData->save();
		$this->ItemPrice->setAll($this->ip);
		$this->ItemPrice->save();
	}
}