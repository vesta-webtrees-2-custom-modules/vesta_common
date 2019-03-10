<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('Vesta\\', __DIR__);
$loader->addPsr4('Vesta\\Hook\\HookInterfaces\\', __DIR__ . "/HookInterfaces");
$loader->addPsr4('Vesta\\Model\\', __DIR__ . "/model");
$loader->addPsr4('Vesta\\ControlPanel\\', __DIR__ . "/controlpanel");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model/elements");

$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");

$loader->register();

