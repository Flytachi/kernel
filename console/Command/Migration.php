<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Console\Command;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\Kernel\Console\Inc\Cmd;
use Flytachi\Kernel\Src\Unit\DbMapping\DbMapping;

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
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function migrate(): void
    {
        $declaration = DbMapping::scanningDeclaration();

        foreach ($declaration->getItems() as $item) {
            self::printLabel($item->config::class, 32);

            $item->config->sepUp();
            $sqlMain = $sqlSub = $sqlSubF = [];

            foreach ($item->getTables() as $structure) {
                if ($structure instanceof Table) {
                    $sql = $structure->toSql($item->config->getDriver());
                    $exp = explode(PHP_EOL . ');' . PHP_EOL, $sql);

                    $sqlMain[] = [
                        'title' => $structure->getFullName(),
                        'exec' => $exp[0] . ');'
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
            $sqlSub = [...$sqlSub, ...$sqlSubF];

            $db = $item->config->connection();
            $db->beginTransaction();

            self::printMessage("* structure", 32);
            foreach ($sqlMain as $sql) {
                try {
                    $info = $db->exec($sql['exec']);
                    self::print("- Table " . $sql['title'] . ' -> creation success', 32);
                } catch (\Throwable $e) {
                    self::print("- Table " . $sql['title'] . ' -> creation failed', 31);
                    if (env('DEBUG', false)) {
                        self::print($e->getMessage(), 31);
                        self::print($e->getTraceAsString(), 31);
                    }
                }
            }

            self::printMessage("* constraints", 32);
            foreach ($sqlSub as $sql) {
                try {
                    $db->exec($sql['exec']);
                    self::print("- " . $sql['exec'] . ' -> creation success', 32);
                } catch (\Throwable $e) {
                    self::print("- " . $sql['exec'] . ' -> creation failed', 31);
                    if (env('DEBUG', false)) {
                        self::print($e->getMessage(), 31);
                        self::print($e->getTraceAsString(), 31);
                    }
                }
            }

            $db->commit();

            self::printLabel($item->config::class, 32);
        }
    }

    public static function help(): void
    {
        $cl = 34;
        self::printTitle("Migration Help", $cl);


        self::printTitle("Migration Help", $cl);
    }
}
