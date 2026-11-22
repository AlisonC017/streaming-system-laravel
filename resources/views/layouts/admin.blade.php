{{-- Panel de administración: usa la misma base layouts/panel. Solo entran los tres niveles de admin. --}}
@php abort_unless(in_array(session('nivel'), ['Total', 'Soporte', 'Contenido'], true), 403); @endphp
@extends('layouts.panel')
