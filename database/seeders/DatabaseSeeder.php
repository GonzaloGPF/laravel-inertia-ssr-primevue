<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Services\FileService;
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
        $this->deleteStoredFiles();

        $this->truncate();

        Model::unguard();

        $this->call([
            UserSeeder::class,
        ]);
    }

    /**
     * Seeding after migrate:fresh finds no rows of files, as the table was dropped, so the physical files
     * are deleted by their folders: they would stay in the disk with nothing pointing at them.
     */
    private function deleteStoredFiles(): void
    {
        app(FileService::class)->deleteAllFolders();
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
            ->filter(fn ($table) => $table->$key !== 'migrations')
            ->each(fn ($table) => DB::table($table->$key)->truncate());

        Schema::enableForeignKeyConstraints();
    }
}
