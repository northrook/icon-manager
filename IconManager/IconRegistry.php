<?php

namespace Northrook\IconManager;

use Northrook\Exception\IconNotFoundException;
use Northrook\Filesystem\File;
use Northrook\HTML\Element\SVG;
use Northrook\Storage\PersistentEntity;

final class IconRegistry extends PersistentEntity
{

    private array $inMemoryIconPacks = [];

    protected mixed $data = [
        'manifest' => [], // Holds a list of all available icon packs as {name: path}
        'icons'    => [], // All preloaded icons, as {pack:name: SVG}
    ];

    public function __construct( array $data = [] ) {
        parent::__construct(
            name : IconRegistry::class,
            data : $data,
        );
    }

    private function loadSerializedIconPack( string $name ) : IconPack {
        $path = new File( $this->data[ 'manifest' ][ $name ] );

        if ( !$path->isReadable ) {
            throw new IconNotFoundException();
        }

        return \unserialize( $path->read );
    }

    public function getIconElement( string $pack, string $key ) : ?SVG {
        return $this->data[ 'icons' ][ $pack ][ $key ] ??= $this->getIconPack( $pack )->getElement( $key );
    }

    public function getIconPack( string $name ) : IconPack {
        return $this->inMemoryIconPacks[ $name ] ??= $this->loadSerializedIconPack( $name );
    }

    public function getAllPacks() : array {
        return $this->data[ 'manifest' ] ?? [];
    }

    public function has( string $name ) : bool {
        $icon = $this->icon( $name );
        if ( !isset( $this->data[ 'manifest' ][ $icon->pack ] ) ) {
            return false;
        }
        return $this->getIconPack( $icon->pack )->has( $icon->key );
    }

    public function get( $packName ) : ?SVG {
        $icon = $this->icon( $packName );
        return $this->getIconElement( $icon->pack, $icon->key );
    }


    public static function hydrate( array $entityArray ) : PersistentEntity {
        $registry           = new IconRegistry( $entityArray[ 'data' ] );
        $registry->autosave = true;
        return $registry;
    }


    private function icon( string $resolve ) : object {

        if ( \str_contains( $resolve, ':' ) ) {
            [ $pack, $key ] = \explode( ':', $resolve, 2 );
        }
        else {
            $key  = null;
            $pack = $resolve;
        }


        return (object) [
            'pack' => $pack,
            'key'  => $key,
        ];
    }
}