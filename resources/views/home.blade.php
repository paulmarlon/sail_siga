@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Principal</h1>
@stop

@section('content')
    <p>Hola, {{ auth()->user()->name }}. Bienvenido al sistema SIGA.</p>
@stop
