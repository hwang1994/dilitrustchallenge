<!DOCTYPE html>
<html>
<head>
    <title>Dilitrust Challenge</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <div class="container">

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1 class="login-panel text-center text-muted">
                    Dilitrust Challenge
                </h1>
                <hr/>
            </div>
        </div>

        @if ($errors->any())
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        {{ $error }} <br/>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                @if (Auth::guard('profile')->check())
                <button class="btn btn-default" data-toggle="modal" data-target="#newDocument"><i class="fa fa-file"></i> Upload New Document</button>
                <a href="/logout" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>
                @else
                <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#login"><i class="fa fa-sign-in"> </i> Login</a>
                <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#signup"><i class="fa fa-user"> </i> Sign Up</a>
                @endif
            </div>
        </div>

        @yield('content')

    </div>

</div>

@if (Auth::guard('profile')->check())
<div id="newDocument" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" enctype="multipart/form-data" method="post" action="/newdocument">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">New Document(s) (pdfs only and each file must be below 50mb)</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Document</label>
                    <input class="form-control" type="file" name="file[]" multiple>
                </div>
                @if (Auth::guard('profile')->user()->role==='admin')
                <div class="form-group">
                    <label>Visibility</label>
                    <div class="radio">
                        <label><input name="visibility" type="radio" value="user" checked>User</label>
                        <label><input name="visibility" type="radio" value="admin">Admin</label>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Post Document(s)!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@else
<div id="login" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="/login">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Login</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input class="form-control" type="text" name="username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Login!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="signup" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="/signup">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Sign Up</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Username (7-15 characters in length and alphanumeric ONLY)</label>
                    <input class="form-control" type="text" name="username">
                </div>
                <div class="form-group">
                    <label>Email (only @dilitrust.com emails)</label>
                    <input class="form-control" type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Password (8-15 characters, at least one uppercase and lowercase letter, number, symbol)</label>
                    <input class="form-control" type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Verify Password</label>
                    <input class="form-control" type="password" name="password_confirmation">
                </div>
                <div class="form-group">
                    <label>Role (admin can view/delete any and all documents)</label>
                    <div class="radio">
                        <label><input name="role" type="radio" value="user" checked>User</label>
                        <label><input name="role" type="radio" value="admin">Admin</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Sign Up!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif

</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    $(document).on("click", "#open-deleteWarning", function () {
        var $form = $('#deletewarningform');
        var documentId = $(this).data('id');
        $form.attr('action', '/delete/'+documentId);
    });
</script>
</html>
