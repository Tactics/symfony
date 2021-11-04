<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::CODE_QUALITY);

    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::PHP_54);
    $containerConfigurator->import(SetList::PHP_55);
    $containerConfigurator->import(SetList::PHP_56);
    $containerConfigurator->import(SetList::PHP_70);
    $containerConfigurator->import(SetList::PHP_71);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::PHP_74);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTOLOAD_PATHS, []);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/lib',
    ]);

    $parameters->set(Option::SKIP, []);

};


