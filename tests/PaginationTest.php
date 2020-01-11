<?php

namespace TomHart\Restful\Tests;

use Illuminate\Support\Facades\DB;
use TomHart\Restful\Tests\Classes\Models\Comment;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\Classes\Models\Post;

class PaginationTest extends TestCase
{

    /**
     * Fix issue 4
     */
    public function testPaginationWorks(): void
    {
        DB::beginTransaction();
        for ($i = 0; $i < 20; $i++) {
            (new ModelTest())->save();
        }
        DB::commit();

        $response = $this->get(route('model-tests.index'), [
            'Accept' => 'application/json'
        ]);

        $data = $response->json();

        $response = $this->get($data['next_page_url'], [
            'Accept' => 'application/json'
        ]);

        $data1 = $response->json();

        $this->assertEquals(20, $data1['total']);
    }

    /**
     * Fix issue 2
     */
    public function testCanPaginateHasMany()
    {
        $comment = new Comment();
        $comment->save();

        $post = new Post();
        $post->save();
        $post->comments()->save($comment);

        $response = $this->json('GET', route('posts.show.extra', [
            'post' => $post->id,
            'extra' => 'comments'
        ]));

        $this->assertIsPagination($response);
    }
}
