<?php

namespace Ordness\Lobby\objects;

use Ordness\Lobby\Core;
use Ordness\Lobby\handlers\GamesHandler;
use Ordness\Lobby\utils\HotbarUtils;
use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpLevelUpSound;
use Voltage\Api\module\BossBar;

final class CTP
{
    private array $blues = [];
    private array $reds = [];
    private ClosureTask $task;
    private BossBar $bossBar;

    public function __construct()
    {
        $this->bossBar = new BossBar();
        $this->bossBar->setColorToAll(BossBarColor::YELLOW);
    }

    /**
     * @return BossBar
     */
    public function getBossBar(): BossBar
    {
        return $this->bossBar;
    }

    /**
     * @return array
     */
    public function getBlues(): array
    {
        return $this->blues;
    }

    /**
     * @return array
     */
    public function getReds(): array
    {
        return $this->reds;
    }

    public function addBlue(Player $player): void
    {
        if (in_array($player->getName(), $this->reds)) {
            unset($this->reds[array_search($player->getName(), $this->reds)]);
        } else if (in_array($player->getName(), $this->blues)) {
            $player->sendMessage(Core::PREFIX . "Vous êtes déjà dans l'équipe §bbleue§r.");
            return;
        }
        if (count($this->blues) >= 1) {
            $player->sendMessage(Core::PREFIX . "L'équipe §bbleue §rest pleine. Je vous redirige vers l'équipe rouge..");
            $this->addRed($player);
            return;
        }
        $this->blues[] = $player->getName();
        $player->sendMessage(Core::PREFIX . "Vous avez rejoins l'équipe §bbleue §r! §7(§b" . count($this->blues) . "§7/§c" . count($this->reds) . "§7)");
        $instrument = NoteInstrument::PIANO();
        $player->broadcastSound(new NoteSound($instrument, 120));
        $player->getWorld()->addParticle($player->getPosition(), new PotionSplashParticle(new Color(0, 0, 255)));
    }

    public function addRed(Player $player): void
    {
        if (in_array($player->getName(), $this->blues)) {
            unset($this->blues[array_search($player->getName(), $this->blues)]);
        } else if (in_array($player->getName(), $this->reds)) {
            $player->sendMessage(Core::PREFIX . "Vous êtes déjà dans l'équipe §crouge§r.");
            return;
        }
        if (count($this->reds) >= 1) {
            $player->sendMessage(Core::PREFIX . "L'équipe §crouge §rest pleine. Je vous redirige vers l'équipe bleue..");
            $this->addBlue($player);
            return;
        }
        $this->reds[] = $player->getName();
        $player->sendMessage(Core::PREFIX . "Vous avez rejoins l'équipe §crouge §r! §7(§b" . count($this->blues) . "§7/§c" . count($this->reds) . "§7)");
        $instrument = NoteInstrument::PIANO();
        $player->broadcastSound(new NoteSound($instrument, $instrument->getMagicNumber()));
        $player->getWorld()->addParticle($player->getPosition(), new PotionSplashParticle(new Color(255, 0, 0)));
        $this->start();
    }

    public function leaveGame(Player $player): void
    {
        if (in_array($player->getName(), $this->blues)) {
            unset($this->blues[array_search($player->getName(), $this->blues)]);
        } else if (in_array($player->getName(), $this->reds)) {
            unset($this->reds[array_search($player->getName(), $this->reds)]);
        }
        $player->sendMessage(Core::PREFIX . "Vous avez quitté le §6Capture The Point§r.");
        HotbarUtils::loadLobbyHotbar($player);
    }

    public function start(): void
    {
        $id = $this->createGame();
        $this->createTeams($id);
        GamesHandler::init();
    }

    public function createGame(): int
    {
        $db = Core::getInstance()->getDB();
        $db->query("INSERT INTO Games(date) VALUES (CURRENT_TIMESTAMP);");
        return $db->insert_id;
    }

    public function getPlayers(): array
    {
        return array_merge($this->blues, $this->reds);
    }

    private function createTeams(int $id_game): void
    {
        $db = Core::getInstance()->getDB();
        $blue = Core::COLOR_BLUE;
        $red = Core::COLOR_RED;
        $db->query("INSERT INTO Teams(color, id_game) VALUES ($blue, $id_game), ($red, $id_game);");
        $blue_id = $db->insert_id;
        $red_id = $blue_id + 1;
        $this->insertPlayers($blue_id, $red_id);
    }

    private function insertPlayers(int $blue_id, int $red_id): void
    {
        $db = Core::getInstance()->getDB();
        foreach ($this->getReds() as $red) {
            $db->query("UPDATE Players SET id_team=$red_id WHERE username=\"$red\";");
        }
        foreach ($this->getBlues() as $blue) {
            $db->query("UPDATE Players SET id_team=$blue_id WHERE username=\"$blue\";");
        }
        $decount = 10;
        $closure = function () use (&$decount) {
            if ($decount === 0) {
                $this->preTeleportPlayers();
                $decount--;
            } else if ($decount === -1) {
                $this->teleportPlayers();
                $this->task->getHandler()->cancel();
            } else {
                $this->decountPlayers($decount);
                $decount--;
            }
        };
        $this->task = new ClosureTask($closure);
        Core::getInstance()->getScheduler()->scheduleRepeatingTask($this->task, 20);
        foreach ($this->getPlayers() as $player) {
            if($p = Server::getInstance()->getPlayerByPrefix($player)){
                HotbarUtils::loadPreJoinHotbar($p);
            }
        }
    }

    private function teleportPlayers(): void
    {
        foreach ($this->getPlayers() as $player) {
            Server::getInstance()->getPlayerByPrefix($player)?->transfer("play.nyrok.fr", 19145);
        }
    }

    private function decountPlayers(int $decount): void
    {
        foreach ($this->getPlayers() as $player) {
            $player = Server::getInstance()->getPlayerByPrefix($player);
            if(!$this->bossBar->hasPlayer($player)) $this->bossBar->addPlayer($player);
            $player?->sendActionBarMessage(Core::PREFIX . "§fLe §6Capture The Point §fcommence dans §e$decount §fsecondes.");
            $player?->broadcastSound(new PopSound());
        }
        $this->bossBar->setTitleToAll(Core::PREFIX . "§fLe §6Capture The Point");
        $this->bossBar->setSubTitleToAll("              §e$decount §fsecondes");
        $this->bossBar->setPercentageToAll($decount / 10);
        $this->bossBar->sendToAll();
    }

    private function preTeleportPlayers(): void
    {
        foreach ($this->getPlayers() as $player) {
            $player = Server::getInstance()->getPlayerByPrefix($player);
            $player?->sendActionBarMessage(Core::PREFIX . "§fTéléportation §een cours§f...");
            $player?->broadcastSound(new XpLevelUpSound(5));
        }
        $this->bossBar->setSubTitleToAll("              §eTéléportation...");
        $this->bossBar->setPercentageToAll(0);
        $this->bossBar->sendToAll();
    }
}