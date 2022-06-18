@extends('backend.layouts.app')

@section('title', __('Video Review'))

@push('after-styles')
    <style type="text/css">
        .toast {
            background-color: rgba(255,255,255,.85);
        }
    </style>
@endpush

@section('content')
<x-backend.card>
    <x-slot name="header">
        <h4>Video Review</h4>
    </x-slot>
    <x-slot name="body">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-8">
                <video style="width: 100%;" controls>
                    <source src="{{ $share->videoCreation->path_mp4() }}" type="video/mp4">
                </video>
                @auth
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.auth.video.comments.store') }}">
                                @csrf
                                <input type="hidden" name="share_uuid" value="{{ $share->uuid }}">
                                <div class="form-group row">
                                    <label for="subject" class="col-sm-2 col-form-label col-form-label-sm">Subject</label>
                                    <div class="col-sm-10">
                                        <input type="text" id="subject" name="subject" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="comment" class="col-sm-2 col-form-label col-form-label-sm">Comment</label>
                                    <div class="col-sm-10">
                                        <textarea id="comment" name="comment" class="form-control form-control-sm" placeholder="Write your comment here..." required></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
            <div class="col-sm-12 col-md-4">
                <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
                  <!-- Position it -->
                    <div style="position: absolute; top: 0; right: 0;">
                        <!-- Then put toasts within -->
                        @foreach($share->comments as $comment)
                            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="toast-header">
                                    <strong class="mr-auto">{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->ago }}</small>
                                    @if(Auth::user() && Auth::user()->hasRole('admin'))
                                        <button class="btn btn-delete" data-id="{{ $comment->id }}">&times;</button>
                                    @endif
                                </div>
                                <div class="toast-body">
                                    <p class="font-weight-bold">{{ $comment->subject }}</p>
                                    <p>{{ $comment->comment }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/video-review.js') }}"></script>
@endpush