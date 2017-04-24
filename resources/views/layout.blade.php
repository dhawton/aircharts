<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/css/app.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <title>AirCharts</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <a class="navbar-brand" href="/">AirCharts</a>

        <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav">
                <li>
                    <a href="/" class="nav-link">Home</a>
                </li>
                <li>
                    <a href="/about" class="nav-link">About</a>
                </li>
                <li>
                    <a href="//api.aircharts.org" class="nav-link">API</a>
                </li>
            </ul>
            <form class="navbar-form navbar-right" method="post" action="/charts">
                {{ csrf_field() }}
                <div class="form-group">
                    <input class="form-control" type="text" placeholder="Chart Search" name="query">
                </div>
                <button class="btn btn-success" type="submit" id="searchbtn">Search</button>
            </form>
        </div>
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

<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js"
        integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
@yield('js')
</body>
</html>