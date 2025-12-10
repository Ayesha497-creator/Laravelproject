@extends('layouts.app')

@section('title', isset($category) ? $category->name : '话题列表')

@section('styles')
<style>
  /* Pink accent styling for home/topic index */
  .topics-index-page {
    background: #fff0f6;
    min-height: 100vh;
  }

  .topics-index-page .card {
    border-color: #f5b8d3;
    box-shadow: 0 4px 16px rgba(255, 105, 180, 0.12);
  }

  .topics-index-page .card-header {
    background: #ffe1ef;
    border-bottom-color: #f5b8d3;
  }

  .topics-index-page .nav-pills .nav-link {
    color: #c2185b;
  }

  .topics-index-page .nav-pills .nav-link.active,
  .topics-index-page .nav-pills .nav-link:hover {
    color: #fff;
    background-color: #ec407a;
    border-radius: 999px;
  }
</style>
@endsection

@section('content')

<div class="row mb-5">
  <div class="col-lg-9 col-md-9 topic-list">

    @if (isset($category))
      <div class="alert alert-info" role="alert">
        {{ $category->name }} ：{{ $category->description }}
      </div>
    @endif

    <div class="card ">

      <div class="card-header bg-transparent">
        <ul class="nav nav-pills">
          <li class="nav-item">
            <a class="nav-link {{ active_class( ! if_query('order', 'recent')) }}" href="{{ Request::url() }}?order=default">
              最后回复
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ active_class(if_query('order', 'recent')) }}" href="{{ Request::url() }}?order=recent">
              最新发布
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body">
        {{-- 话题列表 --}}
        @include('topics._topic_list', ['topics' => $topics])
        {{-- 分页 --}}
        <div class="mt-5">
          {!! $topics->appends(Request::except('page'))->render() !!}
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-3 sidebar">
    @include('topics._sidebar')
  </div>
</div>

@endsection

