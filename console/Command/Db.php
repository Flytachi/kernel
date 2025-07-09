<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Console\Command;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\Kernel\Console\Inc\Cmd;
use Flytachi\Kernel\Src\Unit\DbMapping\DbMapping;
use Flytachi\Kernel\Src\Unit\DbMapping\DbMappingDeclaration;

class Db extends Cmd
{
    public static string $title = "command database control";

    public function handle(): void
    {
        self::printTitle("Db", 32);

        if (
            count($this->args['arguments']) > 1
        ) {
            $this->resolution();
        } else {
            self::printMessage("Enter argument");
            self::print("Example: extra db sql");
        }

        self::printTitle("Db", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'migrate':
                    $this->migrate();
                    break;
                case 'sql':
                    $this->showSql();
                    break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function showSql(): void
    {
        $declaration = DbMapping::scanningDeclaration();
        $data = $this->processDeclarationData($declaration);

        foreach ($declaration->getItems() as $item) {
            self::printLabel($item->config::class, 32);

            if (count($data['sqlSchema']) > 0) {
                self::printMessage("* Schema", 32);
                foreach ($data['sqlSchema'] as $sql) {
                    self::printSplit($sql['exec']);
                }
            }

            if (count($data['sqlMain']) > 0) {
                self::printMessage("* Structure", 32);
                foreach ($data['sqlMain'] as $sql) {
                    self::printSplit($sql['exec']);
                }
            }

            if (count($data['sqlSub']) > 0) {
                self::printMessage("* Constraints", 32);
                foreach ($data['sqlSub'] as $sql) {
                    self::printSplit($sql['exec']);
                }
            }
            self::printLabel($item->config::class, 32);
        }
    }

    private function migrate(): void
    {
        $declaration = DbMapping::scanningDeclaration();
        $data = $this->processDeclarationData($declaration);

        foreach ($declaration->getItems() as $item) {
            self::printLabel($item->config::class, 32);

            $db = $item->config->connection();

            if (count($data['sqlSchema']) > 0) {
                self::printMessage("* Schema", 32);
                foreach ($data['sqlSchema'] as $sql) {
                    try {
                        $db->exec($sql['exec']);
                        self::print("- [s] shema '{$sql['title']}'", 32);
                    } catch (\Throwable $e) {
                        self::print("- [f] shema '{$sql['title']}'", 31);
                        if (env('DEBUG', false)) {
                            self::print($e->getMessage(), 31);
                        }
                    }
                }
            }

            if (count($data['sqlMain']) > 0) {
                self::printMessage("* Structure", 32);
                foreach ($data['sqlMain'] as $sql) {
                    try {
                        $db->exec($sql['exec']);
                        self::print("- [s] table '{$sql['title']}'", 32);
                    } catch (\Throwable $e) {
                        self::print("- [f] table '{$sql['title']}'", 31);
                        if (env('DEBUG', false)) {
                            self::print($e->getMessage(), 31);
                        }
                    }
                }
            }

            if (count($data['sqlSub']) > 0) {
                self::printMessage("* Constraints", 32);
                foreach ($data['sqlSub'] as $sql) {
                    try {
                        $db->exec($sql['exec']);
                        self::print("- [s] " . $sql['title'], 32);
                    } catch (\Throwable $e) {
                        self::print("- [f] " . $sql['title'], 31);
                        if (env('DEBUG', false)) {
                            self::print($e->getMessage(), 31);
                        }
                    }
                }
            }
            self::printLabel($item->config::class, 32);
        }
    }

    /**
     * Processes the database declaration and prepares SQL statements.
     *
     * @param DbMappingDeclaration $declaration
     * @return array{sqlSchema: array, sqlMain: array, sqlSub: array{0: array, 1: array}}
     * An associative array containing 'sqlSchema', 'sqlMain', and 'sqlSub' arrays.
     */
    private function processDeclarationData(DbMappingDeclaration $declaration): array
    {
        $sqlSchema = [];
        $sqlMain = [];
        $sqlSub = [];
        $sqlSubF = [];

        foreach ($declaration->getItems() as $item) {
            $item->config->sepUp();

            foreach ($item->getTables() as $structure) {
                if ($structure instanceof Table) {
                    $schemaSql = $structure->createSchemaIfNotExists($item->config->getDriver());
                    if ($schemaSql !== null) {
                        $title = str_replace(
                            ';',
                            '',
                            str_replace('CREATE SCHEMA IF NOT EXISTS ', '', $schemaSql)
                        );
                        if (!isset($sqlSchema[$title])) {
                            $sqlSchema[$title] = [
                                'title' => $title,
                                'exec' => $schemaSql,
                            ];
                        }
                    }
                    $sql = $structure->toSql($item->config->getDriver());
                    $exp = explode(PHP_EOL . ');' . PHP_EOL, $sql);

                    $sqlMain[] = [
                        'title' => $structure->getFullName(),
                        'exec' => (count($exp) == 1 ? $exp[0] : $exp[0] . PHP_EOL . ');')
                    ];
                    if (count($exp) > 1) {
                        $subExp = explode(PHP_EOL, $exp[1]);
                        for ($i = 0; $i < count($subExp); $i++) {
                            if (str_starts_with($subExp[$i], 'ALTER TABLE')) {
                                preg_match('/ADD\s+CONSTRAINT\s+([a-zA-Z0-9_]+)/i', $sql, $match);
                                $title = $match[1] ?? 'unknown';
                                $sqlSubF[] = [
                                    'title' => "constraint '{$title}'",
                                    'exec' =>  $subExp[$i]
                                ];
                            } else {
                                preg_match(
                                    '/\bINDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?([a-zA-Z0-9_]+)/i',
                                    $subExp[$i],
                                    $match
                                );
                                $title = $match[1] ?? 'unknown';
                                $sqlSub[] = [
                                    'title' => "index '{$title}'",
                                    'exec' =>  $subExp[$i]
                                ];
                            }
                        }
                    }
                }
            }
        }

        return [
            'sqlSchema' => $sqlSchema,
            'sqlMain' => $sqlMain,
            'sqlSub' => [...$sqlSub, ...$sqlSubF]
        ];
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Db Help", $cl);

        self::printLabel("extra db [args...] -[flags...] --[options...]", $cl);
        self::printMessage("args - command", $cl);
        self::print("migrate - migration mapping sql in databases", $cl);
        self::print("sql - show mapping sql", $cl);


        self::printTitle("Db Help", $cl);
    }
}
