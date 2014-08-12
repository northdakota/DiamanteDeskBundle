<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Eltrino\DiamanteDeskBundle\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * Updates DB Schema. Changes from Diamante only will be applied for current schema. Other bundles updating skips
     * @throws \Exception if there are no changes in entities
     */
    protected function updateDbSchema()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $schemaTool = new SchemaTool($em);
        $entitiesMetadata = array(
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Branch::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Ticket::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Comment::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Filter::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Attachment::getClassName())
        );

        $sql = $schemaTool->getUpdateSchemaSql($entitiesMetadata);
        $sql2 = $schemaTool->getUpdateSchemaSql(array());

        $toUpdate = array_diff($sql, $sql2);

        if (empty($toUpdate)) {
            throw new \Exception('No new updates found. DiamanteDesk is up to date!');
        }

        $conn = $em->getConnection();

        foreach ($toUpdate as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Install assets
     * @param OutputInterface $output
     */
    protected function assetsInstall(OutputInterface $output)
    {
        $this->runExistingCommand('assets:install', $output);
    }

    /**
     * Dump assetic
     * @param OutputInterface $output
     * @param array $params
     */
    protected function asseticDump(OutputInterface $output, array $params = array())
    {
        $this->runExistingCommand('assetic:dump', $output, $params);
    }

    /**
     * Update oro navigation
     * @param OutputInterface $output
     */
    protected function updateNavigation(OutputInterface $output)
    {
        $this->runExistingCommand('oro:navigation:init', $output);
    }

    /**
     * Run existing command in system
     * @param string $commandName
     * @param OutputInterface $output
     * @param array $parameters
     */
    protected function runExistingCommand($commandName, OutputInterface $output, array $parameters = array())
    {
        $command = $this->getApplication()->find($commandName);

        $arguments = array(
            'command' => $commandName
        );

        $arguments = array_merge($arguments, $parameters);

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
    }
}