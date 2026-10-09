<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
          <li class="breadcrumb-item"><a href="{{ $link ?? 'javascript: void(0)' }}">{{ $item }}</a></li>
          
          @if(isset($subItem))
          <li class="breadcrumb-item"><a href="{{ $subLink ?? 'javascript: void(0)' }}">{{ $subItem }}</a></li>
          @endif
          
          @if(isset($sub2Item))
          <li class="breadcrumb-item"><a href="{{ $sub2Link ?? 'javascript: void(0)' }}">{{ $sub2Item }}</a></li>
          @endif
          
          @if(isset($sub3Item))
          <li class="breadcrumb-item"><a href="{{ $sub3Link ?? 'javascript: void(0)' }}">{{ $sub3Item }}</a></li>
          @endif
          
          <li class="breadcrumb-item" aria-current="page">{{ $active }}</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">{{ $active }}</h2>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->
