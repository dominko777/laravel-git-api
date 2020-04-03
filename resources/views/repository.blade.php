@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Repository</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        <h3>Information</h3>
                        <ul style="list-style: none; padding-left: 0px;">
                            <li>Name: {{ $repositoryInfo['name'] }}  </li>
                            <li>Author: {{ $repositoryInfo['owner'] }}</li>
                            <li>Likes: {{ $repositoryInfo['likes'] }}</li>
                            <li>Dislikes: {{ $repositoryInfo['dislikes'] }}</li>
                            <li>
                                <a href="#" class="like-btn"
                                   data-name="{{ $repositoryInfo['name'] }}"
                                   data-owner="{{ $repositoryInfo['owner'] }}"
                                   data-type="@if($repositoryInfo['is_liked']) dislike @else like @endif"
                                >
                                    <i style="margin-right: 10px" class="like-icon fas
                                    @if($repositoryInfo['is_liked']) fa-thumbs-down @else fa-thumbs-up @endif">
                                    </i>
                                </a>
                            </li>
                        </ul>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.like-btn', function(){
            const btn = $(this);
            const type = btn.attr('data-type').trim();
            const that = this;
            $.post('/like',
                { "_token": "{{ csrf_token() }}", "owner": btn.data('owner'), "name": btn.data('name'), "type": type },
                function(response) {
                    if (response.status == 'success') {
                        const icon = $(that).find('.like-icon');
                        if (type === 'like') {
                            icon.removeClass('fa-thumbs-up').addClass('fa-thumbs-down');
                            $(that).attr('data-type', 'dislike');
                        } else {
                            icon.removeClass('fa-thumbs-down').addClass('fa-thumbs-up');
                            $(that).attr('data-type', 'like');
                        }
                    }
                }
            );
            return false;
        });
    </script>
@endpush


