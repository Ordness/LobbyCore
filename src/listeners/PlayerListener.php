<?php

namespace Ordness\Lobby\listeners;

use Ordness\Lobby\Core;
use Ordness\Lobby\handlers\FormsHandler;
use Ordness\Lobby\handlers\GamesHandler;
use Ordness\Lobby\utils\HotbarUtils;
use Ordness\Lobby\utils\PlayerUtils;
use Ordness\Lobby\utils\PosUtils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemIds;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

final class PlayerListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        PosUtils::tpToSpawn($player);
        HotbarUtils::loadLobbyHotbar($player);
        $player->setGamemode(GameMode::ADVENTURE());
        Core::getInstance()->getDB()->query("INSERT INTO Players(username) VALUES (\"{$player->getName()}\");");
    }

    public function onDrop(PlayerDropItemEvent $event): void
    {
        if ($event->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    public function onSlotChange(InventoryTransactionEvent $event): void
    {
        if ($event->getTransaction()->getSource()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $event->cancel();
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $event->cancel();
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        if ($event->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        if ($event->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        if ($event->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    public function onPickup(EntityItemPickupEvent $event): void
    {
        if ($event->getEntity() instanceof Player and $event->getEntity()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;
        $event->cancel();
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        match ($event->getItem()->getId()) {
            BlockLegacyIds::CONCRETE => match ($event->getItem()->getBlock()?->getColor()) {
                DyeColor::GREEN() => FormsHandler::gameForm($event->getPlayer()),
                DyeColor::RED() => GamesHandler::getCTP()->addRed($event->getPlayer()),
                DyeColor::BLUE() => GamesHandler::getCTP()->addBlue($event->getPlayer()),
                default => null,
            },
            ItemIds::FEATHER => PlayerUtils::bump($event->getPlayer()),
            ItemIds::TOTEM => FormsHandler::teamsForm($event->getPlayer()),
            -161 => GamesHandler::getCTP()->leaveGame($event->getPlayer()), // BARRIER
            default => null
        };
    }

    public function onItemHeld(PlayerItemHeldEvent $event): void {
        HotbarUtils::$hasSwitch[$event->getPlayer()->getName()] = time();
    }

}