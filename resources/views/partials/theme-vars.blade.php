{{-- Inject admin-editable palette + pre-paint dark mode (no FOUC) --}}
<style>:root { {{ \App\Support\Theme::cssVariables() }} }</style>
<script>{!! \App\Support\Theme::modeScript() !!}</script>
