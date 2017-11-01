<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostsRequest;
use \App\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug = null)
    {
        $model = \App\Tag::pluck('slug')->contains($slug)
            ? \App\Tag::whereSlug($slug)->first()->posts()
            : new Post;

        // with 로 N+1 문제 해결
        $posts = $model->with('user')->latest()->paginate(3);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 새 포스트 작성하기 위한 폼을 반환
        return view('posts.create', ['post' => new Post]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PostsRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostsRequest $request)
    {
        // 로그이나지 않은 사용자의 접근 차단 -> middleware 사용

        // 사용자 입력값에 대한 유효성 검사를 수행 -> 폼 리퀘스트를 통해 유효성 검사

        // DB에 사용자가 전달한 데이터 저장
        $post = $request->user()->posts()->create($request->all()); // 대량 할당 ($fillable) 때문에 all()을 써도 무관
        $post->tags()->sync($request->input('tags'));

        // 방금 만든 포스트의 상세보기 페이지로 이동
        return redirect(route('posts.show', $post->id));
    }

    /**
     * Display the specified resource.
     *
     * @param Post $post
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Post $post)
    {
        $post->load('user', 'tags'); // lazy load

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        // 포스트를 수정할 수 있는 폼을 반환
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PostsRequest|Request $request
     * @param Post $post
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(PostsRequest $request, Post $post)
    {
        // 이 사용자가 이 모델을 업데이트할 수 있는지 확인한다.
        $this->authorize('update', $post);

        // 사용자 입력값이 유효한지 확인 -> PostRequest 에서 해준다. (폼리퀘스트

        // 데이터베이스 레코드를 업데이트
        $post->update($request->all());
        $post->tags()->sync($request->input('tags'));

        // 해당 모델로 상세보기 페이지로 이동한다.
        return redirect(route('posts.show', $post->id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        // 모델을 삭제한다.
        $post->delete();

        // 204 JSON 응답을 반환한다.
        return response()->json([], 204);
    }
}
