@extends('layouts.app')

@section('title', isset($category) ? $category->name : '话题列表')

@section('styles')
<style>
  /* Pink accent styling for home/topic index */
  body {
    background: #e6f7ff;
  }

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

  /* Branch badge styling */
  .branch-badge {
    position: fixed;
    bottom: 10px;
    right: 10px;
    background: #ff0;
    color: #000;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: bold;
    border-radius: 4px;
    z-index: 9999;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }
</style>
@endsection

@section('content')

<div class="mb-4 p-4 bg-white rounded shadow-sm">
  <p class="mb-2">Hi, I am Ayesha.</p>
  <p class="mb-2">Welcome to the community — explore the latest topics and join the discussion.</p>
  <p class="mb-0">Feel free to browse, reply, or start your own thread anytime.</p>
</div>

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
        </
