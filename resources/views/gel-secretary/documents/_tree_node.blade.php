@props(['node'])

@php
    // ── Meta rapide de la vue (les informations viennent du service, jamais en dur) ──
    $kind     = $node['kind'] ?? 'folder';
    $name     = $node['name'] ?? '';
    $count    = $node['doc_count'] ?? 0;
    $isMonth  = $node['is_current_month'] ?? false;
    $isClosed = $node['is_closed'] ?? false;
    $children = $node['children'] ?? [];
    $hasKids  = count($children) > 0;

    $currentYear  = (int) now()->format('Y');
    $isCurrentYear = $kind === 'year' && (int) $name === $currentYear;
    $collapsed     = $kind === 'year' && !$isCurrentYear;

    $icons = [
        'root'       => 'fa-hdd',
        'permanents' => 'fa-shield-halved',
        'courants'   => 'fa-folder-tree',
        'year'       => 'fa-archive',
        'month'      => 'fa-calendar-days',
        'folder'     => 'fa-folder',
    ];
    $icon = $icons[$kind] ?? 'fa-folder';

    // Mois : « Mois actuel » épinglé en tête, les autres décroissants.
    if ($kind === 'year') {
        usort($children, function ($a, $b) {
            $ac = ($a['is_current_month'] ?? false) ? 1 : 0;
            $bc = ($b['is_current_month'] ?? false) ? 1 : 0;
            if ($ac !== $bc) return $bc <=> $ac;
            return ($b['month'] ?? 0) <=> ($a['month'] ?? 0);
        });
    }

    $badge = '';
    if ($isMonth) {
        $badge = '<span class="badge bg-success ms-1" title="Mois en cours">Mois actuel</span>';
    } elseif ($isClosed) {
        $badge = '<span class="badge bg-secondary ms-1" title="Mois clôturé — lecture seule">🔒 clôturé</span>';
    } elseif ($isCurrentYear) {
        $badge = '<span class="badge bg-primary ms-1" title="Année en cours">Année en cours</span>';
    }
    $countBadge = $count > 0
        ? '<span class="badge bg-light text-secondary ms-auto db-count">' . $count . '</span>'
        : '';
@endphp

@if($hasKids)
  <details class="db-tree-node" {{ $collapsed ? '' : 'open' }}>
    <summary>
      <i class="fas {{ $icon }}"></i>
      <span class="t-name">{{ $name }}</span>
      {!! $badge !!}{!! $countBadge !!}
    </summary>
    <ul class="db-tree-children">
      @foreach($children as $child)
        <li>
          @include('gel-secretary.documents._tree_node', ['node' => $child])
        </li>
      @endforeach
    </ul>
  </details>
@else
  <a class="db-tree-node db-tree-leaf" href="{{ $node['url'] }}" title="{{ $name }}">
    <i class="fas {{ $icon }}"></i>
    <span class="t-name">{{ $name }}</span>
    {!! $badge !!}{!! $countBadge !!}
  </a>
@endif