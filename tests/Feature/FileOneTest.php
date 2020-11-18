<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;


class FileOneTest extends TestCase
{
    use RefreshDatabase;

    public function testJsonUploadLmnAdFile()
    {
        //Once the first file is posted, validate it, and save the content to a data store (MySQL preferred).
        //add the unimported rows to the unimported list in the response

        $content = Storage::disk('tests')->get('TechnicalAssignmentSampleDataFile1.csv');
        $this->json('POST', 'api/fileone', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => []]);

        //Additionally, allow a dataset to be retrieved by Record ID.

        $this->json('GET', 'api/fileone/63ds')
            ->assertStatus(200)
            ->assertExactJson(['Record ID' => '63ds', 'Name' => 'Another Record']);
    }

    public function testJsonWithoutFile()
    {
        $this->json('POST', 'api/fileone', [])
            ->assertStatus(400); // Bad request
    }

    public function testJsonWithEmptyFile()
    {
        $this->json('POST', 'api/fileone', ['data' => ''])
            ->assertStatus(200);
    }

    public function testJsonIdempotence()
    {
        $content = Storage::disk('tests')->get('TechnicalAssignmentSampleDataFile1.csv');
        $this->json('POST', 'api/fileone', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => []]);

        $this->json('POST', 'api/fileone', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => [
                ['23d2','First Entry'],
                ['63ds','Another Record'],
                ['32nm','Yet Another Record']
            ]]);

        $this->json('GET', 'api/fileone/63ds')
            ->assertStatus(200)
            ->assertExactJson(['Record ID' => '63ds', 'Name' => 'Another Record']);
    }

    public function testJsonInvalidRows()
    {
        $content = "abc,record one\n" .
                   "de/f,record two\n" .
                   "ghi,record three\n" .
                   "jkl\n" .
                   "mno,record five,with details\n" .
                   "stu v,record seven\n" .
                   ",record eight\n" .
		   "pqr,record six\n" .
		   "pqr,record nine";

        $this->json('POST', 'api/fileone', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => [
                ['de/f','record two'],
                ['jkl'],
                ['mno', 'record five', 'with details'],
                ['stu v', 'record seven'],
		        ['', 'record eight'],
		        ['pqr','record nine']
            ]]);

        $this->json('GET', 'api/fileone/pqr')
            ->assertStatus(200)
            ->assertExactJson(['Record ID' => 'pqr', 'Name' => 'record six']);

    }

    public function testPostUploadLmnAdFile()
    {
        $content = Storage::disk('tests')->get('TechnicalAssignmentSampleDataFile1.csv');
        $this->post('api/fileone', ['data' => $content])
            ->assertStatus(200)
            ->assertExactJson(['unimported' => []]);

        $this->json('GET', 'api/fileone/63ds')
            ->assertStatus(200)
            ->assertExactJson(['Record ID' => '63ds', 'Name' => 'Another Record']);
    }
}
