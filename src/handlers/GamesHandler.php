<?php

namespace Ordness\Lobby\handlers;

use Ordness\Lobby\objects\CTP;

abstract class GamesHandler
{
    private static ?CTP $ctp = null;

    public static function init(): void
    {
        self::$ctp = new CTP();
    }

    public static function getCTP(): ?CTP
    {
        return self::$ctp;
    }

    public static function setCtp(?CTP $ctp): void
    {
        self::$ctp = $ctp;
    }

}