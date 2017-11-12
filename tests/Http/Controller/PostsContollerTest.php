<?php

namespace Test\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\HttpException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostsContollerTest extends \Test\TestCase
{
    use DatabaseTransactions;

    protected $user;

    public function setUp()
    {
        parent::setUp(); // 부모의 setUp 메서드는 오버라이드 하면 안되기 때문에 작성

        $this->user = factory(\App\User::class)->create([
            'name' => 'foo',
            'email' => 'foo@example.com',
        ]);
    }

    /**
     * @test
     */
    public function 로그인하지_않은_사용자가_글을_작성하려면_로그인_페이지로_이동한다() {
        // 어노테이션 test (@test)를 작성하면 메소드이름에 test_를 안붙여도 된다.
        $this->visitRoute('posts.create')
            ->seeRouteIs('login'); // seePageIs 사용해도 괜찮
    }

    /** @test */
    public function 로그인한_사용자는_글_작성_폼을_볼_수_있다(){
        $this->actingAs($this->user)
            ->visitRoute('posts.create')
            ->seeRouteIs('posts.create'); //actingAs 로그인한것처럼 속인다.
    }

    /** @test */
    public function 글을_작성한다() {
        // submitForm 한번에 submit 할 수 있게 해주는 helper함수
        $this->actingAs($this->user)
            ->visitRoute('posts.create')
            ->submitForm('Post', [
                'title' => 'foo title',
                'content' => 'long long content',
                'tags' => [1, 2],
            ])
            ->seeRouteIs('posts.show', 1)
            ->see('foo title') //body 안에 글자를 볼 수 있다.
            ->seeInDatabase('posts', [ // 테이블이름, 값
                'content' => 'long long content',
            ])->seeInDatabase('post_tag', [
                'post_id' => 1,
                'tag_id' => 1,
            ])
            ->notSeeInDatabase('post_tag', [
                'post_id' => 1,
                'tag_id' => 3,
            ]);
    }

    /** @test */
    public function 사용자_입력값에_오류가_있으면_글_작성_폼으로_리디렉션한다() {
        $this->actingAs($this->user)
            ->visitRoute('posts.create')
            ->submitForm('Post', [
                'title' => '',
                'content' => 'short',
                'tags' => [1,2],
            ])
            ->seeRouteIs('posts.create')
            ->see('The title field is required.')
            ->see('The content must be at least 10 characters.')
            ->seeInField('content', 'short')
            ->seeIsSelected('tags', 1);
    }

    /** @test */
    public function 로그인하지_않은_사용자가_글을_강제로_수정폼을_열려고하면_로그인페이지로_리디렉션한다() {
        $post = $this->stubPost();

        $this->visitRoute('posts.edit', $post->id)
            ->seeRouteIs('login');
    }

    /** @test */
    public function 인가되지_않은_사용자가_글을_수정하려하면_예외를_출력한다() {
        $post = $this->stubPost();
        $anotherUser = $this->stubUser();

        //expectException : phpunit에 내장된 메서드 (라라벨 메서드 x)
        $this->expectException(HttpException::class);
        $this->actingAs($anotherUser)
            ->visitRoute('posts.edit', $post->id);
    }

    /** @test */
    public function 사용자_입력값에_오류가_있으면_글_수정폼으로_리디렉션한다() {
        $post = $this->stubPost();
        $this->actingAs($this->user)
            ->visitRoute('posts.edit', $post->id)
            ->submitForm('Update', [
                'title' => 'foo title UPDATED',
                'content' => 'short',
                'tags' => [3,4]
            ])
            ->seeRouteIs('posts.edit', $post->id)
            ->seeInField('title', 'foo title UPDATED')
            ->seeInField('content', 'short')
            ->seeIsSelected('tags', 3)
            ->seeIsSelected('tags', 4);
    }

    /** @test */
    public function 글을_삭제한다() {
        $post = $this->stubPost();

        $this->actingAs($this->user)
            ->delete(route('posts.destroy', $post->id)) // ajax 요청
            ->seeStatusCode(204);
    }

    /** @test */
    public function 글_목록을_출력한다() {
        $posts = $this->stubPosts();

        $this->assertCount(6, $posts);

        $this->visitRoute('posts.index')
            ->seeInElement('.col-md-3', route('tags.posts.index', 'foo'))
            ->seeInElement('.col-md-3', route('tags.posts.index', 'bar'))
            ->seeInElement('.col-md-3', route('tags.posts.index', 'baz'))
            ->dontseeInElement('.col-md-3', route('tags.posts.index', 'hello'))
            ->seeInElement('.col-md-9', route('posts.show', 6))
            ->seeInElement('.col-md-9', route('posts.show', 5))
            ->seeInElement('.col-md-9', route('posts.show', 4))
            ->dontseeInElement('.col-md-9', route('posts.show', 1));
    }

    /** @test */
    public function 글목록에서_페이지_이동을_한다() {
        $posts = $this->stubPosts();

        $this->visitRoute('posts.index')
            ->click(2) // 2번째 페이지 클릭
            ->seeInElement('.col-md-9', route('posts.show', 3))
            ->seeInElement('.col-md-9', route('posts.show', 2))
            ->seeInElement('.col-md-9', route('posts.show', 1))
            ->dontseeInElement('.col-md-9', route('posts.show', 6));
    }

    /** @test */
    public function 글_상세보기를_출력한다() {
        $post = $this->stubPost();

        $this->actingAs($this->user)
            ->visitRoute('posts.show', $post->id)
            ->seeInElement('h1', 'foo title')
            ->seeInElement('article', 'long long content')
            ->seeLink('Edit', route('posts.edit', $post->id))
            ->seeInElement('.btn-danger','Delete'); // Delete는 ajax 요청을 하기 때문에
    }

    /** @test */
    public function 인가되지_않은_사용자에게는_수정_삭제_버튼을_출력하지_않는다() {
        $post = $this->stubPost();
        $anotherUser = $this->stubUser();

        $this->actingAs($anotherUser)
            ->visitRoute('posts.show', $post->id)
            ->dontSeeLink('Edit', route('posts.edit', $post->id))
            ->dontSeeInElement('.btn-danger', 'Delete');
    }

    private function stubPost() {
        return $this->user->posts()->create([
            'title' => 'foo title',
            'content' => 'long long content',
        ]);
    }

    private function stubUser() {
        return factory(User::class)->create([
            'name' => 'bar',
            'email' => 'bar@exmaple.com'
        ]);
    }

    private function stubPosts() {
        factory(User::class, 3)
            ->create()
            ->each(function ($user) {
                $user->posts()->save(factory(Post::class)->make());
                $user->posts()->save(factory(Post::class)->make());
            }); //each 는 collection에서 사용할 수 있는 method // 총 6개의 게시물이 생성

        return Post::get();
    }
}
