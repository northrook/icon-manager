<?php

namespace Northrook\IconManager\Compiler;

use Northrook\Filesystem\File;
use Northrook\HTML\HtmlNode;
use Northrook\IconManager\IconRegistryGenerator;
use Northrook\Resource\Path;

/**
 * @internal
 */
final class DirectoryPack extends IconPackCompiler
{

    public function __construct(
        string $name,
        Path   $source,
        int    $size,
    ) {
        $this->attributes[ 'height' ] = $size;
        $this->attributes[ 'width' ]  = $size;
        $this->iconPackName( $name );
        $this->parseDirectoryData( $source );
    }

    private function parseDirectoryData( Path $source ) : void {

        foreach ( $this->getIconFiles( $source ) as $source ) {
            if ( !$source->isReadable ) {
                throw new \LogicException( 'Provided icon source file is not readable.' );
            }

            $key  = count( $this->icons );
            $svg  = new HtmlNode( $source->read );
            $size = $this->parseNodeSize( $svg->attributes );

            $this->keys[ $source->filename ] = $key;

            $this->icons[ $key ] = $size === true
                ? $svg->innerHtml
                : [
                    'innerHtml' => $svg->innerHtml,
                    ... $size,
                ];
        }
    }

    /**
     * @param array  $attributes
     *
     * @return true|array{'height': int, 'width':int}
     */
    private function parseNodeSize( array $attributes ) : true | array {

        // Get an array of the size related keys
        $attributes = \array_intersect_key( $attributes, \array_flip( [ 'height', 'width', 'viewbox' ] ) );

        // Try using absolute size values
        $height  = (int) ( $attributes[ 'height' ] ?? null ) ?: null;
        $width   = (int) ( $attributes[ 'width' ] ?? null ) ?: null;
        $viewbox = $this->parseViewbox( $attributes );

        // If we hae a viewbox, parse it
        if ( $viewbox ) {

            if ( $height && $height !== $viewbox[ 'height' ] ) {
                throw new \LogicException(
                    "Invalid icon height declaration: height: $height !== viewbox: 
                    {$viewbox['height']}",
                );
            }

            if ( $width && $width !== $viewbox[ 'width' ] ) {
                throw new \LogicException(
                    "Invalid icon width declaration: width: $width !== viewbox: {$viewbox['width']}",
                );
            }

            $height ??= $viewbox[ 'height' ];
            $width  ??= $viewbox[ 'width' ];
        }

        if ( $height === $width && $height === $this->attributes[ 'height' ] ) {
            return true;
        }

        return [
            'height' => $height,
            'width'  => $width,
        ];
    }


    /**
     * Retrieve an array of normalized {@see Path}s to all `.svg` files in the `$source` directory.
     *
     * @param Path  $source
     *
     * @return Path[]
     */
    private function getIconFiles( Path $source ) : array {
        return \array_map(
            callback : static fn ( $path ) => new Path( $path ),
            array    : \glob( "$source->path/*.svg" ) ?? [],
        );
    }

    /**
     * @param array  $attributes
     *
     * @return null|array{'min-x':int, 'min-y':int, 'width':int, 'height':int }
     */
    private function parseViewbox( array $attributes ) : ?array {

        if ( !isset( $attributes[ 'viewbox' ] ) ) {
            return null;
        }

        $viewbox = \explode( ' ', $attributes[ 'viewbox' ] );

        if ( \count( $viewbox ) !== 4 ) {
            throw new \LogicException(
                'The SVG viewbox attribute is malformed. It should contain 4 values, but ' .
                \count( $viewbox, ) . ' was found.',
            );
        }

        return \array_combine(
            [ 'min-x', 'min-y', 'width', 'height' ],
            \array_map( 'intval', $viewbox ),
        );
    }
}