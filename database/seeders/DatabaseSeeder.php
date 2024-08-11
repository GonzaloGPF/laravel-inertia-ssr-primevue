<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws Exception
     */
    public function run(): void
    {
        $this->truncate();

        Model::unguard();

        $this->call([
            UserSeeder::class,
        ]);
    }

    /**
     * @throws Exception
     */
    private function truncate(): void
    {
        Schema::disableForeignKeyConstraints();

        // Truncate all tables, except migrations
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::connection()->getDatabaseName();
        $key = "Tables_in_$dbName";

        collect($tables)
            ->each(function ($table) use ($key) {
                if ($table->$key !== 'migrations'){
                    DB::table($table->$key)->truncate();
                }
            });

        Schema::enableForeignKeyConstraints();
    }
}
