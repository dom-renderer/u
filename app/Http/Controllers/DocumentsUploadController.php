<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentUpload;
use App\Models\DocumentUser;
use App\Models\NotificationTemplate;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class DocumentsUploadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data_query = DocumentUpload::with( [ 'document', 'store', 'storeCategory' ] );

            if ($request->filled('document_type_id')) {
                $data_query->where('document_id', $request->document_type_id);
            }

            if ($request->filled('location_id')) {
                $data_query->where('location_id', $request->location_id);
            }

            if ($request->filled('perpetual') && $request->perpetual != 'all') {
                $perpetualValue = strtolower($request->perpetual) === 'yes' ? 1 : 0;
                $data_query->where('perpetual', $perpetualValue);
            }

            if ($request->filled('expiry_from') && $request->filled('expiry_to')) {
                $expiryFrom = \Carbon\Carbon::createFromFormat('d-m-Y', $request->expiry_from)->startOfDay();
                $expiryTo = \Carbon\Carbon::createFromFormat('d-m-Y', $request->expiry_to)->endOfDay();
                $data_query->whereBetween('expiry_date', [$expiryFrom, $expiryTo]);
            }

            if ($request->filled('issue_from') && $request->filled('issue_to')) {
                $issueFrom = \Carbon\Carbon::createFromFormat('d-m-Y', $request->issue_from)->startOfDay();
                $issueTo = \Carbon\Carbon::createFromFormat('d-m-Y', $request->issue_to)->endOfDay();
                $data_query->whereBetween('issue_date', [$issueFrom, $issueTo]);
            }

            return datatables()
            ->eloquent( $data_query )
            ->addColumn('document_name', function ($row) {
                return !empty($row->document) ? $row->document->name : '-';
            })
            ->addColumn('attachment', function ($row) {
                if ( !empty($row->attachment_path) ) {
                    return '<a href="' . $row->attachment_path . '" target="_blank" class="btn btn-sm btn-secondary">View</a>';
                }
                return '-';
            })
            ->addColumn('location', function ($row) {
                return !empty($row->store) ? ($row->store->code . ' - ' . $row->store->name) : '-';
            })
            ->addColumn('location_category', function ($row) {
                return !empty($row->storeCategory) ? $row->storeCategory->name : '-';
            })
            ->editColumn('expiry_date', function ($row) {
                if ($row->perpetual) {
                    return 'Perpetual';
                }
                return !empty($row->expiry_date) ? \Carbon\Carbon::parse( $row->expiry_date )->format( 'd-m-Y' ) : '-';
            })
            ->editColumn('issue_date', function ($row) {
                return !empty($row->issue_date) ? \Carbon\Carbon::parse( $row->issue_date )->format( 'd-m-Y' ) : '-';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('document-upload.show')) {
                    $action .= '<a href="'.route("document-upload.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('document-upload.edit')) {
                    $action .= '<a href="'.route('document-upload.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('document-upload.destroy')) {
                    $action .= '<form method="POST" action="'.route("document-upload.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->filterColumn('document_name', function($query, $keyword) {
                $query->whereHas('document', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('location', function($query, $keyword) {
                $query->whereHas('store', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('location_category', function($query, $keyword) {
                $query->whereHas('storeCategory', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns( [ 'document_name', 'attachment', 'location', 'expiry_date', 'issue_date', 'action' ] )
            ->addIndexColumn()
            ->toJson();
        }

        $page_title = 'Document List';
        $page_description = 'Manage Document List/Upload here';

        return view( 'document-upload.index', compact( 'page_title', 'page_description' ) );
    }

    public function create()
    {
        $page_title = 'Document Upload';
        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();

        return view( 'document-upload.create', compact( 'page_title', 'document_arr', 'location_category_arr', 'notification_template_arr' ) );
    }
    
    public function store(Request $request)
    {
        $isPerpetual = $request->has('zp_perpetual') && $request->zp_perpetual == '1';
        
        $validationRules = [
            'zp_document_file' => [
                'required',
                'file',
                'max:20480',
                function ($attribute, $value, $fail) {
                    $forbidden = ['exe', 'sh', 'bat', 'php', 'js', 'msi', 'cmd', 'com', 'vbs'];
                    $ext = strtolower($value->getClientOriginalExtension());

                    if (in_array($ext, $forbidden)) {
                        $fail('Please Upload valid file');
                    }
                },
            ],
            'zp_document'          => 'required',
            'zp_location'          => 'required',
            'zp_issue_date'        => 'required|date_format:Y-m-d',
            'zp_remark'            => 'required',
        ];

        if (!$isPerpetual) {
            $validationRules['zp_expiry_date'] = 'required|date_format:Y-m-d';
        }

        $request->validate($validationRules);

        $fileName = Str::random( 40 ) . '.' . $request->file( 'zp_document_file' )->getClientOriginalExtension();
        $request->file( 'zp_document_file' )->storeAs( 'documents', $fileName, 'public' );

        $document_other = array();
        if ( !empty($request->zp_template) ) {
            $document_other[ 'notification_template' ] = $request->zp_template;
        }

        $additionalUsers = $request->zp_additional_users ?? [];

        $document_upload_data = DocumentUpload::create([
            'file_name'                      => $fileName,
            'document_id'                    => $request->zp_document,
            'location_id'                    => $request->zp_location,
            'expiry_date'                    => $isPerpetual ? null : $request->zp_expiry_date,
            'issue_date'                     => $request->zp_issue_date,
            'remark'                         => $request->zp_remark,
            'document_other'                 => !empty($document_other) ? json_encode( $document_other ) : null,
            'perpetual'                      => $isPerpetual ? 1 : 0,
            'enable_store_access'            => $request->has('zp_enable_store_access') ? 1 : 0,
            'enable_dom_access'              => $request->has('zp_enable_dom_access') ? 1 : 0,
            'enable_operation_manager_access'=> $request->has('zp_enable_operation_manager_access') ? 1 : 0,
            'status'                         => $request->has('zp_status') ? 'active' : 'inactive',
        ]);

        if ( !empty($request->zp_users) ) {
            foreach ( $request->zp_users as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $document_upload_data->id,
                    'user_id' => $user_id,
                    'user_type' => 0
                ]);
            }
        }

        if ( !empty($additionalUsers) ) {
            foreach ( $additionalUsers as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $document_upload_data->id,
                    'user_id' => $user_id,
                    'user_type' => 1
                ]);
            }
        }

        return redirect()->route('document-upload.index')->with('success','Document Upload created successfully');
    }

    public function show($id)
    {
        $page_title = 'Document Upload Show';
        $documentupload = DocumentUpload::find(decrypt($id));
        
        $document_other = !empty($documentupload->document_other) ? json_decode( $documentupload->document_other, true ) : array();
        $notification_id_arr = !empty($document_other['notification_template']) ? $document_other['notification_template'] : array();

        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();
    
        return view( 'document-upload.show', compact( 'documentupload', 'page_title', 'notification_id_arr', 'document_arr', 'location_category_arr', 'notification_template_arr' ) );
    }

    public function edit($id)
    {
        $page_title = 'Document Upload Edit';
        $documentupload = DocumentUpload::find( decrypt( $id ) );
        $document_other = !empty($documentupload->document_other) ? json_decode( $documentupload->document_other, true ) : array();
        $notification_id_arr = !empty($document_other['notification_template']) ? $document_other['notification_template'] : array();

        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();
    
        return view( 'document-upload.edit', compact( 'documentupload', 'page_title', 'id', 'document_other', 'notification_id_arr', 'notification_template_arr', 'location_category_arr', 'document_arr' ) );
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);
        $documentUpload = DocumentUpload::findOrFail( $cId );
        $isPerpetual = $request->has('zp_perpetual') && $request->zp_perpetual == '1';
        
        $validationRules = [
            'zp_document_file' => [
                'nullable',
                'file',
                'max:20480',
                function ($attribute, $value, $fail) {
                    $forbidden = ['exe', 'sh', 'bat', 'php', 'js', 'msi', 'cmd', 'com', 'vbs'];
                    $ext = strtolower($value->getClientOriginalExtension());

                    if (in_array($ext, $forbidden)) {
                        $fail('Please Upload valid file');
                    }
                },
            ],
            'zp_document'          => 'required',
            'zp_location'          => 'required',
            'zp_issue_date'        => 'required|date_format:Y-m-d',
            'zp_remark'            => 'required',
        ];

        if (!$isPerpetual) {
            $validationRules['zp_expiry_date'] = 'required|date_format:Y-m-d';
        }

        $request->validate($validationRules);

        if ( $request->hasFile( 'zp_document_file' ) ) {
            if ( !empty($documentUpload->file_name) ) {
                $oldFilePath = public_path( 'storage/documents/' . $documentUpload->file_name );
                if ( file_exists( $oldFilePath ) ) {
                    unlink( $oldFilePath );
                }
            }

            $fileName = Str::random(40) . '.' . $request->file( 'zp_document_file' )->getClientOriginalExtension();
            $request->file( 'zp_document_file' )->storeAs( 'documents', $fileName, 'public' );
            $documentUpload->file_name = $fileName;
        }

        $additionalUsers = $request->zp_additional_users ?? [];

        $documentUpload->document_id                    = $request->zp_document;
        $documentUpload->location_id                    = $request->zp_location;
        $documentUpload->expiry_date                    = $isPerpetual ? null : $request->zp_expiry_date;
        $documentUpload->issue_date                     = $request->zp_issue_date;
        $documentUpload->remark                         = $request->zp_remark;
        $documentUpload->perpetual                      = $isPerpetual ? 1 : 0;
        $documentUpload->enable_store_access            = $request->has('zp_enable_store_access') ? 1 : 0;
        $documentUpload->enable_dom_access              = $request->has('zp_enable_dom_access') ? 1 : 0;
        $documentUpload->enable_operation_manager_access= $request->has('zp_enable_operation_manager_access') ? 1 : 0;
        $documentUpload->status                         = $request->has('zp_status') ? 'active' : 'inactive';

        $document_other = [];
        if ( !empty($request->zp_template) ) {
            $document_other['notification_template'] = $request->zp_template;
        }
        $documentUpload->document_other = !empty($document_other) ? json_encode( $document_other ) : null;

        $documentUpload->save();

        DocumentUser::where( 'document_upload_id', $documentUpload->id )->delete();

        if ( !empty($request->zp_users) ) {
            foreach ( $request->zp_users as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $documentUpload->id,
                    'user_id' => $user_id,
                    'user_type' => 0
                ]);
            }
        }

        if ( !empty($additionalUsers) ) {
            foreach ( $additionalUsers as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $documentUpload->id,
                    'user_id' => $user_id,
                    'user_type' => 1
                ]);
            }
        }

        return redirect()->route('document-upload.index')->with('success','Document Upload updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $documentUpload = DocumentUpload::findOrFail( $id );
        if ($documentUpload->file_name) {
            $filePath = public_path( 'storage/documents/' . $documentUpload->file_name );
            if ( file_exists( $filePath ) ) {
                unlink( $filePath );
            }
        }
        DocumentUser::where( 'document_upload_id', $documentUpload->id )->delete();
        $documentUpload->delete();

        return redirect()->back()->with('success','Document Upload deleted successfully');
    }

    public static function sendDocumentExpiryReminder()
    {
        $days = array_reverse(range(1, 60, 1));
        $today = \Carbon\Carbon::today();

        foreach ($days as $day) {
            $targetDate = $today->copy()->addDays($day);
            $documents = DocumentUpload::with(['document', 'users'])->where('perpetual', false)->whereDate('expiry_date', $targetDate)->get();

            if ( $documents->isNotEmpty() ) {
                foreach ($documents as $doc) {
                    $templatesIds = !empty($doc->document_other) ? json_decode($doc->document_other, true)['notification_template'] ?? [] : [];
    
                    $allTemplates = NotificationTemplate::whereIn('id', $templatesIds)->get();
    
                    foreach ($allTemplates as $template) {
                        foreach ($doc->users as $user) {
                            
                            $content = str_replace(
                                ['{user_name}', '{document_name}', '{expiry_date}'],
                                [$user->name ?? 'N/A', $doc->document->name ?? 'N/A', \Carbon\Carbon::parse( $doc->expiry_date )->format('d-m-Y')],
                                $template->content
                            );
    
                            Mail::send([], [], function ($message) use ($user, $template, $content, $doc) {
                                $message->to( $user->email, $user->name )
                                    ->subject( $template->title . ' - ' . $doc->document->name )
                                    ->setBody( $content, 'text/html' );
                            });
                            sleep(1);
                        }
                    }
                }
            }
        }
        echo 'Document expiry reminders sent successfully.';
    }

    public function import(Request $request)
    {
        if (!$request->isMethod('post')) {
            return response()->json(['status' => false, 'message' => 'Invalid request method']);
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:10240',
            'zip_file' => 'required|file|mimes:zip|max:102400',
        ]);

        $errors = [];
        $successCount = 0;
        $additionalUsersData = [];

        try {
            $excelFile = $request->file('excel_file');
            $zipFile = $request->file('zip_file');

            $tempPath = storage_path('app/temp/document-imports/' . uniqid());
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipFile->getRealPath()) === true) {
                $zip->extractTo($tempPath);
                $zip->close();
            } else {
                return response()->json(['status' => false, 'message' => 'Failed to extract ZIP file']);
            }

            $zipFiles = $this->getFilesFromDirectory($tempPath);

            $excelData = Excel::toArray(new \App\Imports\DocumentUploadImport, $excelFile);
            if (empty($excelData) || empty($excelData[0])) {
                $this->cleanupTempDirectory($tempPath);
                return response()->json(['status' => false, 'message' => 'Excel file is empty']);
            }

            $rows = $excelData[0];
            $headers = array_shift($rows);

            if (!Storage::disk('public')->exists('documents')) {
                Storage::disk('public')->makeDirectory('documents');
            }

            \DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;

                if (empty(array_filter($row))) {
                    continue;
                }

                $storeCode = trim($row[0] ?? '');
                $documentSlug = trim($row[1] ?? '');
                $fileName = trim($row[2] ?? '');
                $issueDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(trim($row[3] ?? 0))->format('Y-m-d');
                $perpetual = strtolower(trim($row[4] ?? ''));
                $expiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(trim($row[5] ?? 0))->format('Y-m-d');
                $remark = trim($row[6] ?? '');
                $storeAccess = strtolower(trim($row[7] ?? ''));
                $domAccess = strtolower(trim($row[8] ?? ''));
                $operationManagerAccess = strtolower(trim($row[9] ?? ''));
                $additionalUsers = trim($row[10] ?? '');
                $notificationUsers = trim($row[11] ?? '');
                $templates = trim($row[12] ?? '');
                $status = strtolower(trim($row[13] ?? ''));

                $rowErrors = [];

                $store = Store::where('code', $storeCode)->first();
                if (!$store) {
                    $rowErrors[] = "Store code '{$storeCode}' not found";
                }

                $document = Document::where('slug', $documentSlug)->first();
                if (!$document) {
                    $rowErrors[] = "Document type slug '{$documentSlug}' not found";
                }

                $matchedFile = null;
                foreach ($zipFiles as $zipFilePath => $zipFileName) {
                    if ($zipFileName === $fileName) {
                        $matchedFile = $zipFilePath;
                        break;
                    }
                }
                if (!$matchedFile) {
                    $rowErrors[] = "File '{$fileName}' not found in ZIP";
                }

                if (!empty($issueDate) && !$this->isValidDate($issueDate)) {
                    $rowErrors[] = "Invalid issue date format '{$issueDate}'. Expected Y-m-d";
                }

                if (!in_array($perpetual, ['yes', 'no'])) {
                    $rowErrors[] = "Perpetual must be 'Yes' or 'No', got '{$perpetual}'";
                }

                if (!empty($expiryDate) && !$this->isValidDate($expiryDate)) {
                    $rowErrors[] = "Invalid expiry date format '{$expiryDate}'. Expected Y-m-d";
                }

                if (!in_array($storeAccess, ['yes', 'no'])) {
                    $rowErrors[] = "Store Access must be 'Yes' or 'No'";
                }

                if (!in_array($domAccess, ['yes', 'no'])) {
                    $rowErrors[] = "DoM Access must be 'Yes' or 'No'";
                }

                if (!in_array($operationManagerAccess, ['yes', 'no'])) {
                    $rowErrors[] = "Operation Manager Access must be 'Yes' or 'No'";
                }

                $additionalUserIds = [];
                if (!empty($additionalUsers)) {
                    $additionalUserIds = array_map('trim', explode(',', $additionalUsers));
                    $validUserIds = User::whereIn('employee_id', $additionalUserIds)->pluck('id')->toArray();
                    $invalidIds = $validUserIds;
                    if (empty($invalidIds)) {
                        $rowErrors[] = "Please define additional users employee_id coma separated";
                    }
                }

                $notificationUserIds = [];
                if (!empty($notificationUsers)) {
                    $notificationUserIds = array_map('trim', explode(',', $notificationUsers));
                    $validNotificationUserIds = User::whereIn('employee_id', $notificationUserIds)->pluck('id')->toArray();
                    $invalidNotificationIds = $validNotificationUserIds;
                    if (empty($invalidNotificationIds)) {
                        $rowErrors[] = "Please define notification users employee_id coma separated";
                    }
                }

                $templateIds = [];
                if (!empty($templates)) {
                    $templateIds = array_map('trim', explode(',', $templates));
                    $validTemplateIds = NotificationTemplate::whereIn('title', $templateIds)->pluck('id')->toArray();
                    $invalidTemplateIds = $validTemplateIds;
                    if (empty($invalidTemplateIds)) {
                        $rowErrors[] = "Please define notification template titles coma separated";
                    }
                }

                if (!in_array($status, ['active', 'inactive']) || !in_array($status, ['yes', 'no'])) {
                    $rowErrors[] = "Status must be 'Active' or 'Inactive'";
                }

                if (!empty($rowErrors)) {
                    $errors[] = "Row {$rowNumber}: " . implode('; ', $rowErrors);
                    continue;
                }

                $newFileName = Str::random(40) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                $destinationPath = storage_path('app/public/documents/' . $newFileName);
                copy($matchedFile, $destinationPath);

                $documentOther = [];
                if (!empty($templateIds)) {
                    $documentOther['notification_template'] = array_map('intval', $templateIds);
                }

                $documentUpload = DocumentUpload::create([
                    'file_name' => $newFileName,
                    'document_id' => $document->id,
                    'location_id' => $store->id,
                    'issue_date' => $issueDate ?: null,
                    'perpetual' => $perpetual === 'yes' ? 1 : 0,
                    'expiry_date' => $expiryDate ?: null,
                    'remark' => $remark ?: null,
                    'enable_store_access' => $storeAccess === 'yes' ? 1 : 0,
                    'enable_dom_access' => $domAccess === 'yes' ? 1 : 0,
                    'enable_operation_manager_access' => $operationManagerAccess === 'yes' ? 1 : 0,
                    'document_other' => !empty($documentOther) ? json_encode($documentOther) : null,
                    'status' => $status,
                ]);

                if (!empty($notificationUserIds)) {
                    foreach ($notificationUserIds as $userId) {
                        DocumentUser::create([
                            'document_upload_id' => $documentUpload->id,
                            'user_id' => $userId,
                            'user_type' => 0
                        ]);
                    }
                }

                if (!empty($additionalUserIds)) {
                    foreach ($additionalUserIds as $userId) {
                        DocumentUser::create([
                            'document_upload_id' => $documentUpload->id,
                            'user_id' => $userId,
                            'user_type' => 1
                        ]);
                    }
                }

                if (!empty($additionalUserIds)) {
                    $additionalUsersData[$documentUpload->id] = $additionalUserIds;
                }

                $successCount++;
            }

            \DB::commit();

            $this->cleanupTempDirectory($tempPath);

            if ($successCount > 0) {
                $message = "{$successCount} document(s) imported successfully.";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " row(s) had errors.";
                }
                return response()->json([
                    'status' => true,
                    'message' => $message,
                    'success_count' => $successCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No documents were imported.',
                    'errors' => $errors,
                ]);
            }

        } catch (\Exception $e) {
            \DB::rollBack();
            if (isset($tempPath)) {
                $this->cleanupTempDirectory($tempPath);
            }
            return response()->json([
                'status' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ]);
        }
    }

    private function getFilesFromDirectory($directory)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[$file->getPathname()] = $file->getFilename();
            }
        }
        return $files;
    }

    private function cleanupTempDirectory($directory)
    {
        if (is_dir($directory)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($directory);
        }
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

}

