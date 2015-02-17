<?php

namespace Stjornvisi;


use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__.'/../../../');

class Bootstrap
{
	protected static $serviceManager;
	protected static $config;
	protected static $bootstrap;

	public static function init()
	{
		// Load the user-defined test configuration file, if it exists; otherwise, load
		if (is_readable(__DIR__ . '/TestConfig.php')) {
			$testConfig = include __DIR__ . '/TestConfig.php';
		} else {
			$testConfig = include __DIR__ . '/TestConfig.php.dist';
		}

		$zf2ModulePaths = array();

		if (isset($testConfig['module_listener_options']['module_paths'])) {
			$modulePaths = $testConfig['module_listener_options']['module_paths'];
			foreach ($modulePaths as $modulePath) {
				if (($path = static::findParentPath($modulePath)) ) {
					$zf2ModulePaths[] = $path;
				}
			}
		}

		$zf2ModulePaths  = implode(PATH_SEPARATOR, $zf2ModulePaths) . PATH_SEPARATOR;
		$zf2ModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ?: (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

		static::initAutoloader();

		// use ModuleManager to load this module and it's dependencies
		$baseConfig = array(
			'module_listener_options' => array(
				'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths),
			),
		);

		$config = ArrayUtils::merge($baseConfig, $testConfig);

		$serviceManager = new ServiceManager(new ServiceManagerConfig());
		$serviceManager->setService('ApplicationConfig', $config);
		$serviceManager->get('ModuleManager')->loadModules();

		static::$serviceManager = $serviceManager;
		static::$config = $config;
	}

	public static function getServiceManager()
	{
		return static::$serviceManager;
	}

	public static function getConfig()
	{
		return static::$config;
	}

	protected static function initAutoloader()
	{
		$vendorPath = static::findParentPath('vendor');

		if (is_readable($vendorPath . '/autoload.php')) {
			$loader = include $vendorPath . '/autoload.php';
		} else {
			$zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/ZF2/library') ? $vendorPath . '/ZF2/library' : false));

			if (!$zf2Path) {
				throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
			}

			include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';

		}

		AutoloaderFactory::factory(array(
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true,
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
				),
			),
		));
	}

	protected static function findParentPath($path)
	{
		$dir = __DIR__;
		$previousDir = '.';
		while (!is_dir($dir . '/' . $path)) {
			$dir = dirname($dir);
			if ($previousDir === $dir) return false;
			$previousDir = $dir;
		}
		return $dir . '/' . $path;
	}
}

Bootstrap::init();



/*
use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);


class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $config = array(
            'module_listener_options' => array(
                'module_paths' => $zf2ModulePaths,
            ),
            'modules' => array(
                'Stjornvisi'
            )
        );

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        $zf2Path = getenv('ZF2_PATH');
        if (!$zf2Path) {
            if (defined('ZF2_PATH')) {
                $zf2Path = ZF2_PATH;
            } elseif (is_dir($vendorPath . '/ZF2/library')) {
                $zf2Path = $vendorPath . '/ZF2/library';
            } elseif (is_dir($vendorPath . '/zendframework/zendframework/library')) {
                $zf2Path = $vendorPath . '/zendframework/zendframework/library';
            }
        }

        if (!$zf2Path) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
        }

        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();

*/