@extends('ecr.layout')
@section("content")
    <div class="container">
        <div class="row text-center ">
            <h2>AirCharts</h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="form form-inline">
                    <div class="form-group">
                        <input type="text" id="searchbox" placeholder="Chart Search">
                    </div>
                    <button type="button" class="btn btn-success" id="btnSearch">Search</button>
                </form>
            </div>
        </div>
        <div class="row" id="chartbox"></div>
    </div>
@endsection
@section('js')
<script type="text/javascript" src="/js/ecr.js"></script>
@endsection