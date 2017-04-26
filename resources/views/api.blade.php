
<!DOCTYPE html>
<html>
<head>
    <title>AirCharts Public API Documentation</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        ul.apinav {
            margin-top: 15px
        }

        footer {
            text-align: center;
            font-size: 10px;
            color: gray
        }

        .italic {
            font-style: italic
        }
        .nav-pills.alt > .active > a, .nav-pills.alt > .active > a:hover, .nav-pills.alt > .active > a:focus {
            color: white;
            background-color: gray
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <ul class="apinav nav nav-pills">
                <li class="logo"><img src="https://www.aircharts.org/images/logo.png" alt="AirCharts"></li>
                <li><a href="/">API Doc Home</a></li>
            </ul>
            <h1>AirCharts Public API Documentation</h1>
            <h5>by Daniel A. Hawton</h5>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">Features</div>
            <div class="panel-body">
                <p>This documentation lists the public functions of the AirCharts v2 API.  Version 1 of the API, while still accepted, is obsolete and not documented.</p>
                <ul>
                    <li>The data return types are <b>JSON</b>.</li>
                    <li>Features added will be posted here.</li>
                    <p></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Airport</h3></div>
            <div class="panel-body">
                Get name, latitude, longitude and elevation of an airport or airport(s).  More than 1 airport is separated with a comma.
                <pre><code>https://api.aircharts.org/v2/Airport/KNPA
https://api.aircharts.org/v2/Airport/EGSS,EGLL,KBOS
</code></pre>
            </div>
        </div>
    </div>
</div>
</body>
</html>