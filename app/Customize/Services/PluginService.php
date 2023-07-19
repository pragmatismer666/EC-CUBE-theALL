<?php

namespace Customize\Services;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\PluginRepository;
use Eccube\Service\Composer\ComposerServiceInterface;
use Eccube\Service\EntityProxyService;
use Eccube\Service\PluginApiService;
use Eccube\Service\SchemaService;
use Eccube\Service\SystemService;
use Eccube\Util\CacheUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginService extends \Eccube\Service\PluginService
{
    public function isEnabled($code)
    {
        $Plugin = $this->pluginRepository->findOneBy([
            'enabled' => Constant::ENABLED,
            'code' => $code,
        ]);
        if ($Plugin) {
            return true;
        }

        return false;
    }
}
