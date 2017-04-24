@extends('layout')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Search Results</h2>
                @foreach($results as $result)
                    <div class="panel-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h2 class="panel-title">{{$result['icao']}}{{ ($result['iata']) ? "/" . $result['iata'] : "" }}
                                    - {{$result['name']}}</h2>
                            </div>
                            <div class="panel-body">
                                @foreach(\App\Chart::where('icao', $result['icao'])->where('iata', $result['iata'])->where('charttype', 'General')->orderBy('charttype')->orderBy('chartname')->get() as $chart)
                                    @if ($loop->first)
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">General</h4>
                                            </div>
                                            <div class="panel-body">
                                                @endif
                                                <a href="{{$chart->url}}">{{$chart->chartname}}</a><br>
                                                @if ($loop->last)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach(\App\Chart::where('icao', $result['icao'])->where('iata', $result['iata'])->where('charttype', 'SID')->orderBy('charttype')->orderBy('chartname')->get() as $chart)
                                    @if ($loop->first)
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">Departure Procedures</h4>
                                            </div>
                                            <div class="panel-body">
                                                @endif
                                                <a href="{{$chart->url}}">{{$chart->chartname}}</a><br>
                                                @if ($loop->last)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach(\App\Chart::where('icao', $result['icao'])->where('iata', $result['iata'])->where('charttype', 'STAR')->orderBy('charttype')->orderBy('chartname')->get() as $chart)
                                    @if ($loop->first)
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">Arrival Procedures</h4>
                                            </div>
                                            <div class="panel-body">
                                                @endif
                                                <a href="{{$chart->url}}">{{$chart->chartname}}</a><br>
                                                @if ($loop->last)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach(\App\Chart::where('icao', $result['icao'])->where('iata', $result['iata'])->where('charttype', 'Intermediate')->orderBy('charttype')->orderBy('chartname')->get() as $chart)
                                    @if ($loop->first)
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">Intermediate Arrival Procedures</h4>
                                            </div>
                                            <div class="panel-body">
                                                @endif
                                                <a href="{{$chart->url}}">{{$chart->chartname}}</a><br>
                                                @if ($loop->last)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @foreach(\App\Chart::where('icao', $result['icao'])->where('iata', $result['iata'])->where('charttype', 'Approach')->orderBy('charttype')->orderBy('chartname')->get() as $chart)
                                    @if ($loop->first)
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">Approach Procedures</h4>
                                            </div>
                                            <div class="panel-body">
                                                @endif
                                                <a href="{{$chart->url}}">{{$chart->chartname}}</a><br>
                                                @if ($loop->last)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection