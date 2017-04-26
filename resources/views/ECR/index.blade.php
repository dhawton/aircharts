@extends('ECR.layout')
@section("content")
    <div class="container">
        <div class="row text-center ">
            <h2>AirCharts ECR</h2>
        </div>
        <div class="row" id="searchrow">
            <div class="col-md-12 text-center">
                <div class="form form-inline">
                    <div class="form-group">
                        <input type="text" id="searchbox" placeholder="Chart Search" class="form-control">
                    </div>
                    <button type="button" class="btn btn-success" id="btnSearch">Search</button>
                </div>
            </div>
        </div>
        <div class="row" id="pdfcloserow" style="display: none;"><div class="col-md-12 text-center"><button type="button" class="btn btn-danger btnclosepdf">Close PDF</button></div></div>
        <div class="row" id="chartbox" style="display:none;">
            <div class="col-md-12">
            <h2 id="airportinfo">&nbsp;</h2>
            <ul class="nav nav-pills">
                <li class="active">
                    <a href="#gen" data-toggle="tab">Gen</a>
                </li>
                <li><a href="#sid" data-toggle="tab">SID</a></li>
                <li><a href="#star" data-toggle="tab">STAR</a></li>
                <li><a href="#arr" data-toggle="tab">Int</a></li>
                <li><a href="#iap" data-toggle="tab">App</a></li>
            </ul><br>
            <div class="tab-content">
                <div class="tab-pane active" id="gen"></div>
                <div class="tab-pane" id="sid"></div>
                <div class="tab-pane" id="star"></div>
                <div class="tab-pane" id="arr"></div>
                <div class="tab-pane" id="iap"></div>
            </div>
            </div>
        </div>
        <div class="row" id="pdfbox" style="display: none;"></div>
    </div>
@endsection
@section('js')
<script type="text/javascript" src="/js/ecr.js"></script>
@endsection