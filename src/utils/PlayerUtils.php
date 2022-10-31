<?php

namespace Ordness\Lobby\utils;

use pocketmine\player\Player;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\sound\ArrowHitSound;
use pocketmine\world\sound\BowShootSound;
use pocketmine\world\sound\GhastShootSound;
use pocketmine\world\sound\GhastSound;

abstract class PlayerUtils
{
    public static function bump(Player $player): void
    {
        if (!$player->isOnGround()) return;
        $directionVector = $player->getDirectionVector();
        $motion = $player->getMotion();
        $motion->x += $directionVector->x;
        $motion->y = 0.5;
        $motion->z += $directionVector->z;
        $player->setMotion($motion);
        $player->getWorld()->addParticle($player->getPosition(), new ExplodeParticle());
        $player->broadcastSound(new GhastShootSound());
    }

}