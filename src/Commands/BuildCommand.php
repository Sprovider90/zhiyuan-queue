<?php

/*
 * This file is part of the Sprovider90/package-builder.
 *
 * (c) Sprovider90 <sprovider90@163.cn>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sprovider90\Zhiyuanqueue\Commands;

use Sprovider90\Zhiyuanqueue\Factory\CommandFactory;
use Sprovider90\Zhiyuanqueue\Logic\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand.
 *
 * @author Sprovider90 <sprovider90@163.cn>
 */
class BuildCommand extends Command
{


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('run some queue to zhiyuan-pro')
            ->addArgument(
                'taskname',
                InputArgument::OPTIONAL,
                'taskname for logicrun'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optional_argument = $input->getArgument('taskname');
        if(in_array($optional_argument,["Message","WarningSms","PhoneNotice"])){
            $obj="Sprovider90\Zhiyuanqueue\Logic\\".$optional_argument;
            $fac=new CommandFactory(new $obj());
            $fac->run();
        }

    }
}
