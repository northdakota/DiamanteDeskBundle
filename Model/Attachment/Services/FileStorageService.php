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
namespace Diamante\DeskBundle\Model\Attachment\Services;

/**
 * Interface FileStorageService
 * @package Diamante\DeskBundle\Model\Attachment\Services
 * @codeCoverageIgnore
 */
interface FileStorageService
{
    /**
     * Upload (create) file
     * @param string $filename filename path
     * @param string $contents content to be put in file
     * @return string path to file
     */
    public function upload($filename, $contents);

    /**
     * Remove given file
     * @param $filename
     * @return void
     */
    public function remove($filename);

    /**
     * @return string
     */
    public function getConfiguredUploadDir();
}
