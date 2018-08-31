<?php
namespace App\Http\GraphQL;

class Query {
    public function airport($root, array $args, $context, $info) {
        return \App\Airport::find($args['id']);
    }

    public function charts($root, array $args, $context, $info) {
        if (isset($args['type'])) {
            return \App\Chart::where('id', $args['id'])->where('charttype', $args['type'])->get();
        } else {
            return \App\Chart::where('id', $args['id'])->orderBy('charttype')->get();
        }
    }
}
