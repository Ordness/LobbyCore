<?php

namespace Ordness\Lobby\utils;

use Ordness\Lobby\Core;
use Ordness\Lobby\handlers\GamesHandler;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

abstract class HotbarUtils
{
    public static array $hasSwitch = [];
    public static function loadLobbyHotbar(Player $player): void
    {
        $items = [
            0 => VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem()->setCustomName("§7- §l§aJouer §r§7-"),
            2 => VanillaItems::FEATHER()->setCustomName("§7- §lBump §r§7-"),
            4 => VanillaItems::DIAMOND()->setCustomName("§7- §l§bCosmétiques §r§7-"),
            6 => VanillaItems::BOOK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1))->setCustomName("§7- §l§cStats §r§7-"),
            8 => VanillaItems::CLOCK()->setCustomName("§7- §l§eParamètres §r§7-"),
        ];
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        foreach ($items as $slot => $item) {
            $player->getInventory()->setItem($slot, $item);
        }
    }

    public static function loadCTPHotbar(Player $player): void
    {
        $items = [
            0 => VanillaBlocks::BARRIER()->asItem()->setCustomName("§7- §l§4Quitter §r§7-"),
            2 => VanillaItems::FEATHER()->setCustomName("§7- §lBump §r§7-"),
            4 => VanillaItems::TOTEM()->setCustomName("§7- §l§6Equipes §r§7-"),
            5 => VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE())->asItem()->setCustomName("§7- §l§fEquipe §bBleue §r§7-"),
            6 => VanillaBlocks::CONCRETE()->setColor(DyeColor::RED())->asItem()->setCustomName("§7- §l§fEquipe §cRouge §r§7-"),
            8 => VanillaItems::CLOCK()->setCustomName("§7- §l§eParamètres §r§7-"),
        ];
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        foreach ($items as $slot => $item) {
            $player->getInventory()->setItem($slot, $item);
        }
        $player->sendMessage(Core::PREFIX . "Vous avez rejoins le jeu §6Capture the Point§r.");
        count(GamesHandler::getCTP()->getBlues()) >= count(GamesHandler::getCTP()->getReds()) ?
            GamesHandler::getCTP()->addRed($player) :
            GamesHandler::getCTP()->addBlue($player);
    }

    public static function loadPreJoinHotbar(Player $player): void
    {
        $items = [
            4 => VanillaItems::FEATHER()->setCustomName("§7- §lBump §r§7-"),
        ];
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        foreach ($items as $slot => $item) {
            $player->getInventory()->setItem($slot, $item);
        }
    }

}