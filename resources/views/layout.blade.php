<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/css/app.css" crossorigin="anonymous">

    <title>AirCharts</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<nav class="navbar navbar-toggleable-md navbar-inverse fixed-top bg-inverse">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle Nav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="/">AirCharts</a>

    <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a href="/" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
                <a href="/about" class="nav-link">About</a>
            </li>
            <li class="nav-item">
                <a href="//api.aircharts.org" class="nav-link">API</a>
            </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" method="post" action="/charts">
            {{ csrf_field() }}
            <input class="form-control mr-sm-2" type="text" placeholder="Chart Search" name="query">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" id="searchbtn">Search</button>
        </form>
    </div>
</nav>
<div class="container">
    @if(session('error'))
        <div class="alert alert-danger">
            <strong>Error</strong> {{session('error')}}
        </div>
    @endif
    @if(isset($error))
        <div class="alert alert-danger">
            <strong>Error</strong> {{$error}}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">
            <strong>Success</strong> {{session('success')}}
        </div>
    @endif
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>There was an error processing your request, please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@yield('content')

<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
@yield('js')
</body>
</html>