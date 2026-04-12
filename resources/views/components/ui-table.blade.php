@props([
    'id' => null,
    'columns' => [], // array of ['label' => 'Header', 'key' => 'field']
    'rows' => [],    // array or Collection of data
    'actions' => null, // array of actions: ['label','href','class','attrs']
    'empty' => 'Tidak ada data',
])

<div class="ui-table-wrapper pekppp-ui">
  <div class="ui-table-controls">
    {{-- left slot: place search / filters etc --}} 
    <div class="ui-table-left">{{ $slot ?? '' }}</div>
    {{-- right: optional place for global action buttons --}}
    <div class="ui-table-right"></div>
  </div>

  <div class="ui-table-scroll">
    <table class="ui-table" @if($id) id="{{ $id }}" @endif>
      <thead>
        <tr>
          @foreach($columns as $col)
            <th>{{ $col['label'] ?? '' }}</th>
          @endforeach
          @if(is_array($actions) && count($actions))
            <th style="width:1%;white-space:nowrap">Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          <tr @if(data_get($row, 'data_id')) data-id="{{ data_get($row, 'data_id') }}" @endif>
            @foreach($columns as $col)
              <td>{!! data_get($row, $col['key']) !!}</td>
            @endforeach

            @php $rowActionsHtml = data_get($row, 'actions'); @endphp
            @if(!empty($rowActionsHtml))
              <td class="ui-table-actions">{!! $rowActionsHtml !!}</td>
            @elseif(is_array($actions) && count($actions))
              <td class="ui-table-actions">
                @foreach($actions as $act)
                  @php $attrs = $act['attrs'] ?? []; @endphp
                  @if(!empty($act['form']) && $act['form'] === true)
                    <form action="{{ $act['href'] ?? '#' }}" method="{{ strtoupper($act['method'] ?? 'POST') }}" style="display:inline">
                      @csrf
                      @if(!empty($act['method']) && strtoupper($act['method']) !== 'POST')
                        @method($act['method'])
                      @endif
                      <button type="submit" class="btn {{ $act['class'] ?? 'btn-primary btn-sm' }}" @foreach($attrs as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
                        {{ $act['label'] ?? 'Action' }}
                      </button>
                    </form>
                  @else
                    <a href="{{ $act['href'] ?? '#' }}" class="btn {{ $act['class'] ?? 'btn-primary btn-sm' }}" 
                       @foreach($attrs as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
                      {{ $act['label'] ?? 'Action' }}
                    </a>
                  @endif
                @endforeach
              </td>
            @endif

          </tr>
        @empty
          <tr class="empty"><td colspan="{{ count($columns) + (is_array($actions) ? 1 : 0) }}">{{ $empty }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
