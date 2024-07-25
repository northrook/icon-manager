<?php

namespace Northrook\IconManager;

// Results in:
// am IconManifest using PersistentEntity
// one or more Icon Packs using PersistentEntity

use Northrook\Compressor;
use Northrook\Filesystem\File;
use Northrook\IconManager\Compiler\DirectoryPack;
use Northrook\IconManager\Compiler\JsonPack;
use Northrook\Logger\Log;
use Northrook\Resource\Path;
use function Northrook\hashKey;
use function Northrook\normalizeKey;
use function Northrook\normalizePath;

class IconRegistryGenerator
{
    private array $sources = [];
    private array $packs   = [];


    public function __construct(
        public readonly string $iconStorageDirectory,
        array                  $sources = [],
        public readonly int    $height = 24,
    ) {
        foreach ( $sources as $name => $path ) {
            $this->addSource( $name, $path );
        }
    }

    /**
     * Accepts a path to a directory of .svg files, or path to a .json file
     *
     * @param string  $name
     * @param string  $path
     *
     * @return $this
     */
    public function addSource( string $name, string $path ) : IconRegistryGenerator {
        $this->sources[ normalizeKey( $name ) ] = normalizePath( $path );
        return $this;
    }

    public function createRegistry() : self {
        $registry = new IconRegistry(
            [ 'manifest' => $this->packs ],
        );

        // dump($registry);
        $registry->save();

        return $this;
    }

    public function compile() : IconRegistryGenerator {
        Log::info( 'Started compiling icon packs.' );
        foreach ( $this->sources as $name => $path ) {
            $source = new Path( $path );
            if ( !$source->exists ) {
                Log::warning( "The '$name' icon source does not exist. " );
                continue;
            }

            if ( $source->extension === 'json' ) {
                $generator = new JsonPack( $name, $source );
            }
            else {
                $generator = new DirectoryPack( $name, $source, $this->height );
            }

            $iconPack = $this->saveSerializedIconPack( $generator->getIconPack() );

            $this->packs[ $name ] = $iconPack->path;
        }
        Log::notice( 'Finished compiling icon packs.' );
        return $this;
    }

    private function saveSerializedIconPack( IconPack $iconPack ) : Path {
        $path = new Path( "$this->iconStorageDirectory/icons/$iconPack->name.iconpack" );
        $pack = \serialize( $iconPack );

        File::save( $path, $pack );

        return $path;
    }
}