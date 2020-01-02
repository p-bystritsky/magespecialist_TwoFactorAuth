<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\DuoSecurity;

/**
 * Upgrade to 1.2.0 version.
 */
class UpgradeTo010200 implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config from srcPath to dstPath
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
            'msp_securitysuite/twofactorauth/allow_trusted_devices',
            'msp_securitysuite_twofactorauth/google/allow_trusted_devices'
        );

        $this->moveConfig(
            'msp_securitysuite/twofactorauth',
            'msp_securitysuite_twofactorauth/general'
        );

        // Generate random duo security key
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $this->config->saveConfig(DuoSecurity::XML_PATH_APPLICATION_KEY, $randomString, 'default', 0);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.2.0';
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
