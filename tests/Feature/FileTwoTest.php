<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;


class FileTwoTest extends TestCase
{
    use RefreshDatabase;

    public function testJsonUploadLmnAdFiles()
    {
        $content = Storage::disk('tests')->get('TechnicalAssignmentSampleDataFile1.csv');
        $this->json('POST', 'api/fileone', ['data' => $content]);
        $content = Storage::disk('tests')->get('TechnicalAssignmentSampleDataFile2.csv');

        // Once the second file is posted, validate it and store its related contents. However, if a Record ID
        // from the second file isn’t found in the data store (from the first file), add it to an unimported list
        // and return the list in the response. This would enable the requestor to know what succeeded
        // and what failed.

        $this->json('POST', 'api/filetwo', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => [
                ['88nv','2/1/2020','click','23'],
                ['88nv','2/2/2020','click','235'],
                ['88nv','2/3/2020','click','53'],
                ['88nv','2/1/2020','conversion','10'],
                ['88nv','2/2/2020','conversion','81'],
                ['88nv','2/3/2020','conversion','0']
            ]]);

        // Additionally, allow a dataset to be retrieved by Record ID. The result would return the related
        // data from both files in a data structure (JSON).

        $this->json('GET', 'api/filetwo/32nm')
            ->assertStatus(200)
            ->assertExactJson([
                ['Record ID' => '32nm',
                'Name' => 'Yet Another Record',
                'Date' => '2/1/2020',
                'EventName' => 'click',
                'NumberOfEvents' => 23
                ],
                ['Record ID' => '32nm',
                    'Name' => 'Yet Another Record',
                    'Date' => '2/1/2020',
                    'EventName' => 'conversion',
                    'NumberOfEvents' => 7
                ],
                ['Record ID' => '32nm',
                    'Name' => 'Yet Another Record',
                    'Date' => '2/2/2020',
                    'EventName' => 'click',
                    'NumberOfEvents' => 12
                ],
                ['Record ID' => '32nm',
                    'Name' => 'Yet Another Record',
                    'Date' => '2/2/2020',
                    'EventName' => 'conversion',
                    'NumberOfEvents' => 5
                ],
                ['Record ID' => '32nm',
                    'Name' => 'Yet Another Record',
                    'Date' => '2/3/2020',
                    'EventName' => 'click',
                    'NumberOfEvents' => 88
                ],
                ['Record ID' => '32nm',
                    'Name' => 'Yet Another Record',
                    'Date' => '2/3/2020',
                    'EventName' => 'conversion',
                    'NumberOfEvents' => 32
                ]]
            );
    }

    public function testJsonWithoutFile()
    {
        $this->json('POST', 'api/filetwo', [])
            ->assertStatus(400); // Bad request
    }

    public function testJsonWithEmptyFile()
    {
        $this->json('POST', 'api/filetwo', ['data' => ''])
            ->assertStatus(200);
    }

    public function testJsonIdempotence()
    {
        $content = "abc,record one";
        $this->json('POST', 'api/fileone', ['data' => $content]);
        $content = "abc,2/2/2020,click,12";
        $this->json('POST', 'api/filetwo', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => []]);

        $this->json('POST', 'api/filetwo', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => [
                ['abc','2/2/2020', 'click', '12']
            ]]);

        $this->json('GET', 'api/filetwo/abc')
            ->assertStatus(200)
            ->assertExactJson([
                ['Record ID' => 'abc',
                'Name' => 'record one',
                'Date' => '2/2/2020',
                'EventName' => 'click',
                'NumberOfEvents' => 12
            ]]);
    }

    public function testJsonInvalidRows()
    {
        $content = "abc,record one";
        $this->json('POST', 'api/fileone', ['data' => $content]);

        $content = "abc,Silvestr 2020,conversion,1\n" .
                   "abc,,conversion,2\n" .
                   "abc,,,3\n" .
                   "abc\n" .
                   "abc,2/4/2020,click,4,99\n" .
                   "abc,2/1/2020,click,5\n" .
                   "abc,2/1/2020,conversion,6";

        $this->json('POST', 'api/filetwo', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => [
                ['abc','Silvestr 2020','conversion', '1'],
                ['abc','','conversion','2'],
                ['abc','','','3'],
                ['abc'],
                ['abc','2/4/2020','click','4','99']
            ]]);

        $this->json('GET', 'api/filetwo/abc')
            ->assertStatus(200)
            ->assertExactJson([
                ['Record ID' => 'abc',
                'Name' => 'record one',
                'Date' => '2/1/2020',
                'EventName' => 'click',
                'NumberOfEvents' => 5
            ],
                ['Record ID' => 'abc',
                    'Name' => 'record one',
                    'Date' => '2/1/2020',
                    'EventName' => 'conversion',
                    'NumberOfEvents' => 6
                ]
            ]);
    }

    public function testPostUploadLmnAdFiles()
    {
        $file1 = UploadedFile::fake()->createWithContent('data.csv', "abc,record one");
        $this->call('post', 'api/fileone', [], [], ['data' => $file1]);

        $file2 = UploadedFile::fake()->createWithContent('data.csv', "abc,2/2/2020,click,12");

        $this->call('post', 'api/filetwo', [], [], ['data' => $file2])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => []]);

        $this->json('GET', 'api/filetwo/abc')
            ->assertStatus(200)
            ->assertExactJson([
                ['Record ID' => 'abc',
                'Name' => 'record one',
                'Date' => '2/2/2020',
                'EventName' => 'click',
                'NumberOfEvents' => 12
            ]]);
    }
}
