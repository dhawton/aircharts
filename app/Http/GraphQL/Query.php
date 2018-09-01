<?php
namespace App\Http\GraphQL;

class Query {
    public function charts($root, array $args, $context, $info) {
        if (isset($args['type'])) {
            return \App\Chart::where('icao', $args['icao'])->where('charttype', $args['type'])->get();
        } else {
            return \App\Chart::where('icao', $args['icao'])->orderBy('charttype')->get();
        }
    }
}
