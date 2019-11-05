<?php
/**
 * This file is a part of "comely-io/app-kernel" package.
 * https://github.com/comely-io/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\App;

use Comely\App\Config\DbConfig;
use Comely\App\Exception\AppConfigException;
use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;
use Comely\Database\Database;
use Comely\Database\Queries\Query;
use Comely\Database\Server\DbCredentials;

/**
 * Class Databases
 * @package Comely\App
 */
class Databases
{
    /** @var AppKernel */
    private $appKernel;
    /** @var array */
    private $dbs;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * Databases constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->dbs = [];
    }

    /**
     * @param string $tag
     * @return Database
     * @throws AppConfigException
     * @throws \Comely\Database\Exception\DbConnectionException
     */
    public function get(string $tag = "primary"): Database
    {
        if (!preg_match('/^[\w\-]{2,16}$/', $tag)) {
            throw new \InvalidArgumentException('Invalid database tag');
        }

        $tag = strtolower($tag);
        if (array_key_exists($tag, $this->dbs)) {
            return $this->dbs[$tag];
        }

        $dbConfig = $this->appKernel->config()->db($tag);
        if (!$dbConfig instanceof DbConfig) {
            throw new AppConfigException(sprintf('Database with tag "%s" is not configured', $tag));
        }

        $dbCredentials = (new DbCredentials($dbConfig->driver))
            ->server($dbConfig->host)
            ->database($dbConfig->name);

        if ($dbConfig->username) {
            $dbCredentials->credentials($dbConfig->username, $dbConfig->password);
        }

        $db = new Database($dbCredentials);
        $this->dbs[$tag] = $db;

        return $db;
    }

    /**
     * @return array
     */
    public function getAllQueries(): array
    {
        $queries = [];

        /**
         * @var string $dbName
         * @var Database $dbInstance
         */
        foreach ($this->dbs as $dbName => $dbInstance) {
            /** @var Query $query */
            foreach ($dbInstance->queries() as $query) {
                $queries[] = [
                    "db" => $dbName,
                    "query" => $query
                ];
            }
        }

        return $queries;
    }
}