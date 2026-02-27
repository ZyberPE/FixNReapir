<?php

namespace FixAndRepair;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    /** @var array<string, int> */
    private array $confirming = [];

    private const COST = 30; // XP levels required

    public function onEnable(): void {
        $this->getLogger()->info("FixAndRepair enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if (!$sender instanceof Player) {
            $sender->sendMessage("§cUse this command in-game.");
            return true;
        }

        $item = $sender->getInventory()->getItemInHand();

        // Check if item is repairable (has durability)
        if (!$item->isDamaged() && $item->getMaxDurability() <= 0) {
            $sender->sendMessage("§cHold a repairable item.");
            return true;
        }

        $name = strtolower($sender->getName());

        // First confirmation
        if (!isset($this->confirming[$name])) {
            $this->confirming[$name] = time();
            $sender->sendMessage("§eHold the item and run §a/$label §eagain to confirm. Cost: §c30 XP levels§e.");
            return true;
        }

        unset($this->confirming[$name]);

        // Check XP cost (unless bypass)
        if (!$sender->hasPermission("fixnrepair.bypass")) {

            if ($sender->getXpManager()->getXpLevel() < self::COST) {
                $sender->sendMessage("§cYou need 30 XP levels to repair this item.");
                return true;
            }

            $sender->getXpManager()->setXpLevel(
                $sender->getXpManager()->getXpLevel() - self::COST
            );
        }

        // Repair item
        $item->setDamage(0);
        $sender->getInventory()->setItemInHand($item);

        $sender->sendMessage("§aItem successfully repaired.");

        return true;
    }
}
