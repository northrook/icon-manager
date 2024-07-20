<?php

namespace Northrook\IconManager\Compiler;

use Northrook\Filesystem\File;
use Northrook\HTML\Element\SVG;
use Northrook\HTML\HtmlNode;
use Northrook\IconManager\IconRegistryGenerator;
use Northrook\Support\Html;

/**
 * @internal
 */
final class DirectoryPack extends IconPackCompiler
{

    public function __construct(
        string $name,
        File   $source,
        int    $size,
    ) {
        $this->attributes[ 'height' ] = $size;
        $this->attributes[ 'width' ]  = $size;
        $this->iconPackName( $name );
        $this->parseDirectoryData( $source );
    }

    private function parseDirectoryData( File $source ) : void {

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
        $height = (int) ( $attributes[ 'height' ] ?? null ) ?: null;
        $width  = (int) ( $attributes[ 'width' ] ?? null ) ?: null;

        // If we hae a viewbox, parse it
        if ( isset( $attributes[ 'viewbox' ] ) ) {
            // Retrieve the height and width values
            $viewbox = SVG::parseViewbox( $attributes );

            if ( $height && $viewbox && $height !== $viewbox[ 'height' ] ) {
                throw new \LogicException(
                    "Invalid icon height declaration: height: $height !== viewbox: {$viewbox['height']}",
                );
            }

            if ( $width && $viewbox && $width !== $viewbox[ 'width' ] ) {
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
     * Retrieve an array of normalized {@see File} paths to all `.svg` files in the `$source` directory.
     *
     * @param File  $source
     *
     * @return File[]
     */
    private function getIconFiles( File $source ) : array {
        return \array_map(
            callback : static fn ( $path ) => new File( $path ),
            array    : \glob( "$source->path/*.svg" ) ?? [],
        );
    }
}