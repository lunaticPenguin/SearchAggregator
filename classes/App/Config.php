<?php

namespace App;

final class Config
{
    protected static $hashConfiguration = null;

    /**
     * Renvoie la valeur d'une clef de config
     * @param string $strKey
     * @param mixed $mixedValue
     * @return mixed, null par défaut si non trouvé
     *
     * @throws \ErrorException si fichier de configuration introuvable
     */
    public static function getValue($strKey = null, $mixedValue = null)
    {
        if (is_null(self::$hashConfiguration)) {
            $strFilePath = dirname('.') . '/../assets/config/conf.php';
            if (!file_exists($strFilePath)) {
                throw new \ErrorException(sprintf("Configuration file [%s] not found.", $strFilePath));
            }
            self::$hashConfiguration = include_once $strFilePath;
        }

        if (array_key_exists($strKey, self::$hashConfiguration)) {
            return self::$hashConfiguration[$strKey];
        }
        return $mixedValue;
    }
}
