<?php

/*
 * This file is part of the Sprovider90/package-builder.
 *
 * (c) Sprovider90 <sprovider90@163.cn>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sprovider90\Zhiyuanqueue;

use Sprovider90\Zhiyuanqueue\Commands\BuildCommand;
use Symfony\Component\Console\Application as BasicApplication;

/**
 * Class Application.
 *
 * @author Sprovider90 <sprovider90@163.cn>
 */
class Application extends BasicApplication
{
    /**
     * Application constructor.
     *
     * @param string $name
     * @param string $version
     */
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->add(new BuildCommand());
    }
}
