<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MSP\TwoFactorAuth\Setup\Operation\EncryptConfiguration;

/**
 * Upgrade to 3.1.0 version.
 */
class UpgradeTo030100 implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EncryptConfiguration
     */
    private $encryptConfiguration;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EncryptConfiguration $encryptConfiguration
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EncryptConfiguration $encryptConfiguration
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->encryptConfiguration = $encryptConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->encryptConfiguration->execute($this->moduleDataSetup);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '3.1.0';
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpgradeTo010200::class,
            UpgradeTo020000::class,
            UpgradeTo020001::class,
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
