<?php

namespace Northrook\IconManager\Latte;

use Latte;
use Northrook\IconManager;
use Northrook\Logger\Log;

class IconManagerExtension extends Latte\Extension
{

    public function __construct(
        private readonly IconManager $iconManager,
    ) {}

    public function getFunctions() : array {
        return [ 'icon' => [ $this, 'getIcon' ], ];
    }

    public function getIcon( string $name ) : ?Latte\Runtime\HtmlStringable {
        try {
            $icon = $this->iconManager->getIcon( $name );
        }
        catch ( \Exception $exception ) {
            Log::exception( $exception );
            return null;
        }
        return $icon ? new Latte\Runtime\Html( $icon->toString() ) : null;
    }
}