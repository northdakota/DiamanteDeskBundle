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
namespace Diamante\DeskBundle\Api\Command;

use Diamante\DeskBundle\Entity\Branch;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\TagBundle\Entity\Taggable;

class UpdateTicketCommand implements Taggable
{
    /**
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     */
    public $id;

    /**
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Type(type="string")
     */
    public $key;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     */
    public $subject;

    /**
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Type(type="string")
     */
    public $description;

    /**
     * @Assert\NotNull()
     */
    public $status;

    /**
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Type(type="object")
     */
    public $reporter;

    /**
     * @Assert\Type(type="object")
     */
    public $assignee;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     */
    public $priority;

    /**
     * @Assert\NotNull()
     * @assert\Type(type="string")
     */
    public $source;

    /**
     * @var \Diamante\DeskBundle\Api\Dto\AttachmentInput
     */
    public $attachmentsInput;

    /**
     * @var Branch
     */
    public $branch;

    /**
     * @Assert\Type(type="array")
     */
    public $tags;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->id;
    }

    /**
     * Set tag collection
     *
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }
}
