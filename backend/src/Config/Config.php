<?php
declare(strict_types=1);

namespace TestingTimes\Config;

use Exception;
use JmesPath\Env as JsonPath;

/**
 * Class Config
 *
 * @package TestingTimes\Config
 */
class Config
{
    /**
     * @var array
     */
    private array $configData;

    /**
     * @throws Exception
     */
    public function __construct(Env $env)
    {
        $configDir = dirname(__DIR__, 2) . '/config';
        if ($handle = opendir($configDir)) {
            while (false !== ($entry = readdir($handle))) {
                if (stripos($entry, '.php') !== false) {
                    $data = include $configDir . '/' . $entry;
                    if (!is_array($data)) {
                        throw new Exception("files in {$configDir} should return an array. {$entry} does not");
                    }
                    $name = str_replace('.php', '', $entry);
                    $this->configData[$name] = $data;
                }
            }

            closedir($handle);
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return JsonPath::search($key, $this->configData);
    }
}
