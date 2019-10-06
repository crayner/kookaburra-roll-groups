<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 5/10/2019
 * Time: 18:09
 */

namespace Kookaburra\RollGroup;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class KookaburraRollGroupBundle
 * @package Kookaburra\RollGroup
 */
class KookaburraRollGroupBundle extends Bundle
{
    /**
     * build
     * @param ContainerBuilder $container
     */
     public function build(ContainerBuilder $container)
     {
         parent::build($container);
     }
}