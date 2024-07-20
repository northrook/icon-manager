<?php

namespace Northrook\IconManager\Compiler;

use Northrook\Core\Trait\PropertyAccessor;
use Northrook\IconManager\IconPack;
use Northrook\Logger\Log;
use function Northrook\{
    classBasename,
    filterHtmlText,
    normalizeKey,
    normalizeUrl,
    escapeHtmlText,
    escapeText,
    escapeUrl
};

abstract class IconPackCompiler
{

    protected string $name;
    protected array  $keys       = [];
    protected array  $icons      = [];
    protected array  $attributes = [];
    protected array  $meta       = [];

    public function getIconPack() : IconPack {
        return new IconPack(
            $this->name,
            $this->keys,
            $this->icons,
            $this->attributes,
            $this->meta,
        );
    }

    public function getKeys() : array {
        return $this->keys;
    }

    public function getIcons() : array {
        return $this->icons;
    }


    final protected function iconPackName( string $name ) : void {
        $this->name = normalizeKey( $name );
    }

    public function __destruct() {
        Log::info( $this::class . ' got destroyed' );
    }
}