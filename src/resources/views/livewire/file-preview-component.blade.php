<div class="position-relative d-flex flex-wrap justify-content-center align-items-center p-2">
    @foreach($model->getMedia($collection) as $media)
        @if (!in_array($media->id, $deleted))
            <div class="image-wrapper position-relative p-1">
                <button type="button" style="width: 25px;height:25px;right:0;top:0;" wire:click="remove('{{$media->id}}')" class="p-0 btn btn-danger rounded-circle position-absolute">
                    X
                </button>

                @if($media->type == 'image')
                    <img src="{{ $media->getFullUrl() }}" style="width: 100px;height:100px;" alt="" class="d-inline-block">
                @else
                    <br>
                    <br>
                    <a href="{{ $media->getFullUrl() }}" target="_blank">{{ $media->name }}</a>
                @endif
            </div>
        @endif
    @endforeach

    @foreach ($deleted as $id)
        <input type="hidden" name="deleted_files[{{ $collection }}][]" value="{{ $id }}">
    @endforeach

    <div wire:loading class="position-absolute w-100 h-100" style="top:0;left:0; background-color:#fff;opacity:0.5;">
        <div class="d-flex align-items-center justify-content-center w-100 h-100">
            <h4>
                <i class="fa fa-spinner fa-spin"></i>
            </h4>
        </div>
    </div>
</div>
