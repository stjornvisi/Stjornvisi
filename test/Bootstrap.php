<?php

namespace Stjornvisi;


use PDO;
use Stjornvisi\Auth\TestAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__.'/../../../');
$_SERVER['DOCUMENT_ROOT'] = getcwd();

class Bootstrap
{
	/** @var  ServiceManager */
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
		require_once 'DataHelper.php';
		require_once 'ArrayDataSet.php';
		require_once 'PDOMock.php';

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
		$baseConfig = [
            'module_listener_options' => [
				'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths),
            ],
        ];

		$config = ArrayUtils::merge($baseConfig, $testConfig);

		$module = new Module();
		$serviceManager = new ServiceManager(new ServiceManagerConfig($module->getServiceConfig()));
		$serviceManager->setAllowOverride(true);
		$serviceManager->setService('ApplicationConfig', $config);
		$serviceManager->get('ModuleManager')->loadModules();
		// Force our db config to the SM config
		$tmpConfig = $serviceManager->get('config');
		$tmpConfig['db'] = $config['db'];
		$serviceManager->setService('config', $tmpConfig);

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
	    require_once __DIR__ . '/../Module.php';

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

		require_once 'DatabaseTestCase.php';
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

    public static function authenticateUser($userId = 1, $isAdmin = 1)
    {
        /** @var AuthenticationService $auth */
        $auth = self::getServiceManager()->get(AuthenticationService::class);
        if ($userId < 1) {
            $auth->clearIdentity();
        }
        else {
            $result = $auth->authenticate(
                new TestAdapter(
                    (object)[
                        'id'       => $userId,
                        'is_admin' => $isAdmin,
                    ]
                )
            );
            $result->isValid();
        }
    }

    /**
     * @return PDO
     */
    public static function getConnection()
    {
        return self::getServiceManager()->get(PDO::class);
    }
}

Bootstrap::init();

