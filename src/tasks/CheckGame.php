<?php

namespace Ordness\Lobby\tasks;

use Ordness\Lobby\Core;
use Ordness\Lobby\handlers\GamesHandler;
use Ordness\Lobby\utils\HotbarUtils;
use pocketmine\color\Color;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\PotionSplashParticle;

final class CheckGame extends Task
{
    public function onRun(): void
    {
        if (count(GamesHandler::getCTP()?->getPlayers() ?? []) >= 2) {
            GamesHandler::getCTP()?->start();
        }
        foreach (GamesHandler::getCTP()?->getBlues() ?? [] as $blue){
            $blue = Server::getInstance()->getPlayerByPrefix($blue);
            $blue?->getWorld()->addParticle($blue->getEyePos()->add(0, 1, 0), new DustParticle(new Color(0, 0, 255)));
        }

        foreach (GamesHandler::getCTP()?->getReds() ?? [] as $red){
            $red = Server::getInstance()->getPlayerByPrefix($red);
            $red?->getWorld()->addParticle($red->getEyePos()->add(0, 1, 0), new DustParticle(new Color(255, 0, 0)));
        }

        foreach (GamesHandler::getCTP()?->getPlayers() ?? [] as $player) {
            Server::getInstance()->getPlayerByPrefix($player)?->sendActionBarMessage("§l§7[ §r§3Bleus: §b" . count(GamesHandler::getCTP()->getBlues()) . " §7§l| §r§4Rouges: §c" . count(GamesHandler::getCTP()->getReds()) . " §l§7]§r");
            if (time() - (HotbarUtils::$hasSwitch[$player] ?? time()) < 5) return;
            Server::getInstance()->getPlayerByPrefix($player)?->sendTip(Core::PREFIX."§6Capture The Point");
        }
    }
}