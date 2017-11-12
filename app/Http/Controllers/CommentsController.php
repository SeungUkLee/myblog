<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Post $post)
    {
        return $post->comments()->with('user')->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Post $post)
    {
        // 입력값 유효성 검사
        $this->validate($request, ['content' => 'required']);

        // 포스트id를 강제로 집어 넣어준다.
        $request->merge(['post_id' => $post->id]);

        // 관계를 이용해서 Comment 생성
        $comment = $request->user()->comments()->create($request->all());

        // Lazy Load
        return response()->json($comment->load('user')->toArray());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Comment $comment
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(Request $request, Comment $comment)
    {
        // 사용자 입력값에 대한 유효성 검사를 수행.
        $this->validate($request, ['content' => 'required']);

        // 사용자의 권한을 검사한다. 이 커멘트를 수정할 수 있는 사용자인지
        $this->authorize('update', $comment);

        // 커멘트를 업데이트한다.
        $comment->update($request->all());

        // Lazy Load
        return response()->json($comment->load('user')->toArray());

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Comment $comment
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @internal param int $id
     */
    public function destroy(Comment $comment)
    {
        // 사용자 권한 검사
        $this->authorize('delete', $comment);

        // 커맨트 삭제
        $comment->delete();

        // 204 JSON 응답 반환
        return response()->json([], 204);
    }
}
