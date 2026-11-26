<div class="sidebar">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a href="{{ url('/admin') }}" class="simple-text logo-mini">
                AE
            </a>
            <a href="{{ url('/admin') }}" class="simple-text logo-normal">
                Aetherix Admin
            </a>
        </div>
        <ul class="nav">
            <!-- Vistas para TODOS los niveles -->
            <li class="{{ request()->is('admin') ? 'active' : '' }}">
                <a href="{{ url('/admin') }}">
                    <i class="tim-icons icon-chart-pie-36"></i>
                    <p>Resumen</p>
                </a>
            </li>
            <li class="{{ request()->is('admin/reportes*') ? 'active' : '' }}">
                <a href="{{ url('/admin/reportes') }}">
                    <i class="tim-icons icon-paper"></i>
                    <p>Reportes</p>
                </a>
            </li>
            <li class="{{ request()->is('admin/auditoria*') ? 'active' : '' }}">
                <a href="{{ url('/admin/auditoria') }}">
                    <i class="tim-icons icon-zoom-split"></i>
                    <p>Auditoría</p>
                </a>
            </li>
            <li class="{{ request()->is('admin/planes*') ? 'active' : '' }}">
                <a href="{{ url('/admin/planes') }}">
                    <i class="tim-icons icon-tv-2"></i>
                    <p>Planes</p>
                </a>
            </li>

            <!-- Solo Total y Soporte -->
            <!-- Solo Total y Soporte -->
            @if (in_array(session('nivel'), ['Total', 'Soporte']))
                <li class="{{ request()->is('admin/usuarios*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/usuarios') }}">
                        <i class="tim-icons icon-single-02"></i>
                        <p>Clientes</p>
                    </a>
                </li>
                <li class="{{ request()->is('admin/cobros*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/cobros') }}">
                        <i class="tim-icons icon-coins"></i>
                        <p>Cobros</p>
                    </a>
                </li>
            @endif

            <!-- Solo Total -->
            @if (session('nivel') === 'Total')
                <li class="{{ request()->is('admin/administradores*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/administradores') }}">
                        <i class="tim-icons icon-badge"></i>
                        <p>Administradores</p>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
