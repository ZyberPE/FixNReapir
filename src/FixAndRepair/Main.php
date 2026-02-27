<?php

declare(strict_types=1);

namespace FixAndRepair;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    private array $confirming = [];

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        if(!$sender instanceof Player){
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        if(!$sender->hasPermission("fixnrepair.use")){
            $sender->sendMessage("§cYou don't have permission.");
            return true;
        }

        $item = $sender->getInventory()->getItemInHand();

        if($item->isNull()){
            $sender->sendMessage("§cHold an item.");
            return true;
        }

        // ✅ API 5 repairable check
        if($item->getMaxDurability() <= 0){
            $sender->sendMessage("§cThis item cannot be repaired.");
            return true;
        }

        if($item->getDamage() <= 0){
            $sender->sendMessage("§eThis item is not damaged.");
            return true;
        }

        $playerName = $sender->getName();
        $cmd = strtolower($command->getName());

        // First execution (confirmation step)
        if(!isset($this->confirming[$playerName])){
            $this->confirming[$playerName] = $cmd;
            $sender->sendMessage("§eRun /$cmd again to confirm. Cost: §c30 XP levels");
            return true;
        }

        // Confirmed
        if($this->confirming[$playerName] === $cmd){

            unset($this->confirming[$playerName]);

            if(!$sender->hasPermission("fixnrepair.bypass")){
                if($sender->getXpManager()->getXpLevel() < 30){
                    $sender->sendMessage("§cYou need 30 XP levels.");
                    return true;
                }

                $sender->getXpManager()->setXpLevel(
                    $sender->getXpManager()->getXpLevel() - 30
                );
            }

            $item->setDamage(0);
            $sender->getInventory()->setItemInHand($item);

            $sender->sendMessage("§aItem repaired successfully!");
        }

        return true;
    }
}
