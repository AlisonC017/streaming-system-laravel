{{-- cliente: un administrador no entra aqui --}}
@php abort_if(session('nivel'), 403); @endphp
@extends('layouts.panel')
