<?php

declare( strict_types = 1 );

namespace Northrook;

use Northrook\Exception\IconNotFoundException;
use Northrook\IconManager\{IconPack, IconRegistry};
use Northrook\HTML\Element;
use function Northrook\{normalizeKey, normalizePath};

readonly class IconManager
{
    public function __construct(
        private IconRegistry $iconRegistry,
        private ?string      $defaultIconPack = null,
    ) {}

    private function name( string $resolve ) : array {

        if ( \str_contains( $resolve, ':' ) ) {
            [ $pack, $key ] = \explode( ':', $resolve, 2 );
        }
        else {
            $key  = $this->defaultIconPack;
            $pack = $resolve;
        }

        return [
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
    public function getIcon( string $name ) : ?Element {
        return $this->iconRegistry->getIconElement( ... $this->name( $name ) );
    }

    /**
     * @param string  $name
     *
     * @return Element
     * @throws IconNotFoundException if no icon matches the $name
     */
    public function getIconAssertive( string $name ) : Element {
        return $this->iconRegistry->getIconElement( ... $this->name( $name ) ) ?? throw new IconNotFoundException(
            "The Icon '{$name}' does not exist.",
        );
    }

}