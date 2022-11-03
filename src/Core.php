<?php

namespace Ordness\Lobby;

use mysqli;
use Ordness\Lobby\handlers\GamesHandler;
use Ordness\Lobby\listeners\EventListener;
use Ordness\Lobby\listeners\PlayerListener;
use Ordness\Lobby\tasks\CheckGame;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Core extends PluginBase
{
    public const PREFIX = "§l§eO§frdness §e» §r";
    public const COLOR_RED = 1;
    public const COLOR_BLUE = 2;
    use SingletonTrait;

    protected function onLoad(): void
    {
        $this::setInstance($this);
    }

    protected function onEnable(): void
    {
        if ($this->getDB()->ping()) $this->initDB();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        GamesHandler::init();
        $this->getScheduler()->scheduleRepeatingTask(new CheckGame(), 20);
    }


    /**
     * @return MySQLi
     */
    public function getDB(): MySQLi
    {
        return new MySQLi("45.145.166.29", "u14_fd44NLyfv3", "Y2G2jno4eC4e=KiZ+!sCbh9f", "s14_ordness", 3306);
    }

    /** @noinspection SqlWithoutWhere */
    private function initDB(): void
    {
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS Games(id INTEGER NOT NULL UNIQUE AUTO_INCREMENT, date DATETIME DEFAULT CURRENT_TIMESTAMP, started BIT NOT NULL DEFAULT 0, PRIMARY KEY(id));");
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS Teams(id INTEGER NOT NULL UNIQUE AUTO_INCREMENT, color INTEGER NOT NULL, id_game INTEGER NOT NULL, PRIMARY KEY(id), FOREIGN KEY(id_game) REFERENCES Games(id));");
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS Players(username char(15) NOT NULL UNIQUE, id_team INTEGER, PRIMARY KEY(username), FOREIGN KEY(id_team) REFERENCES Teams(id));");
        $this->getDB()->query("DELETE FROM Players;");
        $this->getDB()->query("DELETE FROM Teams;");
        $this->getDB()->query("DELETE FROM Games; ");
        $this->getLogger()->notice("Database initialized");
    }

}