<?php

namespace Northrook\IconManager\Compiler;

use Northrook\Filesystem\File;
use Northrook\Logger\Log;
use function Northrook\arrayFilterRecursive;
use function Northrook\escapeText;
use function Northrook\escapeUrl;

class JsonPack extends IconPackCompiler
{
    public function __construct(
        string $name,
        File   $source,
    ) {
        $this->iconPackName( $name );
        $this->parseJsonData( $source );
    }

    private function parseJsonData( File $source ) : void {

        $iconify = json_decode( $source->read, true );

        $icons  = $iconify[ 'icons' ] ?? false;
        $height = $iconify[ 'height' ] ?? false;
        $width  = $iconify[ 'width' ] ?? $height ?? false;

        if ( !$icons ) {
            throw new \LogicException( 'Provided JSON file does not contain any icons.' );
        }

        if ( !$height || !$width ) {
            throw new \LogicException( 'Provided JSON file does not contain prefix.' );
        }

        $this->attributes[ 'height' ] = $height;
        $this->attributes[ 'width' ]  = $width;

        $this->parseIconifyData( $icons, $iconify[ 'aliases' ] ?? [] );

        $meta = $iconify[ 'info' ] ?? false;

        if ( !$meta ) {
            return;
        }

        $this->meta = arrayFilterRecursive(
            [
                'author'  => [
                    'name' => escapeText( $meta[ 'author' ][ 'name' ] ?? null ),
                    'url'  => escapeUrl( $meta[ 'author' ][ 'url' ] ?? null ),
                ],
                'license' => [
                    'title' => escapeText( $meta[ 'license' ][ 'title' ] ?? null ),
                    'spdx'  => escapeText( $meta[ 'license' ][ 'spdx' ] ?? null ),
                    'url'   => escapeUrl( $meta[ 'license' ][ 'url' ] ?? null ),
                ],
                'palette' => $meta[ 'palette' ] ?? false,
            ],
        );
    }

    private function parseIconifyData( array $icons, array $aliases = [] ) : void {

        $aliases = $this->parseIconAliases( $aliases );

        foreach ( $icons as $primaryKey => $iconData ) {
            $iconSvg = $iconData[ 'body' ];
            if ( !$iconSvg ) {
                throw new \LogicException(
                    "Error parsing the {$this->name} icon pack, the icon {$primaryKey} does not contain a body.",
                );
            }
            $this->keys[ $primaryKey ] = count( $this->icons );
            if ( array_key_exists( $primaryKey, $aliases ) ) {
                $this->keys[ $aliases[ $primaryKey ] ] = count( $this->icons );
            }
            $this->icons[] = $iconSvg;
        }

    }

    private function parseIconAliases( array $array ) : array {
        foreach ( $array as $aliasKey => $alias ) {
            if ( isset( $alias[ 'parent' ] ) ) {
                $array[ $alias[ 'parent' ] ] = $aliasKey;
            }
            unset( $array[ $aliasKey ] );
        }
        return $array;
    }

}