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
        <div class="row" id="pdfcloserow" style="display: none;"><button type="button" class="btn btn-danger btnclosepdf">Close</button></div>
        <div class="row" id="chartbox"></div>
        <div class="row" id="pdfbox" style="display: none;"></div>
    </div>
@endsection
@section('js')
<script type="text/javascript" src="/js/ecr.js"></script>
@endsection