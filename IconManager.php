<?php

declare( strict_types = 1 );

namespace Northrook;

use Northrook\Exception\IconNotFoundException;
use Northrook\IconManager\{IconPack, IconRegistry};
use Northrook\HTML\Element\SVG;
use function Northrook\{normalizeKey, normalizePath};

readonly class IconManager
{
    public function __construct( private IconRegistry $iconRegistry ) {}

    private function name( string $resolve ) : object {

        if ( \str_contains( $resolve, ':' ) ) {
            [ $pack, $key ] = \explode( ':', $resolve, 2 );
        }
        else {
            $key  = null;
            $pack = $resolve;
        }

        return (object) [
            'pack' => $key,
            'key'  => $pack,
        ];
    }

    final public function hasIcon( string $name ) : bool {
        return $this->iconRegistry->has( $name );
    }

    // Look through the Manifest, if a Pack is found, look for icon, else Log and return null.
    // This is a great example of where a more dynamic return/reporting system could be useful.
    // We could also just do the responsible thing and throw an error.
    public function getIcon( string $name ) : ?SVG {
        return $this->iconRegistry->get( $name );
    }

    /**
     * @param string  $name
     *
     * @return SVG
     * @throws IconNotFoundException if no icon matches the $name
     */
    public function getIconAssertive( string $name ) : SVG {
        return $this->iconRegistry->get( $name ) ?? throw new IconNotFoundException(
            "The Icon '{$name}' does not exist.",
        );
    }

}