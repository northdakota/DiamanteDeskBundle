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
namespace Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Diamante\DeskBundle\Model\Branch\Logo;

class BranchLogoType extends StringType
{
    const BRANCH_LOGO = 'branch_logo';
    const DELIMITER = '|';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::BRANCH_LOGO;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $object = null;
        if (!empty($value)) {
            list($name, $originalName) = explode(self::DELIMITER, $value);
            $object = new Logo($name, $originalName);
        }
        return $object;
    }

    /**
     * @param Logo $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     * @throws \RuntimeException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return '';
        }
        if (false === ($value instanceof Logo)) {
            throw new \RuntimeException("Variable type validation failed, value should be a Logo type");
        }

        $logo = null;
        $name = $value->getName();
        $originalName = $value->getOriginalName();

        if ($name && $originalName) {
            $logo = $name . self::DELIMITER . $originalName;
        }

        return parent::convertToDatabaseValue($logo, $platform);
    }

    public function canRequireSQLConversion()
    {
        return false;
    }

    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return parent::convertToPHPValueSQL($sqlExpr, $platform);
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValueSQL($sqlExpr, $platform);
    }
}
