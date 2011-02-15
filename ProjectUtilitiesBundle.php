<?php

namespace rs\ProjectUtilitiesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ProjectUtilitiesBundle extends Bundle
{
  
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }  
}
