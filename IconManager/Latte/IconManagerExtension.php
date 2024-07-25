<?php

namespace Northrook\IconManager\Latte;

use Latte;
use Northrook\IconManager;

class IconManagerExtension extends Latte\Extension
{

    public function __construct(
        private readonly IconManager $iconManager,
    ) {}

    public function getFunctions() : array {
        return [ 'icon' => [ $this, 'getIcon' ], ];
    }

    public function getIcon( string $name ) : ?Latte\Runtime\HtmlStringable {
        $icon = $this->iconManager->getIcon( $name );
        return $icon ? new Latte\Runtime\Html( $icon->toString() ) : null;
    }
}