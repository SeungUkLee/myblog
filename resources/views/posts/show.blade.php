@extends('layouts.app')

@section('content')
  <h1 class="page-header">
    {{ $post->title }}
  </h1>

  <ul>
    <li>{{ $post->created_at->diffForHumans() }}</li>
    <li>{{ $post->user->name }}</li>
  </ul>

  <article>
    {!! markdown($post->content) !!}
  </article>

  <ul>
    @foreach ($post->tags as $tag)
      <li>
        <a href="{{ route('tags.posts.index', $tag->slug) }}">
          {{ $tag->name }}
        </a>
      </li>
    @endforeach
  </ul>

  <div class="text-center">

    <a href="{{ route('posts.index') }}" class="btn btn-default"> List </a>

    @can('update', $post)
      <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-warning"> Edit </a>
    @endcan

    @can('delete', $post)
      <button class="btn btn-danger" @click="deletePost"> Delete </button>
    @endcan
  </div>

  <h2> List of Comments </h2> <!-- Comments -->

  <form id="comments_create" @submit.prevent="createComment">
    <div class="form-group">
      <textarea id="content" class="form-control" placeholder="Leave your comment" v-model="content"></textarea>
    </div>

    <div class="form-group text-right">
      <button type="submit" class="btn btn-primary btn-sm">
        Comment
      </button>
    </div>
  </form>

  <ul>
    <li v-for="comment in comments | orderBy 'id' -1">
      <comment :comment="comment" @update="updateComment" @deleted="deleteComment" inline-template>
        @{{ comment.content }} <!-- 15:00 -->
        <small>
          by @{{ comment.user.name }}
          @{{ comment.created_at }}
					<ul>
						<li>
							<a href="#" @click.prevent="toggleUpdateForm">Edit</a>
						</li>
						<li>
							<a href="#" @click.prevent="deleteComment">Delete</a>
						</li>
					</ul>
        </small>



				<!-- update form -->
				<form @submit.prevent="updateComment" v-show="visible">
					<div class="form-group">
						<textarea class="form-control" v-model="newContent">@{{ comment.content }}</textarea>
					</div>

					<div class="form-group">
						<div class="text-right">
							<button type="submit" class="btn btn-primary btn-sm">
								Update
							</button>
						</div>
					</div>
				</form>
      </comment>
    </li>
  </ul>



@endsection

@push('script')
  <script>
    Vue.component('comment',{
      props: ['comment'],

			data: function() {
				return {
					visible: false,
					newContent: '',
				}
			},

      methods: {
        deleteComment: function() {
          this.$http.delete('/comments/'+this.comment.id)
              .then(function() {
								this.$dispatch('deleted', this.comment);
              })
        },

				toggleUpdateForm: function() {
					this.visible = ! this.visible;
				},

				updateComment: function() {
					this.$http.put('/comments/'+this.comment.id, {content: this.newContent})
							.then(function (response) {
								this.$dispatch('update', response.json());
								this.toggleUpdateForm();
							})
				}
      }
    });

    new Vue({
      el: 'body',

      data: {
        comments: [],
        content: ''
      },

      ready: function () {
        this.fetchComments();
        hljs.initHighlightingOnLoad();
      },

      methods: {
        deletePost: function (e) {
          if (confirm('Are you sure?')) {
            this.$http.delete('{{ route('posts.destroy', $post->id) }}')
              .then(function (response) {
                alert('Post deleted!');
                  window.location.href = '{{ route('posts.index') }}';
              });
          }
        },

        fetchComments: function () {
          this.$http.get('{{ route('posts.comments.index', $post->id) }}')
            .then(function (response) {
//                          this.comments = response.data; // TODO json으로 넘겼는데 왜 string??
              console.log(response.json());
              this.comments = response.json();
//                            this.comments = JSON.parse(response.data);
            });
        },

        createComment: function() {
          this.$http.post('{{ route('posts.comments.store', $post->id) }}', {
            content: this.content })
              .then(function (response) {
                this.comments.push(response.json());
                this.content = '';
                alert('Comment created!');
              });
        },

				deleteComment: function (comment) {
					this.comments.$remove(comment);
					alert('Comment deleted!');
				},

				updateComment: function (newComment) {
					var oldComment = _.filter(this.comments, {id: newComment.id});
					this.comments.$set(oldComment, newComment);
					alert('Comment updated!');
				},
      }
    })
  </script>
@endpush
