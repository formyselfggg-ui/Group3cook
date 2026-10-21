@extends('layouts.app')

@section('homeRoute', route('field.dashboard'))
@section('workspace', 'Field operations')
@section('roleLabel', 'Field personnel')
@section('footer', 'DASURECO field operations · Stay safe and keep every work order updated.')

@section('avatar')
    <span class="grid size-9 place-items-center rounded-full bg-[#dff0df] text-sm font-bold text-[#2e7048]" aria-hidden="true">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    </span>
@endsection
