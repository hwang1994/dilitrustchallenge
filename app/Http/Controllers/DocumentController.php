<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Document;
use App\StorageConstants;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:profile'); //only logged in users allowed here
    }

    public function createDocument(Request $request)
    {
        $request->validate([
            'visibility'=> ['in:user,admin'],
            'file.*' => ['required', 'mimes:pdf' , 'max:20000'] // for simplicity i'll only allow pdfs. 20mb is usually the max for email uploads so I'm just using that too
        ]);
        $allParams  = $request->all();
        $user   = Auth::user();
        $id = $user->getAttribute('id');
        $files = $request->file('file');
        $errors = [];
        if($request->hasFile('file'))
        {
            foreach ($files as $file) 
            {
                $path = StorageConstants::DOCUMENTS_DIRECTORY.'/'.htmlspecialchars($file->getClientOriginalName()); // encode the special characters just in case a file has them (shouldnt be possible) to prevent potentional XSS 
                $document = Document::all()->where('filepath',$path)->pop(); //check if file already exists
                if ($document === null) 
                {
                    $path = Storage::putFileAs(StorageConstants::DOCUMENTS_DIRECTORY, $file, htmlspecialchars($file->getClientOriginalName())); // not storing it in the public folder in storage
                    $document = new Document();
                    $document->user_id = $id;
                    $document->filepath = $path;
                    if (array_key_exists('visibility', $allParams)) {
                        $document->visibility = $allParams['visibility'];
                    }
                    $document->save();
                }
                else 
                {
                    $errors[] = $file->getClientOriginalName().' already exists in this directory. File not uploaded. Please rename it!';
                }
            }
        }
        if (count($errors)>0) {
            return redirect()->route('home')->withErrors($errors);
        }
        else {
            return redirect()->route('home');
        }
    }

    public function deleteDocument(Request $request, $file)
    {
        $id = Auth::user()->getAttribute('id');
        $document = Document::all()->where('id', $file)->pop();
        if ($document===null) {
            return redirect()->route('home')->withErrors('Document does not exist on database!');
        }
        if (!($id===$document->user_id || Auth::user()->getAttribute('role')==='admin')) {
            return redirect()->route('home')->withErrors('You do not have permission to delete this document');
        }
        $filename = $document->filepath;
        if (Storage::disk(StorageConstants::STORAGE_DRIVER)->exists($filename)) 
        {
            Storage::disk(StorageConstants::STORAGE_DRIVER)->delete($filename);
            Document::where('id', $file)->delete();
            return redirect()->route('home');
        }
        else 
        {
            Document::where('id', $file)->delete();
            return redirect()->route('home')->withErrors('Document does not exist on storage!');
        }
    }

    public function deleteAllDocuments(Request $request)
    {
        $request->validate([
            'password'  => ['required']
        ]);
        if (count(Storage::allFiles(StorageConstants::DOCUMENTS_DIRECTORY))==0) {
            return redirect()->route('home')->withErrors('No documents to delete!');
        }
        $allParams  = $request->all();
        $password = $allParams['password'];
        if (Hash::check($password, Auth::user()->getAttribute('password'))) 
        {            
            $files = Storage::allFiles(StorageConstants::DOCUMENTS_DIRECTORY);
            Storage::delete($files);
            Document::truncate();
            return redirect()->route('home');
        }
        else
        {
            return redirect()->route('home')->withErrors('Incorrect password!');
        }
    }

    public function downloadDocument($file)
    {
        $document = Document::all()->where('id', $file)->pop();
        if ($document===null) {
            return redirect()->route('home')->withErrors('Requested file does not exist on database!');
        }
        if  (!($document->visibility==='user' || Auth::user()->getAttribute('role')===$document->visibility) ) {
            return redirect()->route('home')->withErrors('You do not have permission to download this document');
        }
        $filename = $document->filepath;
        if (Storage::disk(StorageConstants::STORAGE_DRIVER)->exists($filename)) {
            return Storage::download($filename);
        }
        else {
            return redirect()->route('home')->withErrors('Requested file does not exist on server!');
        }
    }

    public function downloadZip(Request $request)
    {
        // the first 2 lines below destroy my garbage collection since Laravel's deleteFileAfterSend(true) doesn't seem to work consistently during testing
        $files = Storage::allFiles(StorageConstants::DOCUMENTSZIP_DIRECTORY);
        Storage::delete($files);
        if (count(Storage::allFiles(StorageConstants::DOCUMENTS_DIRECTORY))===0 || (Auth::user()->getAttribute('role')!=='admin' && Document::all()->where('visibility', Auth::user()->getAttribute('role'))->count()===0)) 
        {
            return redirect()->route('home')->withErrors('No documents found!');
        }
        $zip = new \ZipArchive();
        $id = Auth::user()->getAttribute('id');
        $fileName = 'documents'.$id.md5(date('Y-m-d H:i:s:u')).'.zip'; // my thinking is we should create new zip files each time with unique names to avoid possible race condition
        if ($zip->open(Storage::disk(StorageConstants::STORAGE_DRIVER)->path(StorageConstants::DOCUMENTSZIP_DIRECTORY.'/'.$fileName), \ZipArchive::CREATE)== TRUE)
        {
            $files = File::files(Storage::disk(StorageConstants::STORAGE_DRIVER)->path(StorageConstants::DOCUMENTS_DIRECTORY));
            if (Auth::user()->getAttribute('role')!=='admin') 
            {
                $documents = Document::all()->where('visibility', 'user')->pluck('filepath')->toArray();           
                foreach ($files as $key => $value) {
                    $relativeName = basename($value);
                    if (in_array(StorageConstants::DOCUMENTS_DIRECTORY.'/'.$relativeName, $documents)) { //see if the file is in the list of documents
                        $zip->addFile($value, $relativeName);
                    }
                }
            }
            else 
            {
                foreach ($files as $key => $value) {
                    $relativeName = basename($value);
                    $zip->addFile($value, $relativeName);
                }
            }
            $zip->close();
            return response()->download(Storage::disk(StorageConstants::STORAGE_DRIVER)->path(StorageConstants::DOCUMENTSZIP_DIRECTORY.'/'.$fileName))->deleteFileAfterSend(true); // Why doesn't this work?!
        }
        else 
        {
            return redirect()->route('home')->withErrors('Failed to create and download zip');
        }
    }

}