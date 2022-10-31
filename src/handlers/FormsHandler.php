<?php

namespace Ordness\Lobby\handlers;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use Ordness\Lobby\Core;
use Ordness\Lobby\utils\HotbarUtils;
use pocketmine\player\Player;

abstract class FormsHandler
{
    public static function gameForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, $data): void {
            match ($data) {
                "ctp" => HotbarUtils::loadCTPHotbar($player),
                "convoi" => $player->sendMessage(Core::PREFIX . "§cCe jeu n'est pas encore disponible."),
                default => null,
            };
        });
        $form->setTitle(Core::PREFIX . "§cMenu des Jeux");
        $form->addButton("§6Capture the Point", $form::IMAGE_TYPE_PATH, "textures/ui/icon_saleribbon", "ctp");
        $form->addButton("§cConvoi", $form::IMAGE_TYPE_PATH, "textures/items/minecart_chest", "convoi");
        $form->addButton("§4Fermer", $form::IMAGE_TYPE_PATH, "textures/ui/cancel", "close");
        $player->sendForm($form);
    }

    public static function teamsForm(Player $player): void
    {
        $form = new CustomForm(null);
        $blues = "";
        $reds = "";
        foreach (GamesHandler::getCTP()->getBlues() as $blue) {
            $blues .= "§f- §3$blue\n";
        }
        foreach (GamesHandler::getCTP()->getReds() as $red) {
            $reds .= "§f- §4$red\n";
        }
        $form->setTitle(Core::PREFIX . "§6Equipes");
        $form->addLabel("§r§7".(count(GamesHandler::getCTP()->getBlues()))." §fEquipe §bBleue:§r\n{$blues}§r§7".(count(GamesHandler::getCTP()->getReds()))." §fEquipe §cRouge:§r\n$reds");
        $player->sendForm($form);
    }


}