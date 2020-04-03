@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Gitgub repositories</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        <form id="search-form" class="form-inline" action="{{ url('repositories') }}" method="GET">
                            <input id="search-form-name-input" name="search" type="text" class="form-control mb-2 mr-sm-2" placeholder="Search" value="{{ ( Request::get('search') ? Request::get('search') : 'PHP') }}">
                            <button type="submit" class="btn btn-primary mb-2">Search</button>
                        </form>
                        <div class="text-center" id="spinner">
                            <div class="lds-dual-ring" ></div>
                        </div>
                        <table class="table" id="repositories-table">
                            <tbody>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        function getRepositories(name) {
            const params = ( name ) ? { 'search' : name } : {};
            $.getJSON( "repositories", params, function( data ) {
                var items = '';
                $.each( data, function( key, val ) {
                    let likeType = 'like';
                    let likeIconClass = 'fa-thumbs-up';
                    if ( val.like_type == 1 ) {
                        likeType = 'dislike';
                        likeIconClass = 'fa-thumbs-down';
                    }
                    console.log(val.owner);
                    items = items + '<tr data-name="' + val.name + '" data-owner="' + val.owner + '">'
                        + '<th scope="row">' + (key + 1) + '</th>'
                        + '<td><a href="repository/' + val.owner + '/' + val.name + '">' +  val.owner + '/' + val.name + '</a></td>'
                        + '<td>'
                        + '<a href="#" class="like-btn" data-type="' + likeType + '">'
                        + '<i style="margin-right: 10px" class="like-icon fas ' + likeIconClass + '"></i>'
                        + '</a>'
                        + '</td>'
                        + '</tr>' ;
                });
                $( "#repositories-table tbody" ).html(items);
                $('#spinner').hide();

                $(document).on('click', '.like-btn', function(){
                    const row = $(this).closest('tr');
                    const type = $(this).attr('data-type');
                    const that = this;
                    $.post('/like',
                        {
                            "_token": "{{ csrf_token() }}",
                            "name": row.data('name'),
                            "owner": row.data('owner'),
                            "type": type
                        },
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
            });
        }

        getRepositories();

        $("#search-form").submit(function(e) {
            e.preventDefault();
            var form = $(this);
            $( "#repositories-table tbody" ).empty();
            $('#spinner').show();
            getRepositories($('#search-form-name-input').val().trim());
        });
    </script>
@endpush


