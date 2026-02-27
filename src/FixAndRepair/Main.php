<?php

declare(strict_types=1);

namespace FixAndRepair;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    /** @var array<string, string> */
    private array $confirming = [];

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        if(!$sender instanceof Player){
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        if(!$sender->hasPermission("fixnrepair.use")){
            $sender->sendMessage("§cYou don't have permission to use this command.");
            return true;
        }

        $item = $sender->getInventory()->getItemInHand();

        if($item->isNull()){
            $sender->sendMessage("§cHold a repairable item.");
            return true;
        }

        if(!$item->isDamageable()){
            $sender->sendMessage("§cThis item cannot be repaired.");
            return true;
        }

        if($item->getDamage() <= 0){
            $sender->sendMessage("§eThis item is not damaged.");
            return true;
        }

        $name = $sender->getName();
        $cmd = strtolower($command->getName());

        // First time running command
        if(!isset($this->confirming[$name])){
            $this->confirming[$name] = $cmd;
            $sender->sendMessage("§eRun /$cmd again to confirm. It will cost §c30 XP levels§e.");
            return true;
        }

        // Confirmed
        if($this->confirming[$name] === $cmd){

            unset($this->confirming[$name]);

            // XP bypass for OP
            if(!$sender->hasPermission("fixnrepair.bypass")){
                if($sender->getXpManager()->getXpLevel() < 30){
                    $sender->sendMessage("§cYou need 30 XP levels to repair this item.");
                    return true;
                }
                $sender->getXpManager()->setXpLevel(
                    $sender->getXpManager()->getXpLevel() - 30
                );
            }

            $item->setDamage(0);
            $sender->getInventory()->setItemInHand($item);

            $sender->sendMessage("§aItem successfully repaired!");
        }

        return true;
    }
}
