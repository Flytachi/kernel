<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Console\Command;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\Kernel\Console\Inc\Cmd;
use Flytachi\Kernel\Src\Unit\DbMapping\DbMapping;
use Flytachi\Kernel\Src\Unit\DbMapping\DbMappingDeclaration;
use JetBrains\PhpStorm\ArrayShape;

class Migration extends Cmd
{
    public static string $title = "command db control";

    public function handle(): void
    {
        self::printTitle("Migration", 32);

        if (
            count($this->args['arguments']) > 1
        ) {
            $this->resolution();
        } else {
            self::printMessage("Enter argument");
            self::print("Example: extra migration migrate");
        }

        self::printTitle("Migration", 32);
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
                    self::printSplit($sql);
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
                        $db->exec($sql);
                        self::print("- Shema " . $sql . ' -> creation success', 32);
                    } catch (\Throwable $e) {
                        self::print("- Shema " . $sql . ' -> creation failed', 31);
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
                        self::print("- Table " . $sql['title'] . ' -> creation success', 32);
                    } catch (\Throwable $e) {
                        self::print("- Table " . $sql['title'] . ' -> creation failed', 31);
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
                        self::print("- " . $sql['exec'] . ' -> creation success', 32);
                    } catch (\Throwable $e) {
                        self::print("- " . $sql['exec'] . ' -> creation failed', 31);
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
                    if ($schemaSql !== null && !in_array($schemaSql, $sqlSchema)) {
                        $sqlSchema[] = $schemaSql;
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
                                $sqlSubF[] = [
                                    'title' => $structure->getFullName(),
                                    'exec' =>  $subExp[$i]
                                ];
                            } else {
                                $sqlSub[] = [
                                    'title' => $structure->getFullName(),
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
            'sqlSub' => [...$sqlSub, ...$sqlSubF] // Combine them here
        ];
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Migration Help", $cl);


        self::printTitle("Migration Help", $cl);
    }
}
