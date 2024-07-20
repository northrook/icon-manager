<?php

declare ( strict_types = 1 );

namespace Northrook\IconManager;

use Northrook\HTML\Element\SVG;

final readonly class IconPack implements \Countable
{

    public function __construct(
        public string $name,
        private array $keys,
        private array $icons,
        private array $attributes,
        private array $meta,
    ) {}


    public function has( string $icon ) : bool {
        return isset( $this->keys[ $icon ] );
    }

    public function getKey( string $icon ) : ?string {
        return $this->keys[ $icon ] ?? null;
    }

    public function getIcon( string $icon ) : null | string | array {
        return $this->icons[ $this->getKey( $icon ) ] ?? null;
    }

    public function getElement( string $icon ) : ?SVG {

        if ( !$this->has( $icon ) ) {
            return null;
        }

        $icon = $this->icons[ $this->keys[ $icon ] ];

        if ( is_array( $icon ) ) {
            $innerHtml  = \array_shift( $icon );
            $attributes = array_merge( $this->attributes, $icon );
        }
        else {
            $innerHtml  = $icon;
            $attributes = $this->attributes;
        }

        return new SVG( $attributes, $innerHtml );
    }


    public function count() : int {
        return count( $this->icons );
    }
}