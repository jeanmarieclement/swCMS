<?php

namespace App\Core;

/**
 * Custom Autoloader for swCMS
 * Handles class loading without relying on Composer
 */
class Autoloader
{
    /**
     * Register the autoloader
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    /**
     * Load a class
     *
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    public static function loadClass($className)
    {
        // Only handle App\ classes and unnamespaced legacy classes.
        // Third-party namespaced classes (Smarty\, PHPUnit\, etc.) must be
        // resolved by Composer's autoloader — bail early to avoid false
        // constants lookups that throw fatal errors on PHP 8.
        if (strpos($className, '\\') !== false && strpos($className, 'App\\') !== 0) {
            return false;
        }

        if (self::loadCoreClass($className)) {
            return true;
        }

        if (self::loadModel($className)) {
            return true;
        }

        if (self::loadController($className)) {
            return true;
        }

        if (self::loadHelper($className)) {
            return true;
        }

        if (self::loadService($className)) {
            return true;
        }

        return false;
    }

    private static function loadService($className)
    {
        if (!defined('\SERVICES_PATH')) {
            return false;
        }
        $file = \SERVICES_PATH . '/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }

    /**
     * Load a core class
     *
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    private static function loadCoreClass($className)
    {
        $coreClasses = [
            'Router', 'Controller', 'Model', 'View', 'Database', 'Autoloader', 'RoleService'
        ];

        if (in_array($className, $coreClasses)) {
            $file = \APP_PATH . '/core/' . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }

    /**
     * Load a model
     *
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    private static function loadModel($className)
    {
        // Check if the class name ends with "Model"
        if (substr($className, -5) === 'Model') {
            $modelName = substr($className, 0, -5);
            $file = \MODELS_PATH . '/' . $modelName . '.php';
        } else {
            $file = \MODELS_PATH . '/' . $className . '.php';
        }

        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }

    /**
     * Load a controller
     *
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    private static function loadController($className)
    {
        // Supporto PSR-4: trasforma namespace in path
        if (strpos($className, '\\') !== false) {
            // Rimuove il prefisso "App\\" dal namespace, se presente
            if (strpos($className, 'App\\') === 0) {
                $classPath = substr($className, 4); // Rimuove "App\\"
            } else {
                $classPath = $className;
            }
            $classPath = str_replace('\\', '/', $classPath);
            // Try namespace case first (works for Cache/, Config/, Exceptions/),
            // then lcfirst of the first segment (works for core/, controllers/,
            // helpers/, models/, services/, middlewares/ which are lowercase on
            // Linux case-sensitive filesystems).
            $candidates = [$classPath];
            $parts = explode('/', $classPath, 2);
            $lcFirst = lcfirst($parts[0]) . (isset($parts[1]) ? '/' . $parts[1] : '');
            if ($lcFirst !== $classPath) {
                $candidates[] = $lcFirst;
            }
            foreach ($candidates as $candidate) {
                $file = \APP_PATH . '/' . $candidate . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }
        // Logica legacy (admin + base)
        if (substr($className, -10) === 'Controller') {
            if (defined('\ADMIN_CONTROLLERS_PATH')) {
                $adminFile = \ADMIN_CONTROLLERS_PATH . '/' . $className . '.php';
                if (file_exists($adminFile)) {
                    require_once $adminFile;
                    return true;
                }
            }
            if (defined('\FRONTEND_CONTROLLERS_PATH')) {
                $frontendFile = \FRONTEND_CONTROLLERS_PATH . '/' . $className . '.php';
                if (file_exists($frontendFile)) {
                    require_once $frontendFile;
                    return true;
                }
            }
            $baseFile = \CONTROLLERS_PATH . '/' . $className . '.php';
            if (file_exists($baseFile)) {
                require_once $baseFile;
                return true;
            }
        }
        return false;
    }

    /**
     * Load a helper class
     *
     * @param string $className The name of the class to load
     * @return bool True if the class was loaded, false otherwise
     */
    private static function loadHelper($className)
    {
        $file = \HELPERS_PATH . '/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }
}
