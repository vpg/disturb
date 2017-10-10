<?php

namespace Disturb;

/**
 * Class Loader
 *
 * @package Vpg
 * @author  Olivier Garbé <ogarbe@voyageprive.com>
 *
 */
class Loader extends \Phalcon\Loader
{
    /**
     * Initialize autoloading with composer, and sets error and exception handlers
     *
     * @param string $vendorDir          vendor directory
     * @param array  $addedNamespaceList forced namespaces
     *
     * @return void
     */
    public function initialize($vendorDir, $forcedNamespaceList = [])
    {
        /**
         * Set error and exception generic handlers
         */
        $this->setErrorAndExceptionHandlers();

        /**
         * Init autoloader
         */
        $composerDir = realpath($vendorDir . '/composer') . '/';
        $composerIncludePath =  @include $composerDir . '/include_paths.php';
        if (!empty($composerIncludePath)) {
            set_include_path(
                get_include_path() .
                PATH_SEPARATOR .
                implode(PATH_SEPARATOR, $composerIncludePath)
            );
        }
        $namespaces = [];
        $prefixes = [];
        $map = @include $composerDir . '/autoload_psr4.php';
        if (!empty($map)) {
            foreach ($map as $k => $values) {
                $k = trim($k, '\\');
                if (!isset($namespaces[$k])) {
                    $namespaces[$k] = implode(';', $values) . '/';
                }
            }
        }
        $namespaces = array_merge($namespaces, $forcedNamespaceList);

        /*echo "DEBUG = " . __FILE__ . " => " . __METHOD__ . " => " . __LINE__;
        echo "<pre>";
        echo var_dump($namespaces);
        echo "</pre>";
        die('END DEBUG');*/

        // get namespaces from Composer
        $map = @include $composerDir . '/autoload_namespaces.php';
        if (!empty($map)) {
            foreach ($map as $k => $values) {
                $k = trim($k, '\\');
                if (!isset($namespaces[$k])) {
                    $dir = '/' . str_replace('\\', '/', $k) . '/';
                    if (substr($k, -1) === '_') {
                        $dir = '/' . str_replace('_', '/', $k);
                        $prefixes[$k] = implode($dir . ';', $values) ;
                    } else {
                        $namespaces[$k] = implode($dir . ';', $values) . $dir;
                    }
                }
            }
        }

        $this->registerNamespaces($namespaces);
        $classMap = @include $composerDir . '/autoload_classmap.php';
        if (!empty($classMap)) {
            $this->registerClasses($classMap);
        }
        $this->registerFiles([realpath($vendorDir) . '/autoload.php']);
        $this->register();

        $files = @include $composerDir . '/autoload_files.php';
        if (!empty($files)) {
            $files = array_unique($files);
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

    /**
     * Sets both error and exception handlers in order to have a generic behavior
     *
     * @return void
     */
    protected function setErrorAndExceptionHandlers()
    {
        /**
         * Set generic error handler
         */
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                if (0 === error_reporting()) {
                    return false;
                }
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            },
            E_ALL
        );

        /**
         * Set generic exception handler
         */
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Generic exception handler
     * It logs every uncaught exception and calls custom ExceptionHandler
     *
     * Usage: Register ExceptionHandler custom function in DI at application entry point (e.g.: web/index.php)
     *        ExceptionHandler has to have an \Exception as main parameter
     *
     * @param \Exception $e PHP basic exception
     *
     * @return bool
     */
    public function handleException(\Throwable $e)
    {
        $di = \Phalcon\DI::getDefault();
        if ($di && $di->has('logger')) {
            $di->get('logger')->error(
                get_class($e) . ' - ' .
                $e->getCode() . ' - ' .
                $e->getMessage() . ' - '.
                $e->getFile() . '(' . $e->getLine() . ')'
            );
            $di->get('logger')->debug(
                $e->getTraceAsString()
            );
        }
        if ($di && $di->has('ExceptionHandler')) {
            return $di->get('ExceptionHandler', [$e]);
        }
        return false;
    }
}
