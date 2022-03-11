<?php

namespace Dotlogics\Media\App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Dotlogics\Media\App\Models\TempMedia;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\File\File;

class TempFileUploadComponent extends Component
{
    use WithFileUploads;

    public Collection $files;

    public $config = [];
    public $file;
    public $name;
    public $title;
    public $maxFiles;
    public $total_files = null;
    public $canAddMoreFiles = true;
    public $maxSize = null;
    public $maxSizekb = null;
    public $maxSizeMb = null;
    public $maxSizeGb = null;

    public $maxSizeValidationMessage = null;

    public function mount(string $name, array $config=[], $maxFiles=10, $totalFiles = null)
    {
        $this->maxFiles = $maxFiles;
        $this->maxSize = get_max_file_size_bytes();
        $this->maxSizeKb = $this->maxSize / 1024;
        $this->maxSizeMb = $this->maxSizeKb / 1024;
        $this->maxSizeGb = $this->maxSizeMb / 1024;

        $this->name = $name;
        $this->title = Str::replace('_', ' ', $this->name);

        $this->config = array_merge($this->config(), $config);

        $this->total_files = $totalFiles;
        $this->files =  TempMedia::find(
            array_merge(
                old($name,[]), $this->config['files']
            )
        );

        $size = $this->maxSize;
        $size_suffix = 'bytes';

        switch(true){
            case $this->maxSizeGb > 1;
                $size = $this->maxSizeGb;
                $size_suffix = 'gigabyte';
                break;
            
            case $this->maxSizeMb > 1;
                $size = $this->maxSizeMb;
                $size_suffix = 'megabyte';
                break;
            
            case $this->maxSizeKb > 1;
                $size = $this->maxSizeKb;
                $size_suffix = 'kilobytes';
                break; 
        }

        $size = number_format($size, 2, '.', '');
        $size = Str::replace('.00', '', $size);

        $this->maxSizeValidationMessage = "The {$this->title} must not be greater than {$size} {$size_suffix}.";
    }

    public function render()
    {
        $this->setCanAddMore();
        return view('media::livewire.temp-file-upload-component');
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => [
                'bail',
                'required',
                function($attribute, $value, $fail){
                    if(!$value instanceof File){
                        return $fail("The :attribute must be a file.");
                    }

                    if ( !is_null($this->maxFiles) && ($this->files->count() + $this->total_files) >=  $this->maxFiles ){
                        $fail("Cannot add more then {$this->maxFiles} Images");
                    }
                },
                'max:' . ($this->maxSizeKb),
            ],
        ], [
            'file.max' => $this->maxSizeValidationMessage,
        ], [
            'file' => $this->title,
        ]);

        $tempFile = TempMedia::create();
        $tempFilename = $this->file->getClientOriginalName();
        $this->file->storeAs('/temp', $tempFilename);

        $tempFile->addMedia(
            storage_path('app/temp/'.$tempFilename)
        )
        ->toMediaCollection('default');

        $this->files[] = $tempFile;
    }

    public function removeMedia($id)
    {
        TempMedia::find($id)->delete();
        $this->files = $this->files->reject(function($file) use($id){
            return $file->id == $id;
        });
    }

    public function setCanAddMore(){
        $this->canAddMoreFiles = (is_null($this->maxFiles) || ($this->files->count() + $this->total_files) <  $this->maxFiles);
    }

    private function config()
    {
        return [
            'classes' => 'd-flex flex-column justify-content-center align-items-center w-100 rounded',
            'styles' => "background-color:#ededed;min-height:70px;text-align:center;cursor:pointer;",
            'defaultText' => 'Click to Select and Upload Files',
            'accept' => implode(',', [
                '*'
            ]),
            'files' => []
        ];
    }

    protected function getListeners()
    {
        return ["file_removed-{$this->name}" => 'fileRemoved'];
    }

    public function fileRemoved($total_files){
        $this->total_files = $total_files;
    }

}
