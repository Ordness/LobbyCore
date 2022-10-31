<?php

namespace Ordness\Lobby\utils;

use pocketmine\player\Player;
use pocketmine\Server;

abstract class PosUtils
{
    public static function tpToSpawn(Player $player): void
    {
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }

}