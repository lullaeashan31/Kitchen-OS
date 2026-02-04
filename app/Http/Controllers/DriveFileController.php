<?php

namespace App\Http\Controllers;

use App\Models\DriveFile;
use App\Services\DriveFileService;
use App\Http\Requests\StoreDriveFileRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DriveFileController extends Controller
{
    use AuthorizesRequests;

    protected $driveFileService;

    public function __construct(DriveFileService $driveFileService)
    {
        $this->driveFileService = $driveFileService;
    }

    public function store(StoreDriveFileRequest $request)
    {
        $this->authorize('create', DriveFile::class);

        $linkedType = $request->input('linked_type');
        $modelClass = $linkedType::modelClass();
        $linkedModel = $modelClass::findOrFail($request->input('linked_id'));

        $this->driveFileService->attachFile(
            $linkedModel,
            $request->input('drive_url'),
            $request->input('name'),
            $request->user()
        );

        return back()->with('success', 'File attached successfully.');
    }

    public function destroy(DriveFile $driveFile)
    {
        $this->authorize('delete', $driveFile);

        $this->driveFileService->deleteFile($driveFile);

        return back()->with('success', 'File link removed.');
    }

    public function preview(DriveFile $driveFile)
    {
        // Just return the view partial or URL?
        // Requirement: "Open inside modal... Iframe."
        if (request()->wantsJson()) {
            return response()->json([
                'url' => $driveFile->preview_url,
                'name' => $driveFile->name
            ]);
        }

        // Or redirect to preview view
        return view('drive.preview', compact('driveFile'));
    }
}
