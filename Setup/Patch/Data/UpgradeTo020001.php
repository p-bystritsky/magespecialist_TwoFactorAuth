<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Setup\Patch\Data;

use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Upgrade to 2.0.1 version.
 */
class UpgradeTo020001 implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var File
     */
    private $file;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SerializerInterface $serializer
     * @param File $file
     * @param Reader $moduleReader
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SerializerInterface $serializer,
        File $file,
        Reader $moduleReader
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleReader = $moduleReader;
        $this->file = $file;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('msp_tfa_country_codes');

        $countryCodesJsonFile =
            $this->moduleReader->getModuleDir(false, 'MSP_TwoFactorAuth') . DIRECTORY_SEPARATOR . 'Setup' .
            DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'country_codes.json';

        $countryCodesJson = $this->file->read($countryCodesJsonFile);

        $countryCodes = $this->serializer->unserialize(trim($countryCodesJson));

        // @codingStandardsIgnoreStart
        foreach ($countryCodes as $countryCode) {
            $connection->insert($tableName, $countryCode);
        }
        // @codingStandardsIgnoreEnd

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpgradeTo010200::class,
            UpgradeTo020000::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
