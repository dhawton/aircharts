@extends('layout')
@section('bodyclass')class="jumbo"@endsection
@section('content')
    <div class="container">
        <h1 class="display-3">Welcome to AirCharts</h1>
        <p>Welcome to the new AirCharts.  Use the search box above to search for charts.</p>
    </div>

<div class="container">
    <div class="row">
        <div class="col-md-12" style="height: 650px" id="mapdiv"></div>
    </div>
</div>
@endsection

@section('js')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
          integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
            integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
            crossorigin=""></script>
<script type="text/javascript">
var map;
function init() {
    var map = L.map('divmap').setView([37.4,0], 2)

    var icon = L.icon({
      iconUrl: '/images/map/dot.png',
      iconSize: [7, 7],
      iconAnchor: [3, 3]
    });
  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
  }).addTo(map);

    {!! $mappoints !!}
}

window.onload = init();
</script>
@endsection
