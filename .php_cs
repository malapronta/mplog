<?php

use Symfony\CS\FixerInterface;

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude(
        array(
            'Resources'
        )
    )
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->fixers(FixerInterface::ALL_LEVEL);
