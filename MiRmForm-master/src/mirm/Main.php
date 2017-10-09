<?php

namespace mirm;

/* base */
use pocketmine\plugin\PluginBase;

/* event */
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

/* packet */
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

/* config */
use pocketmine\utils\Config;

/* command */
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener{

	public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	

		if(!file_exists($this->getDataFolder())){

			mkdir($this->getDataFolder(), 0744, true);

		}

		$this->name = new Config($this->getDataFolder() . "name.yml", Config::YAML);

		@mkdir($this->getDataFolder());
		$this->saveResource("mirm.json");

	}

	public function onChat(PlayerChatEvent $event){

		$name = $event->getPlayer()->getName();

		if(!$this->name->exists($name)){

			$event->setCancelled();
		}
	}

	public function mirm($player){

		$dir = file_get_contents($this->getDataFolder() . "mirm.json");

		$pk = new ModalFormRequestPacket();

		$pk->formId = 10000;

		$pk->formData = $dir;

		$player->dataPacket($pk);

	}

	public function onCommand(CommandSender $sender, Command $command,string $label, array $args):bool{

		$cmd = strtolower($command->getName());

		switch($cmd) {

			case "mirm":

				$name = $sender->getName();

				$player = $sender;

				if(!$this->name->exists($name)){

					$this->mirm($player);

				}

			return false;
			break;
		}
	}		

	public function onReceivePacket(DataPacketReceiveEvent $event){

		$player = $event->getPlayer();

		$pk = $event->getPacket();

		$name = $player->getName();

		$key = "haniokasai";

		if($pk instanceof ModalFormResponsePacket){

			$id = $pk->formId;

			$data = $pk->formData;

			switch($id){

				case 10000;

					$Fdata = json_decode($data, true);

					if($data === "null\n"){

						break;

					}elseif($Fdata[1] === ""){

						$player->sendMessage("§c>> 認証キーを入力してください");

						$this->mirm($player);

					break;

					}if($Fdata[1] === $key){

						$player->addTitle("§a認証に成功しました", "§fチャットをすることが可能です", "10", "60", "10");

						$this->name->set($name);
						$this->name->save();

					break;	

					}else{

						$player->sendMessage("§c>> 認証キーが違います");

						$this->mirm($player);

						break;
					}
			}
		}
	}
}