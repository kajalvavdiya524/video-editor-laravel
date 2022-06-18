<?php

use App\Domains\Auth\Models\MediaFolder;
use Illuminate\Database\Seeder;

/**
 * Class CompanyTableSeeder.
 */
class MediaFolderSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Add the test company
        MediaFolder::create([
            'name' => 'root',
        ]);
        MediaFolder::create([
            'name' => 'video',
            'folder_id' => '1',
        ]);
        MediaFolder::create([
            'name' => 'audio',
            'folder_id' => '1',
        ]);
        MediaFolder::create([
            'name' => 'image',
            'folder_id' => '1',
        ]);

        $this->enableForeignKeys();
    }
}
