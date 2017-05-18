<?php

namespace Arcanys\EasyAppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArcanysEasyAppBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
