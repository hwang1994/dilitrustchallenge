@extends('welcome')
@section('content')

@if (Auth::guard('profile')->check())
    <div class="row">
        <div class="col-md-3">
            <h2 class="login-panel text-muted">
                Documents
            </h2>
            <hr/>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <a href="/downloadzip" class="btn btn-default"><i class="fa fa-download"></i> Download All</a>
        </div>
        @if (Auth::guard('profile')->user()->role==='admin')
        <div class="col-md-2">
            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteallwarning"><i class="fa fa-trash" aria-hidden="true"></i> Delete All</button>
        </div>
        @endif
    </div>
    </br>

    <div class="row">
        @foreach($documents->all() as $document)
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    {{$document['username']}}
                    @if (Auth::guard('profile')->id()===$document['user_id'] || Auth::guard('profile')->user()->role==='admin')
                    <span class="pull-right">
                        <button data-id="{{$document['id']}}" data-toggle="modal" data-target="#deletewarning" id="open-deleteWarning"><i class="fa fa-trash"></i></button>
                    </span>
                    @endif
                </div>
                <div class="panel-body text-center">
                    <p>
                        <a href="/download/{{$document['id']}}">
                            <img class="img-rounded img-thumbnail" src="img/documenticon.png"/>
                            <p class="text-muted text-justify" style="word-wrap:break-word">
                                {{$document['filename']}}
                            </p>
                        </a>
                    </p>
                    <!-- <p class="text-muted text-justify" style="word-wrap:break-word">
                        {{$document['filename']}}
                    </p> -->
                </div>
                <div class="panel-footer ">
                    <span><a href="mailto:{{$document['email']}}" data-toggle="tooltip" title="Email Uploader"><i class="fa fa-envelope"></i>{{$document['email']}}</a></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div id="deletewarning" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form role="form" id="deletewarningform" method="post" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-center">Are you sure?!</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="hidden" name="_method" value="DELETE"/>
                        <button type="submit" class="btn btn-default">Delete!</button>
                    </div>
                </div><!-- /.modal-content -->
            </form>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @if (Auth::guard('profile')->user()->role==='admin')
    <div id="deleteallwarning" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form role="form" method="post" action="/deleteall">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-center">Warning</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Enter Password to delete!</label>
                            <input class="form-control" type="password" name="password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="hidden" name="_method" value="DELETE"/>
                        <button type="submit" class="btn btn-default">Delete All!</button>
                    </div>
                </div><!-- /.modal-content -->
            </form>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @endif
    @else
    <div class="col-md">
        <h2 class="login-panel text-muted">
            Please login to upload/download documents!
        </h2>
    </div>
@endif

@stop