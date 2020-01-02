<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Upgrade to 2.0.0 version.
 */
class UpgradeTo020000 implements DataPatchInterface, PatchVersionInterface
{    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config from srcPath to dstPath.
     *
     * @param string $srcPath
     * @param string $dstPath
     */
    private function moveConfig($srcPath, $dstPath)
    {
        $value = $this->scopeConfig->getValue($srcPath);

        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $this->moveConfig($srcPath . '/' . $k, $dstPath . '/' . $k);
            }
        } else {
            $connection = $this->moduleDataSetup->getConnection();
            $configData = $this->moduleDataSetup->getTable('core_config_data');
            $connection->update($configData, ['path' => $dstPath], 'path=' . $connection->quote($srcPath));
        }
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->moveConfig(
            'msp_securitysuite_twofactorauth/general/force_provider',
            'msp_securitysuite_twofactorauth/general/force_provider_0'
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpgradeTo010200::class,
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
