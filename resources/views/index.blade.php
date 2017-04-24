@section('content')
<div class="jumbotron">
    <div class="container">
        <h1 class="display-3">Welcome to AirCharts</h1>
        <p>Welcome to the new AirCharts.  Use the search box above to search for charts.</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12" style="height: 650px" id="mapdiv"></div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
var map;
function init() {
    var style0 = [{"featureType":"all","stylers":[{"saturdation":"-100"}]},{"featureType":"transit.station.airport","elementType":"gemoetry","stylers":[{"hue":"#000000"}]}];
    //var myLatLng = new google.maps.LatLng(37.4, -122.1);
    var myLatLng = new google.maps.LatLng(37.4, 0);
    var myOpt = {
        zoom: 2,
        center: myLatLng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: true,
        mapTypeControl: true,
        navigationControl: false,
        scaleControl: true,
        streetViewControl: false,
        minZoom: 2,
        maxZoom: 8}

    map = new google.maps.Map(document.getElementById("mapdiv"), myOpt);
    map.setOptions({styles: style0});

    {{$mappoints}}
}
</script>
@endsection